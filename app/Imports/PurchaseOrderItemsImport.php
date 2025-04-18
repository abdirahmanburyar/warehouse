<?php

namespace App\Imports;

use App\Models\PoItem;
use App\Models\Product;
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
            $quantity = floatval($row[3]);
            $unit_cost = floatval($row[4]); 
            $total_cost = floatval($row[5]); 
            
            // Validate all required fields
            if (empty($item_code) || empty($item_description) || $quantity <= 0 || $unit_cost <= 0) {
                continue;
            }

            // Create a unique key combining item code and unit cost
            $item_key = $item_code . '_' . number_format($unit_cost, 2);

            if (isset($groupedItems[$item_key])) {
                // Same item with same unit cost - just add quantities
                $existing = $groupedItems[$item_key];
                $total_quantity = $existing['quantity'] + $quantity;
                $total_cost = $unit_cost * $total_quantity;

                $groupedItems[$item_key] = [
                    'item_code' => $item_code,
                    'item_description' => $item_description,
                    'original_quantity' => $existing['original_quantity'] + $quantity,
                    'uom' => $uom,
                    'quantity' => $total_quantity,
                    'unit_cost' => $unit_cost,
                    'total_cost' => $total_cost,
                    'row_index' => $index
                ];
            } else {
                // New item or same item with different unit cost
                $groupedItems[$item_key] = [
                    'item_code' => $item_code,
                    'item_description' => $item_description,
                    'original_quantity' => $quantity,
                    'uom' => $uom,
                    'quantity' => $quantity,
                    'unit_cost' => $unit_cost,
                    'total_cost' => $quantity * $unit_cost,
                    'row_index' => $index
                ];
            }
        }

        DB::beginTransaction();
        try {
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
                        'uom' => $item['uom'],
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
                            'total_cost' => $new_total_cost
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
                            'total_cost' => $item['total_cost']
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
