<?php

namespace App\Http\Controllers;

// App Models
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Facility;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\District;
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
    /**
     * Reject an entire order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectOrder(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);
            
            $order = Order::findOrFail($request->order_id);
            
            // Update order status to rejected
            $order->status = 'rejected';
            $order->rejected_by = auth()->id();
            $order->rejected_at = now();
            $order->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order has been rejected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject order: ' . $e->getMessage()
            ], 500);
        }
    }
    public function index(Request $request)
    {
        $facility = $request->facility;
        $facilityLocation = $request->facilityLocation;
        $query = Order::query()
            ->with(['facility', 'user'])
            ->when($request->dateFrom && $request->dateTo, function ($query) use ($request) {
                $query->whereBetween('order_date', [$request->dateFrom, $request->dateTo]);
            })
            ->when($request->currentStatus, function ($query, $search) {
                $query->where('status', $search);
            })
            ->when($request->facility, function ($query, $facility) {
                $query->where('facility_id', $facility);
            })
            ->when($request->orderType, function ($query, $orderType) {
                $query->where('order_type', $orderType);
            })
            ->when($request->facilityLocation, function ($query) use($facilityLocation) {
                $query->whereHas('facility', function($q) use ($facilityLocation) {
                    $q->where('district', $facilityLocation);
                });
            })            
            ->latest()
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1));

        // Get order statistics from orders table
        $stats = DB::table('orders')
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
            'rejected' => 0,
            'in_process' => 0,
            'dispatched' => 0,
            'received' => 0
        ];

        $stats = array_merge($defaultStats, $stats);

        $orders = $query->setPath(url()->current());

        $facilities = Facility::select('id','name')->get();
        $facilityLocations = District::select('id','name')->pluck('name')->toArray();
        
        return Inertia::render('Order/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only('search', 'page', 'currentStatus', 'facility', 'orderType', 'facilityLocation', 'dateFrom', 'dateTo','per_page'),
            'stats' => $stats,
            'facilities' => $facilities,
            'facilityLocations' => $facilityLocations
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

    // updateQuantity
    public function updateQuantity(Request $request){
        try {
            DB::beginTransaction();
            
            $request->validate([
                'item_id' => 'required|exists:order_items,id',
                'quantity' => 'required|numeric|min:1'
            ]);
            
            // Get the order item
            $orderItem = OrderItem::findOrFail($request->item_id);
            $oldQuantityToRelease = $orderItem->quantity_to_release ?? 0;
            $newQuantityToRelease = $request->quantity;
            
            // Check if the order is in a state where quantity can be updated
            $order = $orderItem->order;
            if (!in_array($order->status, ['pending'])) {
                return response()->json('Cannot update quantity for orders that are not in pending status', 500);
            }
            
            // Calculate the total current allocations
            $currentAllocatedQuantity = $orderItem->inventory_allocations()->sum('allocated_quantity');
            
            // If quantity_to_release is decreased
            if ($newQuantityToRelease < $oldQuantityToRelease) {
                // Calculate how many items to remove
                $quantityToRemove = $oldQuantityToRelease - $newQuantityToRelease;
                
                // Get the current allocations for this order item, ordered by expiry date descending (newest first)
                // We remove from the newest expiry dates first when decreasing quantity
                $allocations = $orderItem->inventory_allocations()
                    ->orderBy('expiry_date', 'desc')
                    ->get();
                
                $remainingToRemove = $quantityToRemove;
                
                foreach ($allocations as $allocation) {
                    if ($remainingToRemove <= 0) break;
                    
                    // Find matching inventory record to return quantity to
                    $inventory = Inventory::where('product_id', $allocation->product_id)
                        ->where('warehouse_id', $allocation->warehouse_id)
                        ->where('batch_number', $allocation->batch_number)
                        ->where('expiry_date', $allocation->expiry_date)
                        ->first();
                    
                    if ($inventory) {
                        // Return quantity to inventory
                        if ($allocation->allocated_quantity <= $remainingToRemove) {
                            // Return the entire allocation quantity to inventory
                            $inventory->quantity += $allocation->allocated_quantity;
                            $inventory->save();
                            
                            // Remove the entire allocation
                            $remainingToRemove -= $allocation->allocated_quantity;
                            $allocation->delete();
                        } else {
                            // Partially reduce this allocation and return that quantity to inventory
                            $inventory->quantity += $remainingToRemove;
                            $inventory->save();
                            
                            $allocation->allocated_quantity -= $remainingToRemove;
                            $allocation->save();
                            $remainingToRemove = 0;
                        }
                    } else {
                        // If inventory record doesn't exist, create a new one
                        if ($allocation->allocated_quantity <= $remainingToRemove) {
                            // Create new inventory with the full allocation quantity
                            Inventory::create([
                                'product_id' => $allocation->product_id,
                                'warehouse_id' => $allocation->warehouse_id,
                                'location_id' => $allocation->location_id,
                                'batch_number' => $allocation->batch_number,
                                'uom' => $allocation->uom,
                                'barcode' => $allocation->barcode,
                                'expiry_date' => $allocation->expiry_date,
                                'quantity' => $allocation->allocated_quantity
                            ]);
                            
                            // Remove the entire allocation
                            $remainingToRemove -= $allocation->allocated_quantity;
                            $allocation->delete();
                        } else {
                            // Create new inventory with partial quantity
                            Inventory::create([
                                'product_id' => $allocation->product_id,
                                'warehouse_id' => $allocation->warehouse_id,
                                'location_id' => $allocation->location_id,
                                'batch_number' => $allocation->batch_number,
                                'uom' => $allocation->uom,
                                'barcode' => $allocation->barcode,
                                'expiry_date' => $allocation->expiry_date,
                                'quantity' => $remainingToRemove
                            ]);
                            
                            // Partially reduce this allocation
                            $allocation->allocated_quantity -= $remainingToRemove;
                            $allocation->save();
                            $remainingToRemove = 0;
                        }
                    }
                }
                
                // Update the order item quantity_to_release
                $orderItem->quantity_to_release = $newQuantityToRelease;
                $orderItem->save();
                
                DB::commit();
                return response()->json([
                    'message' => 'Quantity to release updated successfully', 
                    'item' => $orderItem->load('inventory_allocations')
                ], 200);
            } 
            // If quantity_to_release is increased, we need to check inventory availability and add allocations
            else if ($newQuantityToRelease > $oldQuantityToRelease) {
                // Calculate how many additional items we need
                $quantityToAdd = $newQuantityToRelease - $oldQuantityToRelease;
                
                // Get the inventory items that match the product (without warehouse restriction)
                $inventoryItems = Inventory::where('product_id', $orderItem->product_id)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')  // FIFO - oldest expiry first
                    ->get();
                
                if ($inventoryItems->isEmpty()) {
                    DB::rollBack();
                    return response()->json('No inventory available for this product', 500);
                }
                
                // First check if we already have existing allocations we can use
                $existingAllocations = $orderItem->inventory_allocations;
                $remainingToAllocate = $quantityToAdd;
                
                if ($existingAllocations->isNotEmpty()) {
                    // Try to find a matching inventory record for the first allocation
                    $firstAllocation = $existingAllocations->first();
                    $matchingInventory = $inventoryItems->first(function($inventory) use ($firstAllocation) {
                        return $inventory->batch_number == $firstAllocation->batch_number && 
                               $inventory->expiry_date == $firstAllocation->expiry_date;
                    });
                    
                    if ($matchingInventory && $matchingInventory->quantity >= $quantityToAdd) {
                        // Update existing allocation and decrease inventory
                        $firstAllocation->allocated_quantity += $quantityToAdd;
                        $firstAllocation->save();
                        
                        // Decrease inventory quantity
                        $matchingInventory->quantity -= $quantityToAdd;
                        $matchingInventory->save();
                        
                        $remainingToAllocate = 0;
                    }
                }
                
                // If we still have quantity to allocate, use available inventory
                if ($remainingToAllocate > 0) {
                    foreach ($inventoryItems as $inventory) {
                        if ($remainingToAllocate <= 0) break;
                        
                        if ($inventory->quantity > 0) {
                            $quantityFromThisInventory = min($inventory->quantity, $remainingToAllocate);
                            
                            // Check if there's an existing allocation with this batch/expiry
                            $existingAllocation = $orderItem->inventory_allocations()
                                ->where('batch_number', $inventory->batch_number)
                                ->where('expiry_date', $inventory->expiry_date)
                                ->first();
                            
                            if ($existingAllocation) {
                                // Update existing allocation
                                $existingAllocation->allocated_quantity += $quantityFromThisInventory;
                                $existingAllocation->save();
                            } else {
                                // Create new allocation
                                $orderItem->inventory_allocations()->create([
                                    'product_id' => $inventory->product_id,
                                    'warehouse_id' => $inventory->warehouse_id,
                                    'location_id' => $inventory->location_id,
                                    'batch_number' => $inventory->batch_number,
                                    'uom' => $inventory->uom,
                                    'barcode' => $inventory->barcode ?? null,
                                    'expiry_date' => $inventory->expiry_date,
                                    'allocated_quantity' => $quantityFromThisInventory,
                                    'allocation_type' => $order->order_type, // Use the order's type
                                    'notes' => 'Allocated from inventory ID: ' . $inventory->id
                                ]);
                            }
                            
                            // Decrease inventory quantity
                            $inventory->quantity -= $quantityFromThisInventory;
                            $inventory->save();
                            
                            $remainingToAllocate -= $quantityFromThisInventory;
                        }
                    }
                }
                
                // Check if we were able to allocate all the requested quantity
                if ($remainingToAllocate > 0) {
                    DB::rollBack();
                    return response()->json('Insufficient inventory. Could only allocate ' . ($quantityToAdd - $remainingToAllocate) . ' out of ' . $quantityToAdd . ' requested items.', 500);
                }
                
                // Update the order item quantity_to_release
                $orderItem->quantity_to_release = $newQuantityToRelease;
                $orderItem->save();
                
                DB::commit();
                return response()->json([
                    'message' => 'Quantity to release updated successfully', 
                    'item' => $orderItem->load('inventory_allocations')
                ], 200);
            } 
            // If quantity_to_release is the same, just return success
            else {
                DB::commit();
                return response()->json([
                    'message' => 'No change in quantity to release', 
                    'item' => $orderItem->load('inventory_allocations')
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
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
                'status' => ['required', Rule::in(['approved', 'in_process', 'dispatched'])]
            ]);

            $order = Order::findOrFail($request->order_id);

            // Define allowed transitions
            $allowedTransitions = [
                'pending' => ['approved'],
                'approved' => ['in_process'],
                'in_process' => ['dispatched'],
                'rejected' => ['approved'] // Allow rejected orders to be approved
            ];

            // Check if the transition is allowed
            if (
                !isset($allowedTransitions[$order->status]) ||
                !in_array($request->status, $allowedTransitions[$order->status])
            ) {
                return response()->json("Status transition not allowed", 500);
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
                    
                    // If transitioning from rejected to approved, clear rejection data
                    if ($order->status === 'rejected') {
                        $updates['rejected_by'] = null;
                        $updates['rejected_at'] = null;
                    }
                    // issued quantity - history
                //    foreach($order->items as $item){
                //     // IssuedQuantity::create([
                //     //     'product_id' => $item['product_id'],
                //     //     'quantity' => $item['quantity'],
                //     //     'batch_number' => $item['batch_number'],
                //     //     'barcode' => $item['barcode'],
                //     //     'uom' => $item['uom'],
                //     //     'expiry_date' => $item['expiry_date'],
                //     //     'issued_by' => $userId,
                //     //     'issued_at' => $now,
                //     // ]);
                //    }
                    break;
                case 'in_process':
                    $updates['in_process'] = true;
                    $updates['in_process_at'] = $now;
                    $updates['in_process_by'] = $userId;
                    break;
                case 'dispatched':
                    $updates['dispatched_by'] = $userId;
                    $updates['dispatched_at'] = $now;
                    break;
            }

            // Update the order
            $order->update($updates);

            // Trigger Kafka event for order status change
            Kafka::publishOrderPlaced("Refreshed");
            // Broadcast event
            event(new OrderEvent('Refreshed'));

            DB::commit();
            return response()->json('Order status updated successfully.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json('Failed to update order status: ' . $e->getMessage(), 500);
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
                ->whereNotIn('order_items.status', ['pending'])
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
            'status' => ['required', Rule::in(['approved', 'in process', 'dispatched', 'delivery_pending', 'delivered'])]
        ]);

        $allowedTransitions = [
            'pending' => ['approved'],
            'approved' => ['in process'],
            'in process' => ['dispatched'],
            'dispatched' => ['delivery_pending', 'delivered'],
            'delivery_pending' => ['delivered']
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
                'status' => ['required', Rule::in(['approved', 'in process', 'dispatched', 'delivery_pending', 'delivered'])],
                'warehouse_id' => 'nullable|exists:warehouses,id'
            ]);

            $items = OrderItem::with('order')->whereIn('id', $request->item_ids)->get();
            $updatedCount = 0;
            $updatedOrders = [];

            $allowedTransitions = [
                'pending' => ['approved'],
                'approved' => ['in process'],
                'in process' => ['dispatched'],
                'dispatched' => ['delivery_pending', 'delivered'],
                'delivery_pending' => ['delivered']
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
                // Get all available inventory for this product from the warehouse, ordered by expiry date (FIFO)
                $warehouseInventories = Inventory::where('product_id', $item->product_id)
                    ->where('quantity', '>', 0)
                    // ->where('warehouse_id', $item->warehouse_id)
                    ->orderBy('expiry_date', 'asc')  // Order by expiry date for FIFO (oldest first)
                    ->get();

                $remainingQuantity = (float) $item->quantity - (float) $item->quantity_on_order;

                if ($warehouseInventories->sum('quantity') < $remainingQuantity) {
                    return response()->json("Not enough items in the inventory", 500);
                }
                if ($request->status == 'approved') {
                    $item->approved_at = Carbon::now()->toDateString();
                    $item->approved_by = auth()->id();
                    $item->save();
                }

                if ($request->status == 'in process') {
                    $item->in_process = 1;
                    $item->save();
                }

                if ($request->status == 'dispatched') {
                    $item->dispatched_at = Carbon::now()->toDateString();
                    $item->dispatched_by = auth()->id();
                    $item->warehouse_id = $request->warehouse_id;
                    $item->save();
                }
                if ($request->status == 'delivery_pending') {
                    $item->delivery_pending_at = Carbon::now()->toDateString();
                    $item->save();
                }
                if ($request->status == 'delivered') {
                    $item->delivered = 1;

                    $usedInventories = [];

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
                            // Handle facility inventory with quantity 0
                            if ($facilityInventory->fresh()->quantity <= 0) {
                                // Get all zero quantity records for this product
                                $zeroQuantityInventories = $item->order->facility
                                    ->inventories()
                                    ->where('product_id', $item->product_id)
                                    ->where('quantity', '=', 0)
                                    ->orderBy('created_at', 'asc')  // Get oldest first
                                    ->get();

                                if ($zeroQuantityInventories->count() > 1) {
                                    // Keep the first (oldest) record and reset its metadata
                                    $firstRecord = $zeroQuantityInventories->first();
                                    $firstRecord->update([
                                        'batch_number' => null,
                                        'expiry_date' => null,
                                        'location' => null,
                                        'warehouse_id' => null
                                    ]);

                                    // Delete all other zero quantity records
                                    $item->order->facility->inventories()
                                        ->where('product_id', $item->product_id)
                                        ->where('quantity', '=', 0)
                                        ->where('id', '!=', $firstRecord->id)
                                        ->delete();
                                } else {
                                    // If this is the only zero quantity record, just reset its metadata
                                    $facilityInventory->update([
                                        'batch_number' => null,
                                        'expiry_date' => null,
                                        'location' => null,
                                        'warehouse_id' => null
                                    ]);
                                }
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

                $zeroQuantityInventories = Inventory::where('product_id', $item->product_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->where('quantity', '=', 0)
                    ->get();

                if ($zeroQuantityInventories->count() > 1) {
                    // Keep the oldest record and reset its metadata
                    $oldestRecord = $zeroQuantityInventories->sortBy('created_at')->first();
                    $oldestRecord->update([
                        'batch_number' => null,
                        'expiry_date' => null,
                        'location' => null,
                        'warehouse_id' => null
                    ]);

                    // Delete all other zero quantity records except the oldest
                    Inventory::where('product_id', $item->product_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->where('quantity', '=', 0)
                        ->where('id', '!=', $oldestRecord->id)
                        ->delete();
                } elseif ($zeroQuantityInventories->count() == 1) {
                    // If only one record exists, just reset its metadata
                    $zeroQuantityInventories->first()->update([
                        'batch_number' => null,
                        'expiry_date' => null,
                        'location' => null,
                        'warehouse_id' => null
                    ]);
                }

                // Broadcast event
                Kafka::publishOrderPlaced('Refreshed');
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
                'status' => ['required', Rule::in(['approved', 'in process', 'dispatched', 'delivery_pending', 'delivered'])],
                'warehouse_id' => 'nullable|exists:warehouses,id'
            ]);

            $item = OrderItem::with('order', 'product')->findOrFail($request->item_id);

            // Define allowed transitions
            $allowedTransitions = [
                'pending' => ['approved'],
                'approved' => ['in process'],
                'in process' => ['dispatched'],
                'dispatched' => ['delivery_pending', 'delivered'],
                'delivery_pending' => ['delivered']
            ];

            // Check if the transition is allowed
            if (
                !isset($allowedTransitions[$item->status]) ||
                !in_array($request->status, $allowedTransitions[$item->status])
            ) {
                return response()->json("Status transition not allowed", 500);
            }

            $remainingQuantity = (float) $item->quantity - (float) $item->quantity_on_order;
            logger()->info($item->product_id);


            // Get all available inventory for this product from the warehouse, ordered by expiry date (FIFO)
            $warehouseInventories = Inventory::where('product_id', $item->product_id)
                ->where('quantity', '>', 0)
                // ->where('warehouse_id', $item->warehouse_id)
                ->orderBy('expiry_date', 'asc')  // Order by expiry date for FIFO (oldest first)
                ->get();

            logger()->info($warehouseInventories->sum('quantity'));
            if ($warehouseInventories->sum('quantity') < $remainingQuantity) {
                return response()->json("Not enough items in the inventory", 500);
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
            if ($request->status == 'delivery_pending') {
                $item->delivery_pending_at = Carbon::now()->toDateString();
                $item->save();
            }
            if ($request->status == 'delivered') {
                $item->delivered = 1;

                $usedInventories = [];

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
                        // Handle facility inventory with quantity 0
                        if ($facilityInventory->fresh()->quantity <= 0) {
                            // Get all zero quantity records for this product
                            $zeroQuantityInventories = $item->order->facility
                                ->inventories()
                                ->where('product_id', $item->product_id)
                                ->where('quantity', '=', 0)
                                ->orderBy('created_at', 'asc')  // Get oldest first
                                ->get();

                            if ($zeroQuantityInventories->count() > 1) {
                                // Keep the first (oldest) record and reset its metadata
                                $firstRecord = $zeroQuantityInventories->first();
                                $firstRecord->update([
                                    'batch_number' => null,
                                    'expiry_date' => null,
                                    'location' => null,
                                    'warehouse_id' => null
                                ]);

                                // Delete all other zero quantity records
                                $item->order->facility->inventories()
                                    ->where('product_id', $item->product_id)
                                    ->where('quantity', '=', 0)
                                    ->where('id', '!=', $firstRecord->id)
                                    ->delete();
                            } else {
                                // If this is the only zero quantity record, just reset its metadata
                                $facilityInventory->update([
                                    'batch_number' => null,
                                    'expiry_date' => null,
                                    'location' => null,
                                    'warehouse_id' => null
                                ]);
                            }
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


                    // Track used inventory for logging
                    $usedInventories[] = [
                        'batch_number' => $warehouseInventory->batch_number,
                        'expiry_date' => $warehouseInventory->expiry_date->format('Y-m-d'),
                        'quantity' => $quantityToTake
                    ];

                    $remainingQuantity -= $quantityToTake;
                }

                // Remove inventory record if quantity is 0
                $zeroQuantityInventories = Inventory::where('product_id', $item->product_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->where('quantity', '=', 0)
                    ->get();

                if ($zeroQuantityInventories->count() > 1) {
                    // Keep the oldest record and reset its metadata
                    $oldestRecord = $zeroQuantityInventories->sortBy('created_at')->first();
                    $oldestRecord->update([
                        'batch_number' => null,
                        'expiry_date' => null,
                        'location' => null,
                        'warehouse_id' => null
                    ]);

                    // Delete all other zero quantity records except the oldest
                    Inventory::where('product_id', $item->product_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->where('quantity', '=', 0)
                        ->where('id', '!=', $oldestRecord->id)
                        ->delete();
                } elseif ($zeroQuantityInventories->count() == 1) {
                    // If only one record exists, just reset its metadata
                    $zeroQuantityInventories->first()->update([
                        'batch_number' => null,
                        'expiry_date' => null,
                        'location' => null,
                        'warehouse_id' => null
                    ]);
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

            // Broadcast event
            Kafka::publishOrderPlaced('Refreshed');
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

    public function show(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $order = Order::where('orders.id', $id)
                ->with(['items.product', 'items.inventory_allocations.warehouse', 'items.inventory_allocations.location', 'facility', 'user'])
                ->first();

            // Get items with SOH using subquery
            $items = DB::table('order_items')
                ->select([
                    'order_items.*',
                    'products.name as product_name',
                    'inventory_sums.total_quantity as soh'
                ])
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin(DB::raw('(
                    SELECT product_id, SUM(quantity) as total_quantity
                    FROM inventories
                    GROUP BY product_id
                ) as inventory_sums'), 'products.id', '=', 'inventory_sums.product_id')
                ->where('order_items.order_id', $id)
                ->get();

            $order->items = $items;
            $products = Product::select('id','name')->get();
            
            DB::commit();
            return inertia("Order/Show", ['order' => $order, 'products' => $products]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return inertia("Order/Show", ['error' =>  $th->getMessage()]);
        }
    }

    public function pending(Request $request)
    {
        $query = Order::with(['facility', 'user'])
            ->where('status', 'pending');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('facility', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(10);

        return Inertia::render('Order/Pending', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only('search', 'page')
        ]);
    }

    public function approved(Request $request)
    {
        $orders = Order::with(['facility', 'user'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return Inertia::render('Order/Approved', [
            'orders' => $orders,
        ]);
    }

    public function inProcess(Request $request)
    {
        $orders = Order::with(['facility', 'user'])
            ->where('status', 'in_process')
            ->latest()
            ->get();

        return Inertia::render('Order/InProcess', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only('search', 'page')
        ]);
    }

    public function dispatched(Request $request)
    {
        $orders = Order::with(['facility', 'user'])
            ->where('status', 'dispatched')
            ->latest()
            ->get();

        return Inertia::render('Order/Dispatched', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only('search', 'page')
        ]);
    }

    public function delivered(Request $request)
    {
        $orders = Order::with(['facility', 'user'])
            ->where('status', 'delivered')
            ->latest()
            ->get();

        return Inertia::render('Order/Delivered', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only('search', 'page')
        ]);
    }

    public function received(Request $request)
    {
        $orders = Order::with(['facility', 'user'])
            ->where('status', 'received')
            ->latest()
            ->get();

        return Inertia::render('Order/Received', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only('search', 'page')
        ]);
    }

    public function all(Request $request)
    {
        $query = Order::with(['facility', 'user']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('facility', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(10);

        return Inertia::render('Order/All', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only('search', 'page')
        ]);
    }
    
}
