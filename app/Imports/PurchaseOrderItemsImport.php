<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\Category;
use App\Models\Dosage;
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
    WithEvents,
    ShouldQueue
{
    use Queueable, InteractsWithQueue;

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
            // Debug: Log the row data to see what columns are present
            Log::info('Processing row in PurchaseOrderItemsImport', [
                'row_keys' => array_keys($row),
                'row_data' => $row
            ]);

            // Check for any ID-related columns in the Excel that we should ignore
            $idColumns = ['id', 'ID', 'Id', 'item_id', 'product_id', 'po_item_id'];
            foreach ($idColumns as $idColumn) {
                if (isset($row[$idColumn])) {
                    Log::warning('Found ID column in Excel, ignoring it', [
                        'column' => $idColumn,
                        'value' => $row[$idColumn]
                    ]);
                    unset($row[$idColumn]);
                }
            }

            if (empty($row['item_description']) || empty($row['quantity']) || empty($row['unit_cost']) || empty($row['uom'])) {
                $this->skippedCount++;
                return null;
            }

            $itemName = trim($row['item_description']);
            $uom = trim($row['uom']);
            $quantity = floatval($row['quantity']);
            $unit_cost = floatval($row['unit_cost']);
            
            // Calculate total cost
            $total_cost = $quantity * $unit_cost;

            // Category
            $categoryId = null;
            if (!empty($row['category'])) {
                $category = trim($row['category']);
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
                if (!isset($this->dosageCache[$dosageForm])) {
                    $dosageModel = Dosage::firstOrCreate(['name' => $dosageForm]);
                    $this->dosageCache[$dosageForm] = $dosageModel->id;
                }
                $dosageId = $this->dosageCache[$dosageForm];
            }

            // Get or create product
            $product = Product::updateOrCreate([
                'name' => $itemName,
            ], [
                'name' => $itemName,
                'category_id' => $categoryId,
                'dosage_id' => $dosageId,
                'is_active' => true,
            ]);

            $this->importedCount++;

            // Update progress in cache
            Cache::increment($this->importId);

            // Create PurchaseOrderItem (explicitly exclude ID to let database auto-increment)
            $poiData = [
                'purchase_order_id' => $this->purchaseOrderId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_cost' => $unit_cost,
                'total_cost' => $total_cost,
                'uom' => $uom
            ];

            // Ensure we're not accidentally including an ID field
            unset($poiData['id']);

            // Check if this product already exists in this purchase order
            $existingItem = PurchaseOrderItem::where('purchase_order_id', $this->purchaseOrderId)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                Log::warning('Duplicate product found in purchase order, skipping', [
                    'purchase_order_id' => $this->purchaseOrderId,
                    'product_id' => $product->id,
                    'product_name' => $itemName
                ]);
                $this->skippedCount++;
                return null;
            }

            // Use insert() instead of create() to avoid any ID issues
            $poiData['created_at'] = now();
            $poiData['updated_at'] = now();
            
            // Explicitly exclude ID from the data array
            $insertData = array_diff_key($poiData, ['id' => null]);
            
            Log::info('Inserting PurchaseOrderItem data', [
                'data_keys' => array_keys($insertData),
                'data' => $insertData
            ]);

            try {
                $id = DB::table('purchase_order_items')->insertGetId($insertData);
                Log::info('Successfully inserted PurchaseOrderItem', ['id' => $id]);
                return PurchaseOrderItem::find($id);
            } catch (\Exception $insertException) {
                Log::error('Failed to insert PurchaseOrderItem', [
                    'error' => $insertException->getMessage(),
                    'data' => $insertData
                ]);
                throw $insertException;
            }

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            throw $e;
        }
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

    public function getResults()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }
}
