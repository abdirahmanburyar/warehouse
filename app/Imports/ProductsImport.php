<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            // Validate file extension
            $extension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                throw new \Exception("Unsupported file format: {$extension}");
            }

            // Create the reader based on file extension
            $reader = ReaderEntityFactory::createReaderFromFile($this->filePath);
            $reader->open($this->filePath);

            $headers = null;
            $isFirstRow = true;
            $rowCount = 0;
            $dataRows = 0;

            // Iterate through all sheets
            foreach ($reader->getSheetIterator() as $sheet) {
                // Iterate through all rows
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $rowCount++;
                    
                    try {
                        $values = $row->toArray();
                    } catch (\Exception $e) {
                        Log::warning("Failed to read row {$rowIndex}", ['error' => $e->getMessage()]);
                        continue;
                    }

                    // Skip empty rows
                    if (empty($values) || (count($values) === 1 && empty(trim($values[0])))) {
                        continue;
                    }

                    // Handle headers
                    if ($isFirstRow) {
                        try {
                            $headers = $this->validateHeaders($values);
                            $isFirstRow = false;
                            continue;
                        } catch (\Exception $e) {
                            throw new \Exception("Header validation failed: " . $e->getMessage());
                        }
                    }

                    // Process row
                    try {
                        // Validate that we have headers
                        if ($headers === null) {
                            throw new \Exception("Headers not found. Please ensure the first row contains column headers.");
                        }

                        // Handle mismatched column counts with extra safety
                        $rowData = $this->safeNormalizeRowData($headers, $values, $rowIndex);
                        
                        if ($rowData !== null) {
                            $productData = $this->processRow($rowData);
                            
                            if ($productData !== null) {
                                // Create product using create method instead of batch insert
                                Product::create($productData);
                                $this->importedCount++;
                                $dataRows++;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->errors[] = "Row {$rowIndex}: " . $e->getMessage();
                        $this->skippedCount++;
                        Log::warning("Skipped row {$rowIndex} during import", [
                            'error' => $e->getMessage(),
                            'row_data' => $values,
                            'headers_count' => $headers ? count($headers) : 0,
                            'values_count' => count($values)
                        ]);
                    }
                }
            }

            $reader->close();

            // Validate that we processed some data
            if ($dataRows === 0) {
                throw new \Exception("No valid data rows found in the file. Please check the file format and ensure it contains the required columns.");
            }

            return [
                'imported' => $this->importedCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors,
                'total_rows' => $rowCount - 1, // Subtract header row
                'data_rows' => $dataRows
            ];

        } catch (\Exception $e) {
            Log::error('Import error', [
                'error' => $e->getMessage(),
                'file' => $this->filePath,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Validate and process headers
     */
    protected function validateHeaders($values)
    {
        if (!is_array($values)) {
            throw new \Exception("Invalid header row: not an array");
        }

        $headers = array_map('strtolower', $values);
        
        // Check for required column
        if (!in_array('item description', $headers)) {
            throw new \Exception("Required column 'item description' not found in headers: " . implode(', ', $headers));
        }

        // Log found headers for debugging
        Log::info('Excel headers found', [
            'headers' => $headers,
            'count' => count($headers)
        ]);

        return $headers;
    }

    /**
     * Safe normalize row data with extensive error checking
     */
    protected function safeNormalizeRowData($headers, $values, $rowIndex)
    {
        // Validate inputs
        if (!is_array($headers) || !is_array($values)) {
            Log::warning("Row {$rowIndex}: Invalid data types", [
                'headers_type' => gettype($headers),
                'values_type' => gettype($values)
            ]);
            return null;
        }

        $headerCount = count($headers);
        $valueCount = count($values);

        // Log the counts for debugging
        Log::debug("Row {$rowIndex}: Processing", [
            'header_count' => $headerCount,
            'value_count' => $valueCount,
            'headers' => $headers,
            'values' => $values
        ]);

        // Filter out empty values and normalize
        $values = array_map(function($value) {
            if (is_string($value)) {
                return trim($value);
            }
            return $value;
        }, $values);

        // If we have more values than headers, truncate the values
        if ($valueCount > $headerCount) {
            $values = array_slice($values, 0, $headerCount);
            Log::warning("Row {$rowIndex}: Truncated excess columns", [
                'original_count' => $valueCount,
                'truncated_count' => $headerCount
            ]);
        }
        // If we have fewer values than headers, pad with empty strings
        elseif ($valueCount < $headerCount) {
            $values = array_pad($values, $headerCount, '');
            Log::warning("Row {$rowIndex}: Padded missing columns", [
                'original_count' => $valueCount,
                'padded_count' => $headerCount
            ]);
        }

        // Validate that we have valid data after normalization
        if (empty($values) || (count($values) === 1 && empty($values[0]))) {
            Log::info("Row {$rowIndex}: Skipping empty row after normalization");
            return null; // Skip completely empty rows
        }

        // Final validation before array_combine
        if (count($headers) !== count($values)) {
            Log::error("Row {$rowIndex}: Critical error - header and value counts still don't match after normalization", [
                'final_header_count' => count($headers),
                'final_value_count' => count($values),
                'headers' => $headers,
                'values' => $values
            ]);
            return null;
        }

        // Now we can safely combine with extra error checking
        try {
            $result = array_combine($headers, $values);
            if ($result === false) {
                Log::error("Row {$rowIndex}: array_combine failed", [
                    'headers' => $headers,
                    'values' => $values
                ]);
                return null;
            }
            return $result;
        } catch (\Exception $e) {
            Log::error("Row {$rowIndex}: Exception in array_combine", [
                'error' => $e->getMessage(),
                'headers' => $headers,
                'values' => $values
            ]);
            return null;
        }
    }

    /**
     * Process a single row from the Excel file
     */
    protected function processRow($row)
    {
        // Validate required fields
        if (empty($row['item description']) || trim($row['item description']) === '') {
            throw new \Exception("Missing or empty item description");
        }

        $itemName = trim($row['item description']);
        
        // Validate item name
        if (strlen($itemName) > 255) {
            throw new \Exception("Item description too long (max 255 characters): " . substr($itemName, 0, 50) . "...");
        }

        // Sanitize and validate category and dosage
        $category = !empty($row['category']) ? trim($row['category']) : null;
        $dosageForm = !empty($row['dosage form']) ? trim($row['dosage form']) : null;

        // Validate category length if provided
        if ($category && strlen($category) > 255) {
            throw new \Exception("Category name too long (max 255 characters): " . substr($category, 0, 50) . "...");
        }

        // Validate dosage length if provided
        if ($dosageForm && strlen($dosageForm) > 255) {
            throw new \Exception("Dosage form name too long (max 255 characters): " . substr($dosageForm, 0, 50) . "...");
        }

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
