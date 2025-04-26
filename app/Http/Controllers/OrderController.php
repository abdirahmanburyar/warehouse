<?php

namespace App\Http\Controllers;

// App Models
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Facility;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;

// App Events and Resources
use App\Events\OrderEvent;
use App\Http\Resources\OrderResource;

// Laravel Core
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

// App Facades
use App\Facades\Kafka;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['facility', 'user'])
            ->when($request->from && $request->to, function ($query) use ($request) {
                $query->whereBetween('order_date', [$request->from, $request->to]);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('facility', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest();

        // $orders = $query->paginate($request->input('perPage', ), ['*'], 'page', $request->input('page', 1))
        //     ->withQueryString();

        // $orders->setPath(url()->current());

        // Get order items statistics
        $stats = DB::table('order_items')
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->status => $item->count];
        })
        ->toArray();

        // Ensure all statuses have a value
        $defaultStats = [
            'pending' => 0,
            'approved' => 0,
            'in_process' => 0,
            'dispatched' => 0,
            'delivered' => 0
        ];

        $stats = array_merge($defaultStats, $stats);

        return Inertia::render('Order/Index', [
            'stats' => $stats,
            'orders' => OrderResource::collection($query->get()),
            'facilities' => Facility::select('id', 'name')->get(),
            'filters' => $request->only('search', 'status', 'page', 'from', 'to'),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'facility_id' => 'required|exists:facilities,id',
                'order_date' => 'required|date',
                'expected_date' => 'nullable|date|after_or_equal:order_date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:order_items,id',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:1',
            ]);

            DB::beginTransaction();

            // Create or update order
            $order = Order::updateOrCreate(
                ['id' => $request->id],
                [
                    'warehouse_id' => $validated['warehouse_id'],
                    'facility_id' => $validated['facility_id'],
                    'user_id' => auth()->id(),
                    'order_number' => $request->id ? Order::find($request->id)->order_number : 'ORD-' . strtoupper(uniqid()),
                    'status' => $request->id ? Order::find($request->id)->status : 'pending',
                    'number_items' => collect($validated['items'])->sum('quantity'),
                    'notes' => $validated['notes'] ?? null,
                    'order_date' => $validated['order_date'],
                    'expected_date' => $validated['expected_date'] ?? null,
                ]
            );

            // Get current item IDs
            $currentItemIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete items that are not in the request (only pending orders)
            if ($request->id) {
                $order->items()
                    ->whereNotIn('id', $currentItemIds)
                    ->where('order_id', $order->id)
                    ->delete();
            }

            // Process each item
            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['id'])) {
                    // Update existing item
                    $order->items()
                        ->where('id', $itemData['id'])
                        ->update([
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                        ]);
                } else {
                    // Create new item
                    $order->items()->create([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity']
                    ]);
                }
            }

            Kafka::publishOrderPlaced('Refreshed');

            event(new OrderEvent('Refreshed'));

            DB::commit();
            return response()->json('Order ' . ($request->id ? 'updated' : 'created') . ' successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(Order $order)
    {
        try {
            if ($order->status !== 'pending') {
                return back()->with('error', 'Only pending orders can be deleted.');
            }
    
            $order->items()->delete();
            $order->delete();
    
            return back()->with('success', 'Order deleted successfully.');
        } catch (\Throwable $th) {
            return back()->with($th->getMessage(), 500);
        }
    }

    public function bulk(Request $request)
    {
        try {
            $orderIds = $request->input('orderIds');

            // Validate that at least one order is selected
            if (empty($orderIds)) {
                return response()->json('Please select at least one order', 400);
            }

            // Get all selected orders
            $orders = Order::whereIn('id', $orderIds)->get();

            // Check if any order has non-pending items and collect their IDs
            $nonPendingOrders = [];
            foreach ($orders as $order) {
                if ($order->status !== 'pending') {
                    $nonPendingOrders[] = $order->id;
                }
            }

            if (!empty($nonPendingOrders)) {
                return response()->json('Cannot delete orders that are not in pending status', 500);
            }

            // Delete orders if all are pending
            $orders->each(function ($order) {
                $order->items()->delete();
                $order->delete();
            });

            return response()->json('Selected orders deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function searchProduct(Request $request)
    {
        try {
            $search = $request->input('search');
            $products = Product::where('name', 'like', '%' . $search . '%')
                ->orWhere('barcode', 'like', '%' . $search . '%')
                ->select('id', 'name', 'barcode')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                    ];
                });

            return response()->json($products, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validate request
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'status' => ['required', Rule::in(['approved', 'in process', 'dispatched', 'delivered'])]
            ]);

            $order = Order::findOrFail($request->order_id);

            // Define allowed transitions
            $allowedTransitions = [
                'pending' => ['approved'],
                'approved' => ['in process'],
                'in process' => ['dispatched'],
                'dispatched' => ['delivered']
            ];

            // Check if the transition is allowed
            if (
                !isset($allowedTransitions[$order->status]) ||
                !in_array($request->status, $allowedTransitions[$order->status])
            ) {
                return response()->json("Status transition not allowed", 422);
            }

            $userId = auth()->id();
            $now = now();

            // Prepare updates for order
            $updates = ['status' => $request->status];

            // Add timestamp based on the status
            switch ($request->status) {
                case 'approved':
                    $updates['approved_at'] = $now;
                    $updates['approved_by'] = $userId;
                    break;
                case 'in process':
                    $updates['in_process'] = true;
                    break;
                case 'dispatched':
                    $updates['dispatched_by'] = $userId;
                    $updates['dispatched_at'] = $now;
                    break;
                case 'delivered':
                    $updates['delivered'] = true;
                    break;
            }

            // Update the order
            // $order->update($updates);

            // Trigger Kafka event for order status change
            Kafka::publishOrderPlaced("Refreshed");
            // Broadcast event
            event(new OrderEvent('Refreshed'));

            DB::commit();
            return response()->json('Order status updated successfully.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status change failed', [
                'error' => $e->getMessage(),
                'order_id' => $request->order_id ?? null
            ]);
            return response()->json('Failed to update order status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get items for an order with their inventory quantities
     */
    public function items(Order $order)
    {
        try {
            $items = $order->items()
                ->with('product','warehouse')
                ->get()
                ->map(function ($item) {
                    // Sum all inventory quantities for this product
                    $inventoryQty = \App\Models\Inventory::where('product_id', $item->product_id)->sum('quantity');
                    $item->inventory_quantity = $inventoryQty;
                    return $item;
                });

            return response()->json($items);
        } catch (\Exception $e) {
            Log::error('Failed to get order items', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return response()->json('Failed to load order items', 500);
        }
    }

    public function getOutstanding(Request $request, $id)
    {
        try {
            $outstanding = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('facilities', 'facilities.id', '=', 'orders.facility_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('order_items.id', $id)
                ->whereNotIn('order_items.status', ['pending', 'delivered'])
                ->select(
                    'products.name as product_name',
                    'facilities.name as facility_name',
                    'order_items.quantity',
                    'order_items.status'
                )
                ->get()
                ->map(function ($item) {
                    return [
                        'product' => $item->product_name,
                        'facility' => $item->facility_name,
                        'quantity' => $item->quantity,
                        'status' => $item->status
                    ];
                });

            return response()->json($outstanding, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function updateItem(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'id' => 'required|exists:order_items,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $orderItem = OrderItem::findOrFail($validated['id']);

            // Check if the new quantity will not exceed the current inventory quantity
            $currentInventoryQuantity = \App\Models\Inventory::where('product_id', $orderItem->product_id)->sum('quantity');
            if ($validated['quantity'] > $currentInventoryQuantity) {
                return response()->json('The new quantity exceeds the current inventory quantity.', 500);
            }

            $orderItem->update([
                'quantity' => $validated['quantity'],
            ]);
            Kafka::publishOrderPlaced("Refreshed");
            event(new OrderEvent('Refreshed'));

            DB::commit();
            return response()->json('Order item updated successfully.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Bulk change status of orders
     */
    public function bulkChangeStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'required|exists:orders,id',
            'status' => ['required', Rule::in(['approved', 'in process', 'dispatched', 'delivered'])]
        ]);

        $allowedTransitions = [
            'pending' => ['approved'],
            'approved' => ['in process'],
            'in process' => ['dispatched'],
            'dispatched' => ['delivered']
        ];

        DB::beginTransaction();
        try {
            $orders = Order::whereIn('id', $request->order_ids)->get();
            $updatedCount = 0;

            foreach ($orders as $order) {
                if (
                    isset($allowedTransitions[$order->status]) &&
                    in_array($request->status, $allowedTransitions[$order->status])
                ) {

                    $oldStatus = $order->status;
                    $order->status = $request->status;
                    $order->save();


                    $updatedCount++;
                }
            }

            DB::commit();

            if ($updatedCount === 0) {
                return response()->json("No orders were eligible for status change", 500);
            }

            Kafka::publishOrderPlaced('Refreshed');
            event(new OrderEvent('Order status updated'));
            return response()->json("Successfully updated {$updatedCount} orders to {$request->status}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk order status change failed', [
                'error' => $e->getMessage(),
                'order_ids' => $request->order_ids
            ]);
            return response()->json('Failed to update order statuses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk change status of order items
     */
    public function bulkChangeItemStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'item_ids' => 'required|array',
                'item_ids.*' => 'required|exists:order_items,id',
                'status' => ['required', Rule::in(['approved', 'in process', 'dispatched', 'delivered'])],
                'warehouse_id' => 'nullable|exists:warehouses,id'
            ]);

            $items = OrderItem::with('order')->whereIn('id', $request->item_ids)->get();
            $updatedCount = 0;
            $updatedOrders = [];

            $allowedTransitions = [
                'pending' => ['approved'],
                'approved' => ['in process'],
                'in process' => ['dispatched'],
                'dispatched' => ['delivered']
            ];


            foreach ($items as $item) {
                // Check if transition is allowed
                if (
                    !isset($allowedTransitions[$item->status]) ||
                    !in_array($request->status, $allowedTransitions[$item->status])
                ) {
                    continue;
                }

                $oldStatus = $item->status;
                $item->status = $request->status;
                if ($request->status == 'approved') {
                    $item->approved_at = Carbon::now()->toDateString();
                    $item->approved_by = auth()->id();
                    $item->save();
                }
                if ($request->status == 'dispatched') {
                    $item->dispatched_at = Carbon::now()->toDateString();
                    $item->dispatched_by = auth()->id();
                    $item->warehouse_id = $request->warehouse_id;
                    $item->save();
                }
                if ($request->status == 'in process') {
                    $item->in_process = 1;
                    $item->save();
                }
                if ($request->status == 'delivered') {
                    $item->delivered = 1;
                    
                    $remainingQuantity = (float) $item->quantity - (float) $item->quantity_on_order;
                    $usedInventories = [];

                    // Get all available inventory for this product from the warehouse, ordered by expiry date (FIFO)
                    $warehouseInventories = Inventory::where('product_id', $item->product_id)
                        ->where('quantity', '>', 0)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->orderBy('expiry_date', 'asc')  // Order by expiry date for FIFO (oldest first)
                        ->get();
                        
                    if ($warehouseInventories->sum('quantity') < $remainingQuantity) {
                        return response()->json("Not enough items in the inventory", 500);
                    }

                    foreach ($warehouseInventories as $warehouseInventory) {
                        if ($remainingQuantity <= 0) break;

                        // Calculate how much we can take from this batch
                        $quantityToTake = min($remainingQuantity, $warehouseInventory->quantity);
                        
                        // Update or create facility inventory for this batch
                        $facilityInventory = $item->order->facility->inventories()
                            ->where('product_id', $item->product_id)
                            ->where('batch_number', $warehouseInventory->batch_number)
                            ->first();

                        if ($facilityInventory) {
                            $facilityInventory->increment('quantity', $quantityToTake);
                            // Remove facility inventory if quantity becomes 0
                            if ($facilityInventory->fresh()->quantity <= 0) {
                                $facilityInventory->delete();
                            }
                        } else {
                            $item->order->facility->inventories()->create([
                                'product_id' => $item->product_id,
                                'batch_number' => $warehouseInventory->batch_number,
                                'expiry_date' => $warehouseInventory->expiry_date,
                                'quantity' => $quantityToTake,
                                'updated_at' => now()
                            ]);
                        }

                        // Update warehouse inventory
                        $warehouseInventory->decrement('quantity', $quantityToTake);
                        
                        // Remove warehouse inventory record if quantity is 0
                        if ($warehouseInventory->fresh()->quantity <= 0) {
                            $warehouseInventory->delete();
                        }

                        // Track used inventory for logging
                        $usedInventories[] = [
                            'batch_number' => $warehouseInventory->batch_number,
                            'expiry_date' => $warehouseInventory->expiry_date->format('Y-m-d'),
                            'quantity' => $quantityToTake
                        ];

                        $remainingQuantity -= $quantityToTake;
                    }

                    // Check if all items in this order have the same status
                    $pendingItems = $item->order->items()
                        ->where('status', '!=', 'delivered')
                        ->count();

                    if ($pendingItems === 0) {
                        $item->order->status = 'completed';
                        $item->order->save();
                    }

                    $item->delivered = 1;
                    $item->status = 'delivered';
                    $item->save();
                }               

                // Publish to Kafka
                try {
                    Kafka::publishOrderPlaced('Refreshed');
                } catch (\Exception $e) {
                    Log::error('Failed to publish order item status change to Kafka', [
                        'order_id' => $item->order_id,
                        'item_id' => $item->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Broadcast event
                event(new OrderEvent('Refreshed'));

                // Track unique orders that were affected
                if (!in_array($item->order_id, $updatedOrders)) {
                    $updatedOrders[] = $item->order_id;

                    // Check if all items in this order have the same status
                    // $allItemsSameStatus = $item->order->items()
                    //     ->where('status', '!=', $request->status)
                    //     ->doesntExist();

                    // if ($allItemsSameStatus) {
                    //     $item->order->status = "completed";
                    //     $item->order->save();
                    // }

                }

                $updatedCount++;
            }

            DB::commit();

            if ($updatedCount === 0) {
                return response()->json("No items were eligible for status change. Please check if the status transitions are allowed.", 500);
            }

            Kafka::publishOrderPlaced('Refreshed');

            event(new OrderEvent('Order items status updated'));
            return response()->json("Successfully updated {$updatedCount} items to {$request->status}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk order item status change failed', [
                'error' => $e->getMessage(),
                'item_ids' => $request->item_ids
            ]);
            return response()->json('Failed to update item statuses: ' . $e->getMessage(), 500);
        }
    }

    public function changeItemStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'item_id' => 'required|exists:order_items,id',
                'status' => ['required', Rule::in(['approved', 'in process', 'dispatched', 'delivered'])],
                'warehouse_id' => 'nullable|exists:warehouses,id'
            ]);

            $item = OrderItem::with('order', 'product')->findOrFail($request->item_id);

            // Define allowed transitions
            $allowedTransitions = [
                'pending' => ['approved'],
                'approved' => ['in process'],
                'in process' => ['dispatched'],
                'dispatched' => ['delivered']
            ];

            // Check if the transition is allowed
            if (
                !isset($allowedTransitions[$item->status]) ||
                !in_array($request->status, $allowedTransitions[$item->status])
            ) {
                return response()->json("Status transition not allowed", 500);
            }

            // Update item status
            $item->status = $request->status;
            if ($request->status == 'in process') {
                $item->in_process = 1;
                $item->save();
            }
            if ($request->status == 'approved') {
                $item->approved_at = Carbon::now()->toDateString();
                $item->approved_by = auth()->id();
                $item->save();
            }
            if ($request->status == 'dispatched') {
                $item->dispatched_at = Carbon::now()->toDateString();
                $item->dispatched_by = auth()->id();
                $item->warehouse_id = $request->warehouse_id;
                $item->save();
            }
            if ($request->status == 'delivered') {
                $item->delivered = 1;

                $remainingQuantity = (float) $item->quantity - (float) $item->quantity_on_order;
                $usedInventories = [];

                // Get all available inventory for this product from the warehouse, ordered by expiry date (FIFO)
                $warehouseInventories = Inventory::where('product_id', $item->product_id)
                    ->where('quantity', '>', 0)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->orderBy('expiry_date', 'asc')  // Order by expiry date for FIFO (oldest first)
                    ->get();

                if ($warehouseInventories->sum('quantity') < $remainingQuantity) {
                    return response()->json("Not enough items in the inventory", 500);
                }

                foreach ($warehouseInventories as $warehouseInventory) {
                    if ($remainingQuantity <= 0) break;

                    // Calculate how much we can take from this batch
                    $quantityToTake = min($remainingQuantity, $warehouseInventory->quantity);
                    
                    // Update or create facility inventory for this batch
                    $facilityInventory = $item->order->facility->inventories()
                        ->where('product_id', $item->product_id)
                        ->where('batch_number', $warehouseInventory->batch_number)
                        ->first();

                    if ($facilityInventory) {
                        $facilityInventory->increment('quantity', $quantityToTake);
                        // Remove facility inventory if quantity becomes 0
                        if ($facilityInventory->fresh()->quantity <= 0) {
                            $facilityInventory->delete();
                        }
                    } else {
                        
                        $item->order->facility->inventories()->create([
                            'product_id' => $item->product_id,
                            'batch_number' => $warehouseInventory->batch_number,
                            'expiry_date' => $warehouseInventory->expiry_date,
                            'quantity' => $quantityToTake,
                            'updated_at' => now()
                        ]);
                    }
                    // here we gonna update the inventories table
                    $warehouseInventory->decrement('quantity', $quantityToTake);
                    
                    // Remove inventory record if quantity is 0
                    if ($warehouseInventory->fresh()->quantity <= 0) {
                        $warehouseInventory->delete();
                    }

                    // Track used inventory for logging
                    $usedInventories[] = [
                        'batch_number' => $warehouseInventory->batch_number,
                        'expiry_date' => $warehouseInventory->expiry_date->format('Y-m-d'),
                        'quantity' => $quantityToTake
                    ];

                    $remainingQuantity -= $quantityToTake;
                }

                // Log the inventory usage with clear FIFO information
                Log::info('Order item fulfilled using FIFO (oldest expiry first)', [
                    'order_item_id' => $item->id,
                    'total_quantity' => $item->quantity,
                    'used_inventories' => $usedInventories
                ]);

                // Check if all items in this order have the same status
                $pendingItems = $item->order->items()
                    ->where('status', '!=', 'delivered')
                    ->count();

                if ($pendingItems === 0) {
                    $item->order->status = 'completed';
                    $item->order->save();
                }

                $item->delivered = 1;
                $item->status = 'delivered';
                $item->save();

            }  

            // Publish to Kafka
            try {
                Kafka::publishOrderPlaced('Refreshed');
            } catch (\Exception $e) {
                Log::error('Failed to publish order item status change to Kafka', [
                    'order_id' => $item->order_id,
                    'item_id' => $item->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Broadcast event
            event(new OrderEvent('Refreshed'));

            DB::commit();
            return response()->json('Order item status updated successfully.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order item status change failed', [
                'error' => $e->getMessage(),
                'item_id' => $request->item_id ?? null
            ]);
            return response()->json('Failed to update order item status: ' . $e->getMessage(), 500);
        }
    }

    public function show(Request $request, Order $order){
        try {
            DB::beginTransaction();
            $order->load('items.product', 'facility', 'user', 'items.warehouse');
            $warehouses = Warehouse::select('id', 'name')->get();
            DB::commit();
            return inertia("Order/Show", ['order' => $order, 'warehouses' => $warehouses]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return inertia("Order/Show", ['error' => $th->getMessage()]);
        }
    }
}
