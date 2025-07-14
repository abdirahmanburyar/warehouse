<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

class ProductsImport implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    WithValidation, 
    SkipsOnError, 
    SkipsEmptyRows,
    WithProgressBar,
    WithCalculatedFormulas
{
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $categoryCache = [];
    protected $dosageCache = [];
    protected $importId;

    public function __construct($importId = null)
    {
        $this->importId = $importId;
    }

    /**
     * @param array $row
     */
    public function model(array $row)
    {
        try {
            // Skip if no item description
            if (empty($row['item_description'])) {
                $this->skippedCount++;
                return null;
            }

            $itemName = trim($row['item_description']);
            
            // Validate item name length
            if (strlen($itemName) > 255) {
                $this->errors[] = "Item description too long: " . substr($itemName, 0, 50) . "...";
                $this->skippedCount++;
                return null;
            }

            // Check for duplicate product
            if (Product::where('name', $itemName)->exists()) {
                $this->errors[] = "Product '{$itemName}' already exists";
                $this->skippedCount++;
                return null;
            }

            // Handle category
            $categoryId = null;
            if (!empty($row['category'])) {
                $category = trim($row['category']);
                if (strlen($category) > 255) {
                    $this->errors[] = "Category name too long: " . substr($category, 0, 50) . "...";
                    $this->skippedCount++;
                    return null;
                }
                
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
            if (!empty($row['dosage_form'])) {
                $dosageForm = trim($row['dosage_form']);
                if (strlen($dosageForm) > 255) {
                    $this->errors[] = "Dosage form name too long: " . substr($dosageForm, 0, 50) . "...";
                    $this->skippedCount++;
                    return null;
                }
                
                if (!isset($this->dosageCache[$dosageForm])) {
                    $dosageModel = Dosage::firstOrCreate(['name' => $dosageForm]);
                    $this->dosageCache[$dosageForm] = $dosageModel->id;
                }
                $dosageId = $this->dosageCache[$dosageForm];
            }

            $this->importedCount++;

            return new Product([
                'name' => $itemName,
                'category_id' => $categoryId,
                'dosage_id' => $dosageId,
                'is_active' => true,
            ]);

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            return null;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'item_description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'item_description.required' => 'Item description is required.',
            'item_description.max' => 'Item description cannot exceed 255 characters.',
            'category.max' => 'Category name cannot exceed 255 characters.',
            'dosage_form.max' => 'Dosage form name cannot exceed 255 characters.',
        ];
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
            $this->skippedCount++;
        }
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500; // Process 1000 rows at a time
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 500; // Insert 100 records at a time
    }

    /**
     * Get the results of the import
     */
    public function getResults()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }

    /**
     * Get the errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the imported count
     */
    public function getImportedCount()
    {
        return $this->importedCount;
    }

    /**
     * Get the skipped count
     */
    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}
