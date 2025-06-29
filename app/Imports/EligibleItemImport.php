<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\EligibleItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class EligibleItemImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importId;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $validFacilityTypes = [
        'Health Centre',
        'Primary Health Unit',
        'District Hospital',
        'Regional Hospital'
    ];

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $importId)
    {
        $this->filePath = $filePath;
        $this->importId = $importId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            if (!file_exists($this->filePath)) {
                throw new \Exception("Import file not found: {$this->filePath}");
            }

            // Create the reader based on file extension
            $reader = ReaderEntityFactory::createReaderFromFile($this->filePath);
            $reader->open($this->filePath);

            $headers = null;
            $isFirstRow = true;
            $rowCount = 0;

            // Iterate through all sheets
            foreach ($reader->getSheetIterator() as $sheet) {
                // Iterate through all rows
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $rowCount++;
                    $values = $row->toArray();

                    // Handle headers
                    if ($isFirstRow) {
                        $headers = array_map('strtolower', $values);
                        if (!in_array('item description', $headers) || !in_array('facility type', $headers)) {
                            throw new \Exception("Required columns 'item description' and 'facility type' not found");
                        }
                        $isFirstRow = false;
                        continue;
                    }

                    // Process row
                    try {
                        $rowData = array_combine($headers, $values);
                        $this->processRow($rowData, $rowIndex);
                    } catch (\Exception $e) {
                        $this->errors[] = "Row {$rowIndex}: " . $e->getMessage();
                        $this->skippedCount++;
                    }
                }
            }

            $reader->close();

            // Store results in cache
            $results = [
                'imported' => $this->importedCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors,
                'total_rows' => $rowCount - 1 // Subtract header row
            ];

            Cache::put("import_{$this->importId}_results", $results, now()->addHours(1));

            // Clean up all files
            $this->cleanupFiles();

            Log::info('Eligible items import completed', [
                'import_id' => $this->importId,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Import failed', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Store error in cache
            Cache::put("import_{$this->importId}_error", $e->getMessage(), now()->addHours(1));
            
            // Clean up all files
            $this->cleanupFiles();

            throw $e;
        }
    }

    /**
     * Clean up all related files
     */
    protected function cleanupFiles()
    {
        try {
            // Delete the file in storage
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            // Delete the file from public/excel-imports if it exists
            $publicPath = public_path('excel-imports/' . basename($this->filePath));
            if (file_exists($publicPath)) {
                unlink($publicPath);
            }

            // Delete from storage/app/public/excel-imports if it exists
            $storagePath = storage_path('app/public/excel-imports/' . basename($this->filePath));
            if (file_exists($storagePath)) {
                unlink($storagePath);
            }

            Log::info('Cleaned up import files', [
                'import_id' => $this->importId,
                'files_cleaned' => [
                    'storage' => $this->filePath,
                    'public' => $publicPath,
                    'storage_public' => $storagePath
                ]
            ]);
        } catch (\Exception $e) {
            Log::warning('Error cleaning up import files', [
                'import_id' => $this->importId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process a single row from the Excel file
     */
    protected function processRow($row, $rowIndex)
    {
        if (empty($row['item description'])) {
            throw new \Exception("Missing item description");
        }

        if (empty($row['facility type'])) {
            throw new \Exception("Missing facility type");
        }

        $itemName = trim($row['item description']);
        $facilityType = trim($row['facility type']);

        // Validate facility type
        if (!in_array($facilityType, $this->validFacilityTypes)) {
            throw new \Exception("Invalid facility type: {$facilityType}");
        }

        // Find the product
        $product = Product::where('name', $itemName)->first();
        if (!$product) {
            throw new \Exception("Product not found: {$itemName}");
        }

        // Check if eligible item already exists
        if (EligibleItem::where('product_id', $product->id)
                       ->where('facility_type', $facilityType)
                       ->exists()) {
            throw new \Exception("Eligible item already exists for this product and facility type");
        }

        // Create the eligible item
        EligibleItem::create([
            'product_id' => $product->id,
            'facility_type' => $facilityType
        ]);

        $this->importedCount++;
    }
} 