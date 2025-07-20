<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class PurchaseOrderItemsImport implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    WithValidation,
    SkipsEmptyRows, 
    WithEvents
{
    protected $purchaseOrderId;
    protected $importId;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $productCache = [];
    protected $categoryCache = [];
    protected $dosageCache = [];

    public function __construct($purchaseOrderId, string $importId)
    {
        $this->purchaseOrderId = $purchaseOrderId;
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        try {
            if (empty($row['item_description']) || empty($row['quantity']) || empty($row['unit_cost']) || empty($row['uom'])) {
                $this->skippedCount++;
                return null;
            }

            $itemName = trim($row['item_description']);

            if (strlen($itemName) > 255) {
                $this->errors[] = "Item description too long: " . substr($itemName, 0, 50) . "...";
                $this->skippedCount++;
                return null;
            }

            // Find or create product
            $product = $this->getOrCreateProduct($row);
            
            // Calculate total cost
            $totalCost = $row['quantity'] * $row['unit_cost'];

            $this->importedCount++;

            // Update progress in cache
            Cache::increment($this->importId);

            // Create PurchaseOrderItem
            return PurchaseOrderItem::create([
                'purchase_order_id' => $this->purchaseOrderId,
                'product_id' => $product->id,
                'quantity' => $row['quantity'],
                'original_quantity' => $row['quantity'],
                'uom' => $row['uom'],
                'unit_cost' => $row['unit_cost'],
                'total_cost' => $totalCost,
            ]);

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            throw $e;
        }
    }

    protected function getOrCreateProduct($row)
    {
        $productName = trim($row['item_description']);
        
        // Check cache first
        if (isset($this->productCache[$productName])) {
            return $this->productCache[$productName];
        }

        // Category
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

        // Dosage
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

        // Find or create product
        $product = Product::updateOrCreate([
            'name' => $productName,
        ], [
            'name' => $productName,
            'category_id' => $categoryId,
            'dosage_id' => $dosageId,
            'is_active' => true,
        ]);

        // Cache the product
        $this->productCache[$productName] = $product;
        
        return $product;
    }



    public function rules(): array
    {
        return [
            'item_description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'uom' => 'required|string|max:50',
            'category' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
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
                        'totalAmount' => $totalAmount,
                        'importedCount' => $this->importedCount,
                        'skippedCount' => $this->skippedCount,
                        'errors' => $this->errors
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

    public function getResults()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }
}
