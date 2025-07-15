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
use App\Events\InventoryUpdated;

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
            // Skip if required fields are empty
            if (empty($row['item']) || empty($row['quantity']) || empty($row['batch_no'])) {
                $this->skippedCount++;
                return null;
            }

            $itemName = trim($row['item']);
            $quantity = (float) $row['quantity'];
            $batchNo = trim($row['batch_no']);

            // Validate quantity
            if ($quantity <= 0) {
                $this->errors[] = "Invalid quantity for item: {$itemName}";
                $this->skippedCount++;
                return null;
            }

            // Find or create product
            $product = $this->getOrCreateProduct($itemName, $row['category'] ?? null);
            if (!$product) {
                $this->errors[] = "Could not create/find product: {$itemName}";
                $this->skippedCount++;
                return null;
            }

            // Use default warehouse_id = 1 and location
            $warehouseId = 1; // Default warehouse
            $locationName = $row['location'] ?? 'Default Location';

            // Parse expiry date
            $expiryDate = null;
            if (!empty($row['expiry_date'])) {
                try {
                    $expiryDate = \Carbon\Carbon::parse($row['expiry_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $this->errors[] = "Invalid expiry date format for item: {$itemName}";
                    $this->skippedCount++;
                    return null;
                }
            }

            // Check if inventory item already exists with same batch number and product
            $existingItem = InventoryItem::where('product_id', $product->id)
                ->where('batch_number', $batchNo)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($existingItem) {
                // Update existing item quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $quantity,
                ]);
                
                $this->importedCount++;
                Cache::increment($this->importId);
                event(new InventoryUpdated($this->importId, Cache::get($this->importId)));
                return null; // Return null since we're updating, not creating new model
            }

            // Create new inventory item
            $inventoryItem = new InventoryItem([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'batch_number' => $batchNo,
                'expiry_date' => $expiryDate,
                'location' => $locationName,
                'uom' => $row['uom'] ?? 'PCS',
                'notes' => null,
            ]);

            $this->importedCount++;
            Cache::increment($this->importId);
            event(new InventoryUpdated($this->importId, Cache::get($this->importId)));

            return $inventoryItem;

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            Log::error('InventoryImport error', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function getOrCreateProduct($itemName, $categoryName = null)
    {
        // Check cache first
        if (isset($this->productCache[$itemName])) {
            return $this->productCache[$itemName];
        }

        // Try to find existing product
        $product = Product::where('name', $itemName)->first();
        
        if (!$product) {
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
        }

        $this->productCache[$itemName] = $product;
        return $product;
    }

    protected function getOrCreateCategory($categoryName)
    {
        if (isset($this->categoryCache[$categoryName])) {
            return $this->categoryCache[$categoryName];
        }

        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['is_active' => true]
        );

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
                Cache::forget($this->importId);
                Log::info('Inventory import completed', [
                    'importId' => $this->importId,
                    'imported' => $this->importedCount,
                    'skipped' => $this->skippedCount,
                    'errors' => count($this->errors)
                ]);
                event(new InventoryUpdated($this->importId, 'completed'));
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
