<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Validation\Rule;

class PurchaseOrderItemsImport implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    SkipsEmptyRows, 
    WithEvents,
    ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public $purchaseOrderId;
    public $importId;
    protected $productCache = [];
    protected $categoryCache = [];
    protected $dosageCache = [];

    public function __construct($purchaseOrderId, string $importId = null)
    {
        $this->purchaseOrderId = $purchaseOrderId;
        $this->importId = $importId ?? 'po_items_' . time() . '_' . uniqid();
    }

    public function model(array $row)
    {
        try {
            // Validate required fields
            if (empty($row['item_description']) || empty($row['quantity']) || empty($row['unit_cost']) || empty($row['uom'])) {
                Log::warning('Row skipped: Missing required fields', $row);
                return null;
            }

            $item_description = trim($row['item_description']);
            $uom = trim($row['uom']);
            $quantity = floatval($row['quantity']);
            $unit_cost = floatval($row['unit_cost']);
            
            // Calculate total cost
            $total_cost = $quantity * $unit_cost;

            // Get or create category (can be null)
            $category = $this->getOrCreateCategory($row['category'] ?? null);

            // Get or create dosage form (can be null)
            $dosageForm = $this->getOrCreateDosage($row['dosage_form'] ?? null);

            // Get or create product
            $product = $this->getOrCreateProduct($item_description, $category?->id, $dosageForm?->id);

            // Create PurchaseOrderItem
            $poi = PurchaseOrderItem::create([
                'purchase_order_id' => $this->purchaseOrderId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_cost' => $unit_cost,
                'total_cost' => $total_cost,
                'uom' => $uom
            ]);

            Log::info('PurchaseOrderItem created', [
                'po_item_id' => $poi->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'total_cost' => $total_cost
            ]);

            // Update progress
            $currentProgress = Cache::get($this->importId, 0);
            Cache::put($this->importId, $currentProgress + 1, 3600);

            return $poi; // Return the created model instead of null

        } catch (\Throwable $e) {
            Log::error('PurchaseOrderItemsImport error', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }



    protected function getOrCreateCategory($categoryName)
    {
        // If category name is null or empty, return null
        if (empty($categoryName)) {
            return null;
        }

        if (isset($this->categoryCache[$categoryName])) {
            return $this->categoryCache[$categoryName];
        }

        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['status' => 'active']
        );

        $this->categoryCache[$categoryName] = $category;
        return $category;
    }

    protected function getOrCreateDosage($dosageName)
    {
        // If dosage name is null or empty, return null
        if (empty($dosageName)) {
            return null;
        }

        if (isset($this->dosageCache[$dosageName])) {
            return $this->dosageCache[$dosageName];
        }

        $dosage = Dosage::firstOrCreate(
            ['name' => $dosageName],
            ['status' => 'active']
        );

        $this->dosageCache[$dosageName] = $dosage;
        return $dosage;
    }

    protected function getOrCreateProduct($itemName, $categoryId = null, $dosageId = null)
    {
        if (isset($this->productCache[$itemName])) {
            return $this->productCache[$itemName];
        }

        $product = Product::firstOrCreate(
            ['name' => $itemName],
            [
                'status' => 'active',
                'category_id' => $categoryId,
                'dosage_id' => $dosageId
            ]
        );

        $this->productCache[$itemName] = $product;
        return $product;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                try {
                    // Update purchase order total amount
                    $totalAmount = PurchaseOrderItem::where('purchase_order_id', $this->purchaseOrderId)
                        ->sum('total_cost');
                    
                    DB::table('purchase_orders')
                        ->where('id', $this->purchaseOrderId)
                        ->update(['total_amount' => $totalAmount]);

                    Log::info('PurchaseOrderItems import completed', [
                        'importId' => $this->importId,
                        'purchaseOrderId' => $this->purchaseOrderId,
                        'totalAmount' => $totalAmount
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update purchase order total amount', [
                        'error' => $e->getMessage(),
                        'purchaseOrderId' => $this->purchaseOrderId
                    ]);
                }
                
                Cache::forget($this->importId);
            },
        ];
    }
}
