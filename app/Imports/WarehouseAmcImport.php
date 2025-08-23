<?php

namespace App\Imports;

use App\Models\WarehouseAmc;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class WarehouseAmcImport implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    WithValidation, 
    SkipsEmptyRows, 
    WithEvents,
    ShouldQueue
{
    use InteractsWithQueue, Queueable;
    protected $importedCount = 0;
    protected $updatedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $productCache = [];
    protected $importId;
    protected $monthYears = [];

    public function __construct(string $importId)
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
            if (empty($row['item'])) {
                $this->skippedCount++;
                return null;
            }

            $itemName = trim($row['item']);

            // Find the product by name
            $product = Product::where('name', $itemName)->first();
            
            if (!$product) {
                // Product doesn't exist, skip this row and continue
                $this->errors[] = "Product not found: {$itemName} - Skipped";
                $this->skippedCount++;
                return null;
            }

            // Update progress in cache
            Cache::increment($this->importId);

            // Process each month column and create/update WarehouseAmc records
            $processedMonths = 0;
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
                
                // If quantity is null or empty, skip this month (don't update existing data)
                if ($quantity === null) {
                    continue;
                }

                // Create or update WarehouseAmc record
                $warehouseAmc = WarehouseAmc::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'month_year' => $monthYear,
                    ],
                    [
                        'quantity' => $quantity,
                    ]
                );

                if ($warehouseAmc->wasRecentlyCreated) {
                    $this->importedCount++;
                } else {
                    $this->updatedCount++;
                }
                
                $processedMonths++;
            }

            // If no months were processed, count as skipped
            if ($processedMonths === 0) {
                $this->skippedCount++;
                return null;
            }

            // Return null since we're handling the creation manually
            return null;

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row for '{$row['item']}': " . $e->getMessage();
            $this->skippedCount++;
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'item' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                // Update cache with final results
                $results = $this->getResults();
                $message = "Import completed successfully. ";
                if ($results['imported'] > 0) {
                    $message .= "Created: {$results['imported']} new AMC records. ";
                }
                if ($results['updated'] > 0) {
                    $message .= "Updated: {$results['updated']} existing AMC records. ";
                }
                if ($results['skipped'] > 0) {
                    $message .= "Skipped: {$results['skipped']} rows. ";
                }

                Cache::put("warehouse_amc_import_{$this->importId}", [
                    'status' => 'completed',
                    'progress' => 100,
                    'message' => trim($message),
                    'results' => $results
                ], 3600);
                
                // Log completion
                Log::info("Warehouse AMC import completed", [
                    'import_id' => $this->importId,
                    'imported' => $this->importedCount,
                    'updated' => $this->updatedCount,
                    'skipped' => $this->skippedCount,
                    'errors' => $this->errors
                ]);
            },
        ];
    }

    public function getResults()
    {
        return [
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
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
                    return $date->format('Y-m');
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
                return $year . '-' . str_pad($monthNumber, 2, '0', STR_PAD_LEFT);
            }
        }

        // Try to handle Excel's date format (e.g., "2025-01-01" -> "2025-01")
        if (preg_match('/^(\d{4})-(\d{1,2})/', $headerValue, $matches)) {
            $year = $matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            return $year . '-' . $month;
        }

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


