<?php

namespace App\Imports;

use App\Models\WarehouseAmc;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
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

class WarehouseAmcImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, WithProgressBar, WithEvents
{
    use Importable;

    protected $monthYears;
    protected $products;
    protected $errors = [];
    protected $importId;
    protected $totalRows = 0;
    protected $processedRows = 0;

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

    public function model(array $row)
    {
        try {
            // Skip empty rows
            if (empty($row['item']) || trim($row['item']) === '') {
                return null;
            }

            // Find product by name (exact match first, then partial)
            $product = Product::where('name', '=', trim($row['item']))->first();
            
            if (!$product) {
                $product = Product::where('name', 'like', '%' . trim($row['item']) . '%')->first();
            }
            
            if (!$product) {
                $this->errors[] = "Product '{$row['item']}' not found";
                Log::warning("Product not found during import: {$row['item']}");
                return null;
            }

            // Process each month column
            foreach ($row as $key => $value) {
                // Skip non-month columns
                if (in_array($key, ['item', 'category', 'dosage_form'])) {
                    continue;
                }

                // Convert formatted month back to YYYY-MM format
                $monthYear = $this->parseMonthYear($key);
                if (!$monthYear) {
                    continue;
                }

                // Clean and validate quantity
                $quantity = $this->cleanQuantity($value);
                if ($quantity === null) {
                    continue;
                }

                // Update or create warehouse AMC record
                WarehouseAmc::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'month_year' => $monthYear,
                    ],
                    [
                        'quantity' => $quantity,
                    ]
                );
            }

            // Update progress
            $this->processedRows++;
            $this->updateProgress();

            // Return null since we're handling the creation manually
            return null;

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row for '{$row['item']}': " . $e->getMessage();
            Log::error("Import error for row: " . json_encode($row) . " - " . $e->getMessage());
            return null;
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
                $date = \DateTime::createFromFormat($format, trim($headerValue));
                if ($date) {
                    return $date->format('Y-m');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // If no format matches, try to extract year and month manually
        if (preg_match('/(\w+)\s+(\d{4})/', trim($headerValue), $matches)) {
            $monthName = $matches[1];
            $year = $matches[2];
            
            $monthNumber = date('m', strtotime($monthName . ' 1'));
            if ($monthNumber) {
                return $year . '-' . str_pad($monthNumber, 2, '0', STR_PAD_LEFT);
            }
        }

        return null;
    }

    /**
     * Clean and validate quantity value
     */
    private function cleanQuantity($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        // Remove any non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        
        if ($cleaned === '' || $cleaned === '-') {
            return 0;
        }

        $quantity = (float) $cleaned;
        
        // Ensure non-negative
        return max(0, $quantity);
    }
}
