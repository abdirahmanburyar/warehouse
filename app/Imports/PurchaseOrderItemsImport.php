<?php

namespace App\Imports;

use App\Models\PoItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PurchaseOrderItemsImport implements ToCollection
{
    protected $purchaseOrderId;

    public function __construct($purchaseOrderId)
    {
        $this->purchaseOrderId = $purchaseOrderId;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            // Skip header row
            $rows = $rows->slice(1);

            // Group items by item_code and unit_cost to handle duplicates
            $groupedItems = [];

            foreach ($rows as $index => $row) {
                // Skip empty rows
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                $item_code = trim($row[0]);
                $item_description = trim($row[1]);
                $uom = trim($row[2]);
                $dose = $row[3];
                $quantity = floatval($row[4]);
                $category = $row[5];
                $dosage_form = $row[6];
                $unit_cost = floatval($row[7]); 
                $total_cost = floatval($row[8]); 
                
                // Validate all required fields
                if (empty($item_code) || empty($item_description) || $quantity <= 0 || $unit_cost <= 0) {
                    continue;
                }

                // Find or create Category
                $categoryModel = Category::firstOrCreate(
                    ['name' => $category],
                    ['description' => $category, 'is_active' => true]
                );

                // Find or create Dosage
                $dosageModel = Dosage::firstOrCreate(
                    ['name' => $dosage_form],
                    ['description' => $dosage_form, 'is_active' => true]
                );

                // Create a unique key combining item code and unit cost
                $item_key = $item_code . '_' . number_format($unit_cost, 2);

                if (isset($groupedItems[$item_key])) {
                    // Same item with same unit cost - just add quantities
                    $groupedItems[$item_key]['quantity'] += $quantity;
                    $groupedItems[$item_key]['total_cost'] += $total_cost;
                } else {
                    // New item
                    $groupedItems[$item_key] = [
                        'purchase_order_id' => $this->purchaseOrderId,
                        'item_code' => $item_code,
                        'item_description' => $item_description,
                        'uom' => $uom,
                        'quantity' => $quantity,
                        'original_quantity' => $quantity,
                        'unit_cost' => $unit_cost,
                        'total_cost' => $total_cost,
                    ];
                }
            }

       
            foreach ($groupedItems as $item) {
                // Check if product exists by item_code (barcode) or description (name)
                $product = Product::where('barcode', $item['item_code'])
                    ->orWhere('name', $item['item_description'])
                    ->first();

                // If product doesn't exist, create it
                if (!$product) {
                    $product = Product::create([
                        'barcode' => $item['item_code'],
                        'name' => $item['item_description'],
                        'dose' => $item['dose'],
                        'dosage_id' => $dosage_form->id,
                        'category_id' => $category->id,
                        'dose' => $item['dose'],
                        'status' => 'active'
                    ]);
                }

                // Check if item already exists in this purchase order
                $existingPoItem = PoItem::where('purchase_order_id', $this->purchaseOrderId)
                    ->where(function($query) use ($item) {
                        $query->where('item_code', $item['item_code'])
                              ->orWhere('item_description', $item['item_description']);
                    })
                    ->first();

                if ($existingPoItem) {
                    // Update existing PO item
                    $new_quantity = $existingPoItem->quantity + $item['quantity'];
                    $new_total_cost = $existingPoItem->total_cost + $item['total_cost'];
                    // Calculate unit cost by dividing total cost by total quantity
                    $new_unit_cost = round($new_total_cost / $new_quantity, 2);

                    try {
                        $existingPoItem->update([
                            'quantity' => $new_quantity,
                            'original_quantity' => $new_quantity,
                            'unit_cost' => $new_unit_cost,
                            'total_cost' => $new_total_cost,
                        ]);

                    } catch (\Exception $e) {
                        Log::error('Failed to update PO item:', [
                            'item_code' => $item['item_code'],
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                } else {
                    try {
                        // Create new PO Item
                        $data = [
                            'purchase_order_id' => $this->purchaseOrderId,
                            'item_code' => $item['item_code'],
                            'item_description' => $item['item_description'],
                            'uom' => $item['uom'],
                            'quantity' => $item['quantity'],
                            'original_quantity' => $item['quantity'],
                            'unit_cost' => $item['unit_cost'],
                            'total_cost' => $item['total_cost'],
                        ];

                        $newItem = PoItem::create($data);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing items:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
