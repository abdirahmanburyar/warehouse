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

        // Group items by item_code to handle duplicates
        $groupedItems = [];

        foreach ($rows as $index => $row) {
            // Skip empty rows
            if ($row->filter()->isEmpty()) {
                continue;
            }

            // Validate required fields
            if (!isset($row[0]) || !isset($row[1]) || !isset($row[2]) || 
                !isset($row[3]) || !isset($row[4])) {
                Log::error('Missing required fields in row:', [
                    'index' => $index,
                    'data' => $row->toArray()
                ]);
                continue;
            }

            $item_code = $row[0];
            $quantity = floatval($row[3]);
            $unit_cost = floatval($row[4]);
            $row_total = $quantity * $unit_cost;

            // If item already exists in grouped items, update quantities and costs
            if (isset($groupedItems[$item_code])) {
                $existing = $groupedItems[$item_code];
                $total_quantity = $existing['quantity'] + $quantity;
                $total_cost = $existing['total_cost'] + $row_total;

                $groupedItems[$item_code] = [
                    'item_code' => $item_code,
                    'item_description' => $row[1],
                    'uom' => $row[2],
                    'quantity' => $total_quantity,
                    'total_cost' => $total_cost,
                    'row_index' => $index
                ];
            } else {
                // First occurrence of this item
                $groupedItems[$item_code] = [
                    'item_code' => $item_code,
                    'item_description' => $row[1],
                    'uom' => $row[2],
                    'quantity' => $quantity,
                    'total_cost' => $row_total,
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
                ->where('item_description', $item['item_description'])
                ->orWhere('item_code', $item['item_code'])
                    ->first();

                if ($existingPoItem) {
                    // Update existing PO item
                    $new_quantity = $existingPoItem->quantity + $item['quantity'];
                    $new_total_cost = $existingPoItem->total_cost + $item['total_cost'];
                    $new_unit_cost = $new_total_cost / $new_quantity;

                    $existingPoItem->update([
                        'quantity' => $new_quantity,
                        'unit_cost' => $new_unit_cost,
                        'total_cost' => $new_total_cost
                    ]);
                } else {
                    // Calculate unit cost from total cost and quantity
                    $unit_cost = $item['total_cost'] / $item['quantity'];

                    // Create new PO Item
                    PoItem::create([
                        'purchase_order_id' => $this->purchaseOrderId,
                        'item_code' => $item['item_code'],
                        'item_description' => $item['item_description'],
                        'uom' => $item['uom'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $unit_cost,
                        'total_cost' => $item['total_cost']
                    ]);
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
