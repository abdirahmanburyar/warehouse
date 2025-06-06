<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductsImport implements ToCollection
{
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $batchSize = 100; // Process records in batches of 100

    /**
     * Process the collection of rows from the Excel file
     */
    public function collection(Collection $rows)
    {
        // Get headers to identify columns
        $headers = $rows->first();
        if (!$headers) return;
        
        // Find column indexes
        $itemDescIndex = null;
        $categoryIndex = null;
        $dosageFormIndex = null;
        
        foreach ($headers as $key => $header) {
            if (is_string($header)) {
                $header = trim(strtolower($header));
                if ($header === 'item description') {
                    $itemDescIndex = $key;
                } else if ($header === 'category') {
                    $categoryIndex = $key;
                } else if ($header === 'dosage form') {
                    $dosageFormIndex = $key;
                }
            }
        }
        
        // Validate that we found the required columns
        if ($itemDescIndex === null) {
            $this->errors[] = "Item Description column not found in Excel file";
            return;
        }
        
        // Increase PHP execution time limit for large imports
        set_time_limit(300); // 5 minutes
        
        DB::beginTransaction();
        try {
            // Prepare for batch processing
            $records = [];
            $categoryCache = [];
            $dosageCache = [];
            
            // Process data rows
            foreach ($rows->skip(1) as $rowIndex => $row) {
                // Skip empty rows
                if (empty($row[$itemDescIndex])) {
                    $this->errors[] = "Row skipped: Missing item description";
                    $this->skippedCount++;
                    continue;
                }
                
                // Trim whitespace from values
                $itemName = trim($row[$itemDescIndex]);
                $category = ($categoryIndex !== null && !empty($row[$categoryIndex])) ? trim($row[$categoryIndex]) : null;
                $dosageForm = ($dosageFormIndex !== null && !empty($row[$dosageFormIndex])) ? trim($row[$dosageFormIndex]) : null;

                // Check if product already exists
                $existingProduct = Product::where('name', $itemName)->first();
                if ($existingProduct) {
                    $this->errors[] = "Row skipped: Product '{$itemName}' already exists";
                    $this->skippedCount++;
                    continue;
                }

                // Find or create category if provided
                $categoryId = null;
                if ($category) {
                    // Use cached category ID if available
                    if (isset($categoryCache[$category])) {
                        $categoryId = $categoryCache[$category];
                    } else {
                        // Use firstOrCreate for better performance
                        $categoryModel = Category::firstOrCreate(
                            ['name' => $category],
                            ['is_active' => true]
                        );
                        $categoryId = $categoryModel->id;
                        
                        // Cache the category ID
                        $categoryCache[$category] = $categoryId;
                    }
                }

                // Find or create dosage form if provided
                $dosageId = null;
                if ($dosageForm) {
                    // Use cached dosage ID if available
                    if (isset($dosageCache[$dosageForm])) {
                        $dosageId = $dosageCache[$dosageForm];
                    } else {
                        // Use firstOrCreate for better performance
                        $dosageModel = Dosage::firstOrCreate(['name' => $dosageForm]);
                        $dosageId = $dosageModel->id;
                        
                        // Cache the dosage ID
                        $dosageCache[$dosageForm] = $dosageId;
                    }
                }

                // Create product record using the model to trigger the boot method
                try {
                    Product::create([
                        'name' => $itemName,
                        'category_id' => $categoryId,
                        'dosage_id' => $dosageId,
                        'movement' => 'Slow Moving', // Default movement value
                        'is_active' => true, // Default value
                    ]);
                    
                    // Increment imported count only if creation is successful
                    $this->importedCount++;
                } catch (\Exception $e) {
                    $this->errors[] = "Failed to create product '{$itemName}': {$e->getMessage()}";
                    $this->skippedCount++;
                    continue;
                }

                // Log progress for large imports
                if ($this->importedCount % 500 === 0) {
                    Log::info("Processed {$this->importedCount} products so far");
                }
            }
            
            // Log final processing count
            Log::info("Processed final batch, total: {$this->importedCount} products");
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product import error: ' . $e->getMessage());
            $this->errors[] = "Import failed: " . $e->getMessage();
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
