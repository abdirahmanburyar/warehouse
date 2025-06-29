<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ProductsImport extends DefaultValueBinder implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    WithChunkReading, 
    WithBatchInserts,
    WithCustomValueBinder,
    ShouldQueue
{
    use Importable;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $categoryCache = [];
    protected $dosageCache = [];
    protected $importId;
    protected $filePath;

    public function __construct($filePath = null)
    {
        // Generate a unique import ID
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
     * Transform a row from the Excel file into a Product model
     */
    public function model(array $row)
    {
        try {
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

            // Return new Product model instance
            return new Product([
                'name' => $itemName,
                'category_id' => $categoryId,
                'dosage_id' => $dosageId,
                'movement' => 'Slow Moving', // Default movement value
                'is_active' => true, // Default value
            ]);

        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            $this->addError("Failed to process row: {$e->getMessage()}");
            $this->incrementSkipped();
            return null;
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'item_description' => 'required|string',
            'category' => 'nullable|string',
            'dosage_form' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'item_description.required' => 'Item description is required',
        ];
    }

    /**
     * Define the chunk size for reading the Excel file
     */
    public function chunkSize(): int
    {
        return 100; // Process 100 rows at a time for better memory management
    }

    /**
     * Define the batch size for database inserts
     */
    public function batchSize(): int
    {
        return 50; // Insert 50 records at a time
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

    /**
     * Clean up after import
     */
    public function __destruct()
    {
        // Clean up the stored file
        if ($this->filePath && file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
