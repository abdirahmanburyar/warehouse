<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ProductsImport
{
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $categoryCache = [];
    protected $dosageCache = [];
    protected $importId;
    protected $filePath;
    protected $batchSize = 1000; // Process 1000 rows at a time

    public function __construct($filePath = null)
    {
        $this->importId = uniqid('import_', true);
        $this->filePath = $filePath;
        
        // Initialize counters in cache
        Cache::put("import_{$this->importId}_imported", 0, now()->addHours(24));
        Cache::put("import_{$this->importId}_skipped", 0, now()->addHours(24));
        Cache::put("import_{$this->importId}_errors", [], now()->addHours(24));

        // Store the file in a persistent location if provided
        if ($this->filePath) {
            $storagePath = Storage::disk('local')->path('excel-imports');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            $newPath = $storagePath . '/' . basename($this->filePath);
            copy($this->filePath, $newPath);
            $this->filePath = $newPath;
        }
    }

    /**
     * Import the products from the Excel file
     */
    public function import()
    {
        try {
            // Create the reader based on file extension
            $reader = ReaderEntityFactory::createReaderFromFile($this->filePath);
            $reader->open($this->filePath);

            $batch = [];
            $headers = null;
            $isFirstRow = true;

            // Iterate through all sheets
            foreach ($reader->getSheetIterator() as $sheet) {
                // Iterate through all rows
                foreach ($sheet->getRowIterator() as $row) {
                    // Get the row as an array
                    $values = $row->toArray();

                    // If this is the first row, store headers
                    if ($isFirstRow) {
                        $headers = array_map('strtolower', $values);
                        $isFirstRow = false;
                        continue;
                    }

                    // Create associative array combining headers with values
                    $rowData = array_combine($headers, $values);

                    try {
                        $product = $this->processRow($rowData);
                        if ($product !== null) {
                            $batch[] = $product;
                        }

                        // If we've reached batch size, insert the batch
                        if (count($batch) >= $this->batchSize) {
                            Product::insert($batch);
                            $batch = [];
                        }
                    } catch (\Exception $e) {
                        Log::error('Import row error: ' . $e->getMessage());
                        $this->addError("Failed to process row: {$e->getMessage()}");
                        $this->incrementSkipped();
                    }
                }
            }

            // Insert any remaining batch items
            if (!empty($batch)) {
                Product::insert($batch);
            }

            $reader->close();

            return [
                'imported' => $this->getImportedCount(),
                'skipped' => $this->getSkippedCount(),
                'errors' => $this->getErrors()
            ];

        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            throw $e;
        } finally {
            // Cleanup
            if ($this->filePath && file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }

    /**
     * Process a single row from the Excel file
     */
    protected function processRow($row)
    {
        // Check if required field exists
        if (empty($row['item_description'])) {
            $this->addError("Row skipped: Missing item description");
            $this->incrementSkipped();
            return null;
        }

        $itemName = trim($row['item_description']);
        $category = !empty($row['category']) ? trim($row['category']) : null;
        $dosageForm = !empty($row['dosage_form']) ? trim($row['dosage_form']) : null;

        // Check if product already exists
        if (Product::where('name', $itemName)->exists()) {
            $this->addError("Row skipped: Product '{$itemName}' already exists");
            $this->incrementSkipped();
            return null;
        }

        // Find or create category if provided
        $categoryId = null;
        if ($category) {
            if (isset($this->categoryCache[$category])) {
                $categoryId = $this->categoryCache[$category];
            } else {
                $categoryModel = Category::firstOrCreate(
                    ['name' => $category],
                    ['is_active' => true]
                );
                $categoryId = $categoryModel->id;
                $this->categoryCache[$category] = $categoryId;
            }
        }

        // Find or create dosage form if provided
        $dosageId = null;
        if ($dosageForm) {
            if (isset($this->dosageCache[$dosageForm])) {
                $dosageId = $this->dosageCache[$dosageForm];
            } else {
                $dosageModel = Dosage::firstOrCreate(['name' => $dosageForm]);
                $dosageId = $dosageModel->id;
                $this->dosageCache[$dosageForm] = $dosageId;
            }
        }

        $this->incrementImported();

        // Return data for batch insert
        return [
            'name' => $itemName,
            'category_id' => $categoryId,
            'dosage_id' => $dosageId,
            'movement' => 'Slow Moving', // Default movement value
            'is_active' => true, // Default value
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get the import ID
     */
    public function getImportId(): string
    {
        return $this->importId;
    }

    /**
     * Increment imported count in cache
     */
    protected function incrementImported(): void
    {
        Cache::increment("import_{$this->importId}_imported");
    }

    /**
     * Increment skipped count in cache
     */
    protected function incrementSkipped(): void
    {
        Cache::increment("import_{$this->importId}_skipped");
    }

    /**
     * Add error to cache
     */
    protected function addError(string $error): void
    {
        $errors = Cache::get("import_{$this->importId}_errors", []);
        $errors[] = $error;
        Cache::put("import_{$this->importId}_errors", $errors, now()->addHours(24));
    }

    /**
     * Get imported count from cache
     */
    public function getImportedCount(): int
    {
        return (int) Cache::get("import_{$this->importId}_imported", 0);
    }

    /**
     * Get skipped count from cache
     */
    public function getSkippedCount(): int
    {
        return (int) Cache::get("import_{$this->importId}_skipped", 0);
    }

    /**
     * Get errors from cache
     */
    public function getErrors(): array
    {
        return Cache::get("import_{$this->importId}_errors", []);
    }
}
