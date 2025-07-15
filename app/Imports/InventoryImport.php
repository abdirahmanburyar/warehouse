<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
// use App\Events\InventoryUpdated;

class InventoryImport implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    WithValidation, 
    SkipsEmptyRows, 
    WithEvents, 
    ShouldQueue
{
    use Queueable, InteractsWithQueue;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $productCache = [];
    protected $categoryCache = [];
    protected $importId;

    public function __construct(string $importId)
    {
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        try {
           logger()->info($row);

        } catch (\Throwable $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            logger()->error('InventoryImport error', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function getOrCreateProduct($itemName, $categoryName = null)
    {
        logger()->info('InventoryImport: getOrCreateProduct called', [
            'itemName' => $itemName,
            'categoryName' => $categoryName
        ]);
        
        // Check cache first
        if (isset($this->productCache[$itemName])) {
            logger()->info('InventoryImport: Product found in cache', ['itemName' => $itemName]);
            return $this->productCache[$itemName];
        }

        // Try to find existing product
        $product = Product::where('name', $itemName)->first();
        
        if (!$product) {
            logger()->info('InventoryImport: Product not found, creating new one', ['itemName' => $itemName]);
            
            // Create new product if it doesn't exist
            $categoryId = null;
            if ($categoryName) {
                $categoryId = $this->getOrCreateCategory($categoryName);
            }

            $product = Product::create([
                'name' => $itemName,
                'category_id' => $categoryId,
                'is_active' => true,
            ]);
            
            logger()->info('InventoryImport: New product created', [
                'productId' => $product->id,
                'productName' => $product->name
            ]);
        } else {
            logger()->info('InventoryImport: Existing product found', [
                'productId' => $product->id,
                'productName' => $product->name
            ]);
        }

        $this->productCache[$itemName] = $product;
        return $product;
    }

    protected function getOrCreateCategory($categoryName)
    {
        logger()->info('InventoryImport: getOrCreateCategory called', ['categoryName' => $categoryName]);
        
        if (isset($this->categoryCache[$categoryName])) {
            logger()->info('InventoryImport: Category found in cache', ['categoryName' => $categoryName]);
            return $this->categoryCache[$categoryName];
        }

        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['is_active' => true]
        );

        logger()->info('InventoryImport: Category processed', [
            'categoryId' => $category->id,
            'categoryName' => $category->name,
            'wasCreated' => $category->wasRecentlyCreated
        ]);

        $this->categoryCache[$categoryName] = $category->id;
        return $category->id;
    }



    public function rules(): array
    {
        return [
            'item' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'uom' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
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
                logger()->info('InventoryImport: AfterImport event triggered', [
                    'importId' => $this->importId,
                    'imported' => $this->importedCount,
                    'skipped' => $this->skippedCount,
                    'errors' => count($this->errors),
                    'errorDetails' => $this->errors
                ]);
                
                Cache::forget($this->importId);
                logger()->info('Inventory import completed', [
                    'importId' => $this->importId,
                    'imported' => $this->importedCount,
                    'skipped' => $this->skippedCount,
                    'errors' => count($this->errors)
                ]);
                // event(new InventoryUpdated($this->importId, 'completed'));
            },
        ];
    }

    public function getResults()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }
}
