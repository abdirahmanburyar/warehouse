<?php

namespace App\Imports;

use App\Models\WarehouseAmc;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldQueue;

class WarehouseAmcImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, WithProgressBar, WithEvents, ShouldQueue
{
    use Importable;

    protected $monthYears;
    protected $products;
    protected $errors = [];
    protected $importId;
    protected $totalRows = 0;
    protected $processedRows = 0;

    /**
     * The name of the queue the job should be sent to.
     */
    public $queue = 'imports';

    public function __construct($importId = null)
    {
        $this->importId = $importId;
        
        // Get existing month years from database
        $this->monthYears = WarehouseAmc::select('month_year')
            ->distinct()
            ->orderBy('month_year', 'desc')
            ->pluck('month_year')
            ->toArray();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->processRow($row->toArray());
        }
    }

    private function processRow(array $row)
    {
        try {
            // Skip empty rows
            if (empty($row['item']) || trim($row['item']) === '') {
                Log::info("Skipping empty row: " . json_encode($row));
                return;
            }

            Log::info("Processing row: " . json_encode($row));

            // Find product by name (exact match first, then partial)
            $product = Product::where('name', '=', trim($row['item']))->first();
            
            if (!$product) {
                $product = Product::where('name', 'like', '%' . trim($row['item']) . '%')->first();
            }
            
            // If product not found, log warning but continue processing other rows
            if (!$product) {
                $this->errors[] = "Product '{$row['item']}' not found - skipping this row";
                Log::warning("Product not found during import: {$row['item']} - row skipped");
                return;
            }

            Log::info("Found product: {$product->name} (ID: {$product->id})");

            // Process each month column
            foreach ($row as $key => $value) {
                // Skip non-month columns
                if (in_array($key, ['item', 'category', 'dosage_form'])) {
                    Log::info("Skipping non-month column: {$key} = {$value}");
                    continue;
                }

                // Convert formatted month back to YYYY-MM format
                $monthYear = $this->parseMonthYear($key);
                if (!$monthYear) {
                    Log::info("Could not parse month from column: {$key} = {$value}");
                    continue;
                }

                Log::info("Processing month: {$key} -> {$monthYear} = {$value}");

                // Clean and validate quantity
                $quantity = $this->cleanQuantity($value);
                
                // If quantity is null or empty, skip this month (don't update existing data)
                if ($quantity === null) {
                    Log::info("Skipping null/empty quantity for month: {$monthYear}");
                    continue;
                }

                Log::info("Creating/updating WarehouseAmc: product_id={$product->id}, month_year={$monthYear}, quantity={$quantity}");

                // Only update/create if we have a valid quantity
                if ($quantity !== null) {
                    $warehouseAmc = WarehouseAmc::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'month_year' => $monthYear,
                        ],
                        [
                            'quantity' => $quantity,
                        ]
                    );
                    
                    Log::info("WarehouseAmc record " . ($warehouseAmc->wasRecentlyCreated ? 'created' : 'updated') . ": ID={$warehouseAmc->id}");
                }
            }

            // Update progress
            $this->processedRows++;
            $this->updateProgress();

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row for '{$row['item']}': " . $e->getMessage();
            Log::error("Import error for row: " . json_encode($row) . " - " . $e->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            'item' => 'required|string',
            'category' => 'nullable|string',
            'dosage_form' => 'nullable|string',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                $this->totalRows = $event->getReader()->getTotalRows()['Sheet1'] ?? 0;
                $this->updateProgress('processing', 0, 'Import started');
            },
            
            AfterImport::class => function(AfterImport $event) {
                $this->updateProgress('completed', 100, 'Import completed successfully');
                
                // Log completion
                Log::info("Warehouse AMC import completed", [
                    'import_id' => $this->importId,
                    'total_rows' => $this->totalRows,
                    'processed_rows' => $this->processedRows,
                    'errors' => $this->errors
                ]);
            },
        ];
    }

    /**
     * Update import progress in cache
     */
    private function updateProgress($status = 'processing', $progress = null, $message = null)
    {
        if (!$this->importId) {
            return;
        }

        $cacheKey = "warehouse_amc_import_{$this->importId}";
        $currentStatus = Cache::get($cacheKey, []);
        
        $updatedStatus = array_merge($currentStatus, [
            'status' => $status,
            'progress' => $progress ?? ($this->totalRows > 0 ? round(($this->processedRows / $this->totalRows) * 100) : 0),
            'message' => $message ?? "Processed {$this->processedRows} of {$this->totalRows} rows",
            'processed_rows' => $this->processedRows,
            'total_rows' => $this->totalRows,
            'errors' => $this->errors,
            'updated_at' => now()->toISOString(),
        ]);
        
        Cache::put($cacheKey, $updatedStatus, 3600); // 1 hour expiry
    }

    /**
     * Parse month year from formatted header (e.g., "Feb 2025" -> "2025-02")
     */
    private function parseMonthYear($headerValue)
    {
        if (!$headerValue || trim($headerValue) === '') {
            return null;
        }

        $headerValue = trim($headerValue);
        Log::info("Parsing month year from: '{$headerValue}'");

        // Try to parse various date formats
        $formats = [
            'M Y',      // Feb 2025
            'F Y',      // February 2025
            'Y-m',      // 2025-02
            'm/Y',      // 02/2025
            'm-Y',      // 02-2025
        ];

        foreach ($formats as $format) {
            try {
                $date = \DateTime::createFromFormat($format, $headerValue);
                if ($date) {
                    $result = $date->format('Y-m');
                    Log::info("Successfully parsed '{$headerValue}' using format '{$format}' -> '{$result}'");
                    return $result;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // If no format matches, try to extract year and month manually
        if (preg_match('/(\w+)\s+(\d{4})/', $headerValue, $matches)) {
            $monthName = $matches[1];
            $year = $matches[2];
            
            $monthNumber = date('m', strtotime($monthName . ' 1'));
            if ($monthNumber) {
                $result = $year . '-' . str_pad($monthNumber, 2, '0', STR_PAD_LEFT);
                Log::info("Manually parsed '{$headerValue}' -> '{$result}'");
                return $result;
            }
        }

        // Try to handle Excel's date format (e.g., "2025-01-01" -> "2025-01")
        if (preg_match('/^(\d{4})-(\d{1,2})/', $headerValue, $matches)) {
            $year = $matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $result = $year . '-' . $month;
            Log::info("Excel date format parsed '{$headerValue}' -> '{$result}'");
            return $result;
        }

        Log::warning("Could not parse month year from: '{$headerValue}'");
        return null;
    }

    /**
     * Clean and validate quantity value
     */
    private function cleanQuantity($value)
    {
        // Return null for empty or null values
        if ($value === null || $value === '' || trim($value) === '') {
            return null;
        }

        // Remove any non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        
        if ($cleaned === '' || $cleaned === '-') {
            return null;
        }

        $quantity = (float) $cleaned;
        
        // Ensure non-negative
        return max(0, $quantity);
    }
}


