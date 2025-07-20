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
            // Skip if required fields are empty
            if (empty($row['item_description']) || empty($row['quantity']) || empty($row['unit_cost']) || empty($row['uom'])) {
                $this->skippedCount++;
                return null;
            }

            // Find or create product with category and dosage
            $product = $this->getOrCreateProduct($row);
            
            // Calculate total cost
            $totalCost = $row['quantity'] * $row['unit_cost'];

            // Create PurchaseOrderItem record (without category_id and dosage_id)
            $poItem = PurchaseOrderItem::create([
                'purchase_order_id' => $this->purchaseOrderId,
                'product_id' => $product->id,
                'quantity' => $row['quantity'],
                'original_quantity' => $row['quantity'],
                'original_uom' => $row['uom'],
                'uom' => $row['uom'],
                'unit_cost' => $row['unit_cost'],
                'total_cost' => $totalCost,
            ]);

            $this->importedCount++;
            
            // Update progress
            Cache::put($this->importId, $this->importedCount, 3600);

            return $poItem;

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            Log::error('PurchaseOrderItems import error', [
                'row' => $row,
                'error' => $e->getMessage(),
                'importId' => $this->importId
            ]);
            return null;
        }
    }

    protected function getOrCreateProduct($row)
    {
        $productName = $row['item_description'];
        
        // Check cache first
        if (isset($this->productCache[$productName])) {
            return $this->productCache[$productName];
        }

        // Try to find existing product
        $product = Product::where('name', $productName)->first();
        
        if (!$product) {
            // Find or create category
            $category = null;
            if (!empty($row['category'])) {
                $category = $this->getOrCreateCategory($row['category']);
            }

            // Find or create dosage form
            $dosage = null;
            if (!empty($row['dosage_form'])) {
                $dosage = $this->getOrCreateDosage($row['dosage_form']);
            }

            // Create new product with category and dosage
            $product = Product::create([
                'name' => $productName,
                'barcode' => $row['item_code'] ?? $productName,
                'description' => $row['description'] ?? $productName,
                'category_id' => $category?->id,
                'dosage_id' => $dosage?->id,
                'is_active' => true,
            ]);
        } else {
            // If product exists, update category and dosage if provided and not already set
            $updated = false;
            
            if (!empty($row['category']) && !$product->category_id) {
                $category = $this->getOrCreateCategory($row['category']);
                $product->category_id = $category->id;
                $updated = true;
            }
            
            if (!empty($row['dosage_form']) && !$product->dosage_id) {
                $dosage = $this->getOrCreateDosage($row['dosage_form']);
                $product->dosage_id = $dosage->id;
                $updated = true;
            }
            
            if ($updated) {
                $product->save();
            }
        }

        // Cache the product
        $this->productCache[$productName] = $product;
        
        return $product;
    }

    protected function getOrCreateCategory($categoryName)
    {
        // Check cache first
        if (isset($this->categoryCache[$categoryName])) {
            return $this->categoryCache[$categoryName];
        }

        // Try to find existing category
        $category = Category::where('name', $categoryName)->first();
        
        if (!$category) {
            // Create new category
            $category = Category::create([
                'name' => $categoryName,
                'description' => $categoryName,
                'is_active' => true,
            ]);
        }

        // Cache the category
        $this->categoryCache[$categoryName] = $category;
        
        return $category;
    }

    protected function getOrCreateDosage($dosageName)
    {
        // Check cache first
        if (isset($this->dosageCache[$dosageName])) {
            return $this->dosageCache[$dosageName];
        }

        // Try to find existing dosage
        $dosage = Dosage::where('name', $dosageName)->first();
        
        if (!$dosage) {
            // Create new dosage
            $dosage = Dosage::create([
                'name' => $dosageName,
                'description' => $dosageName,
                'is_active' => true,
            ]);
        }

        // Cache the dosage
        $this->dosageCache[$dosageName] = $dosage;
        
        return $dosage;
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
