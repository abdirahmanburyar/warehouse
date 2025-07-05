<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\Log;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ProductsImport
{
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $categoryCache = [];
    protected $dosageCache = [];
    protected $filePath;
    protected $batchSize = 1000;

    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }
        
        $this->filePath = $filePath;
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
                        if (!in_array('item description', $headers)) {
                            throw new \Exception("Required column 'item description' not found");
                        }
                        $isFirstRow = false;
                        continue;
                    }

                    // Process row
                    try {
                        $rowData = array_combine($headers, $values);
                        $productData = $this->processRow($rowData);
                        
                        if ($productData !== null) {
                            // Create product using create method instead of batch insert
                            Product::create($productData);
                            $this->importedCount++;
                        }
                    } catch (\Exception $e) {
                        $this->errors[] = "Row {$rowIndex}: " . $e->getMessage();
                        $this->skippedCount++;
                    }
                }
            }

            $reader->close();

            return [
                'imported' => $this->importedCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors,
                'total_rows' => $rowCount - 1 // Subtract header row
            ];

        } catch (\Exception $e) {
            Log::error('Import error', [
                'error' => $e->getMessage(),
                'file' => $this->filePath
            ]);
            throw $e;
        }
    }

    /**
     * Process a single row from the Excel file
     */
    protected function processRow($row)
    {
        if (empty($row['item description'])) {
            throw new \Exception("Missing item description");
        }

        $itemName = trim($row['item description']);
        $category = !empty($row['category']) ? trim($row['category']) : null;
        $dosageForm = !empty($row['dosage form']) ? trim($row['dosage form']) : null;

        // Check for duplicate product
        if (Product::where('name', $itemName)->exists()) {
            throw new \Exception("Product '{$itemName}' already exists");
        }

        // Handle category
        $categoryId = null;
        if ($category) {
            if (!isset($this->categoryCache[$category])) {
                $categoryModel = Category::firstOrCreate(
                    ['name' => $category],
                    ['is_active' => true]
                );
                $this->categoryCache[$category] = $categoryModel->id;
            }
            $categoryId = $this->categoryCache[$category];
        }

        // Handle dosage
        $dosageId = null;
        if ($dosageForm) {
            if (!isset($this->dosageCache[$dosageForm])) {
                $dosageModel = Dosage::firstOrCreate(['name' => $dosageForm]);
                $this->dosageCache[$dosageForm] = $dosageModel->id;
            }
            $dosageId = $this->dosageCache[$dosageForm];
        }

        return [
            'name' => $itemName,
            'category_id' => $categoryId,
            'dosage_id' => $dosageId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
