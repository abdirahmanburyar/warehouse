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
            logger()->info('InventoryImport: Processing row', ['row' => $row]);
            
            // Skip if required fields are empty
            if (empty($row['item']) || empty($row['quantity']) || empty($row['batch_no'])) {
                logger()->warning('InventoryImport: Skipping row - missing required fields', [
                    'item' => $row['item'] ?? 'empty',
                    'quantity' => $row['quantity'] ?? 'empty',
                    'batch_no' => $row['batch_no'] ?? 'empty'
                ]);
                $this->skippedCount++;
                return null;
            }

            $itemName = trim($row['item']);
            $quantity = (float) $row['quantity'];
            $batchNo = trim($row['batch_no']);

            logger()->info('InventoryImport: Parsed values', [
                'itemName' => $itemName,
                'quantity' => $quantity,
                'batchNo' => $batchNo
            ]);

            // Validate quantity
            if ($quantity <= 0) {
                logger()->warning('InventoryImport: Invalid quantity', [
                    'itemName' => $itemName,
                    'quantity' => $quantity
                ]);
                $this->errors[] = "Invalid quantity for item: {$itemName}";
                $this->skippedCount++;
                return null;
            }

            // Find or create product
            logger()->info('InventoryImport: Getting/creating product', ['itemName' => $itemName]);
            $product = $this->getOrCreateProduct($itemName, $row['category'] ?? null);
            if (!$product) {
                logger()->error('InventoryImport: Failed to get/create product', ['itemName' => $itemName]);
                $this->errors[] = "Could not create/find product: {$itemName}";
                $this->skippedCount++;
                return null;
            }
            logger()->info('InventoryImport: Product found/created', ['productId' => $product->id, 'productName' => $product->name]);

            // Use default warehouse_id = 1 and location
            $warehouseId = 1; // Default warehouse
            $locationName = $row['location'] ?? 'Default Location';
            
            logger()->info('InventoryImport: Warehouse and location set', [
                'warehouseId' => $warehouseId,
                'locationName' => $locationName
            ]);

            // Parse expiry date
            $expiryDate = null;
            if (!empty($row['expiry_date'])) {
                try {
                    $expiryDate = \Carbon\Carbon::parse($row['expiry_date'])->format('Y-m-d');
                    logger()->info('InventoryImport: Expiry date parsed', ['expiryDate' => $expiryDate]);
                } catch (\Exception $e) {
                    logger()->error('InventoryImport: Invalid expiry date format', [
                        'itemName' => $itemName,
                        'expiryDate' => $row['expiry_date'],
                        'error' => $e->getMessage()
                    ]);
                    $this->errors[] = "Invalid expiry date format for item: {$itemName}";
                    $this->skippedCount++;
                    return null;
                }
            }

            // First, ensure we have a unique Inventory record for this product
            logger()->info('InventoryImport: Getting/creating inventory record', [
                'productId' => $product->id
            ]);
            
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'product_id' => $product->id,
                    'quantity' => 0, // Will be calculated from items
                ]
            );
            
            logger()->info('InventoryImport: Inventory record processed', [
                'inventoryId' => $inventory->id,
                'productId' => $inventory->product_id,
                'wasCreated' => $inventory->wasRecentlyCreated
            ]);

            // Check if inventory item already exists with same batch number and product
            logger()->info('InventoryImport: Checking for existing item', [
                'productId' => $product->id,
                'batchNo' => $batchNo,
                'warehouseId' => $warehouseId
            ]);
            
            $existingItem = InventoryItem::where('product_id', $product->id)
                ->where('batch_number', $batchNo)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($existingItem) {
                logger()->info('InventoryImport: Updating existing item', [
                    'existingItemId' => $existingItem->id,
                    'oldQuantity' => $existingItem->quantity,
                    'newQuantity' => $existingItem->quantity + $quantity
                ]);
                
                // Update existing item quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $quantity,
                ]);
                
                $this->importedCount++;
                Cache::increment($this->importId);
                // event(new InventoryUpdated($this->importId, Cache::get($this->importId)));
                return null; // Return null since we're updating, not creating new model
            }

            // Create new inventory item
            logger()->info('InventoryImport: Creating new inventory item', [
                'productId' => $product->id,
                'inventoryId' => $inventory->id,
                'warehouseId' => $warehouseId,
                'quantity' => $quantity,
                'batchNumber' => $batchNo,
                'expiryDate' => $expiryDate,
                'location' => $locationName,
                'uom' => $row['uom'] ?? 'PCS'
            ]);
            
            $inventoryItem = new InventoryItem([
                'inventory_id' => $inventory->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'batch_number' => $batchNo,
                'expiry_date' => $expiryDate,
                'location' => $locationName,
                'uom' => $row['uom'] ?? 'PCS',
                'notes' => null,
            ]);

            logger()->info('InventoryImport: Inventory item created', [
                'inventoryItemId' => $inventoryItem->id ?? 'not saved yet',
                'inventoryId' => $inventoryItem->inventory_id,
                'data' => $inventoryItem->toArray()
            ]);

            $this->importedCount++;
            Cache::increment($this->importId);
            // event(new InventoryUpdated($this->importId, Cache::get($this->importId)));

            return $inventoryItem;

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            logger()->error('InventoryImport error', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
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
