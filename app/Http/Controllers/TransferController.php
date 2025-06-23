<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Facility;
use App\Models\FacilityInventory;
use App\Models\FacilityInventoryItem;
use App\Models\FacilityBackorder;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Product;
use App\Models\IssuedQuantity;
use App\Models\Disposal;
use App\Models\BackOrderHistory;
use App\Models\BackOrder;
use App\Models\InventoryItem;
use App\Models\Liquidate;
use App\Models\ReceivedQuantity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TransferCreated;
use App\Events\TransferStatusChanged;
use App\Events\InventoryUpdated;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TransferResource;

class TransferController extends Controller
{

    public function changeItemStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'transfer_id' => 'required|exists:transfers,id',
                'status' => 'required|in:reviewed,approved,rejected,in_process,dispatched,delivered,received'
            ]);

            $transfer = Transfer::find($request->transfer_id);
            if(!$transfer){
                return response()->json("Not Found or you are not authorized to take this action", 500);
            }
            
            // Determine user's role in the transfer
            $currentUser = auth()->user();
            $currentWarehouse = $currentUser->warehouse;
            $currentFacility = $currentUser->facility_id;
            
            // User is sender if their warehouse/facility is the source
            $isSender = ($transfer->from_warehouse_id === $currentWarehouse?->id) || 
                       ($transfer->from_facility_id === $currentFacility);
            
            // User is receiver if their warehouse/facility is the destination
            $isReceiver = ($transfer->to_warehouse_id === $currentWarehouse?->id) || 
                         ($transfer->to_facility_id === $currentFacility);
            
            // Store the old status before making any changes
            $oldStatus = $transfer->status;
            $newStatus = $request->status;

            // pending -> reviewed (SENDER ACTION)
            if($oldStatus == 'pending' && $newStatus == 'reviewed' && $isSender && auth()->user()->can('transfer.review')){                
                $transfer->update([
                    'status' => 'reviewed',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            // pending -> rejected (branch) (SENDER ACTION)
            if($oldStatus == 'pending' && $newStatus == 'rejected' && $isSender && auth()->user()->can('transfer.approve')){
                $transfer->update([
                    'status' => 'rejected',
                    'rejected_by' => auth()->id(),
                    'rejected_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            // reviewed -> approved (SENDER ACTION)
            if($oldStatus == 'reviewed' && $newStatus == 'approved' && $isSender && auth()->user()->can('transfer.approve')){                
                $transfer->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            // reviewed -> rejected (branch) (SENDER ACTION)
            if($oldStatus == 'reviewed' && $newStatus == 'rejected' && $isSender && auth()->user()->can('transfer.approve')){
                $transfer->update([
                    'status' => 'rejected',
                    'rejected_by' => auth()->id(),
                    'rejected_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            // approved -> in_process (SENDER ACTION)
            if($oldStatus == 'approved' && $newStatus == 'in_process' && $isSender && auth()->user()->can('transfer.in_process')){
                $transfer->update([
                    'status' => 'in_process',
                    'processed_by' => auth()->id(),
                    'processed_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }

            // in_process -> dispatched (SENDER ACTION)
            if($oldStatus == 'in_process' && $newStatus == 'dispatched' && $isSender && auth()->user()->can('transfer.dispatch')){
                $transfer->update([
                    'status' => 'dispatched',
                    'dispatched_by' => auth()->id(),    
                    'dispatched_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            // dispatched -> delivered (RECEIVER ACTION)
            if($oldStatus == 'dispatched' && $newStatus == 'delivered' && $isReceiver && auth()->user()->can('transfer.deliver')){
                $transfer->update([
                    'status' => 'delivered',
                    'delivered_by' => auth()->id(),    
                    'delivered_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            // delivered -> received (RECEIVER ACTION)
            if($oldStatus == 'delivered' && $newStatus == 'received' && $isReceiver && auth()->user()->can('transfer.receive')){
                $transfer->update([
                    'status' => 'received',
                    'received_by' => auth()->id(),    
                    'received_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            DB::commit();
            
            // Return debug information along with success message
            return response()->json("Transfer status changed successfully", 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function index(Request $request)
    {
        // Start building the query
        $query = Transfer::with('fromWarehouse', 'toWarehouse', 'fromFacility','fromFacility', 'fromWarehouse', 'toFacility', 'items')
            ->withCount('items')
            ->latest('transfer_date');
        
        // Apply filters
        // Filter by tab/status
        if ($request->has('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }
        
        // Filter by search term
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('transferID', 'like', $searchTerm)
                  ->orWhereHas('fromFacility', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm);
                  })
                  ->orWhereHas('toFacility', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm);
                  })
                  ->orWhereHas('fromWarehouse', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm);
                  })
                  ->orWhereHas('toWarehouse', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm);
                  });
            });
        }
        
        if ($request->filled('transfer_type')) {
            switch ($request->transfer_type) {
                case 'Warehouse to Warehouse':
                    $query->whereNotNull('from_warehouse_id')
                          ->whereNotNull('to_warehouse_id');
                    break;
        
                case 'Facility to Facility':
                    $query->whereNotNull('from_facility_id')
                          ->whereNotNull('to_facility_id');
                    break;
        
                case 'Facility to Warehouse':
                    $query->whereNotNull('from_facility_id')
                          ->whereNotNull('to_warehouse_id');
                    break;
        
                case 'Warehouse to Facility':
                    $query->whereNotNull('from_warehouse_id')
                          ->whereNotNull('to_facility_id');
                    break;
            }
        }
        

        if($request->filled('date_from') && !$request->filled('date_to')){
            $query->whereDate('transfer_date', $request->date_from);
        }

        if($request->filled('transfer_type') && $request->transfer_type == 'Facility'){
            $query->whereHas('toFacility')
            ->whereHas('fromFacility');
        }

        if($request->filled('transfer_type') && $request->transfer_type == 'Warehouse'){
            $query->whereHas('toWarehouse')
            ->whereHas('fromWarehouse');
        }
        
        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transfer_date', [$request->date_from, $request->date_to]);
        }
        
        // Execute the query
        $transfers = $query->paginate($request->input('per_page', 2), ['*'], 'page', $request->input('page', 1))
        ->withQueryString();
    $transfers->setPath(url()->current()); // Force Laravel to use full URLs
        
        // Get all transfers for statistics (unfiltered)
        $allTransfers = Transfer::all();
        $total = $allTransfers->count();
        $approvedCount = $allTransfers->whereIn('status', ['approved'])->count();
        $inProcessCount = $allTransfers->whereIn('status', ['in_process', 'dispatched'])->count();
        $dispatchedCount = $allTransfers->where('status', 'dispatched')->count();
        $receivedCount = $allTransfers->where('status', 'received')->count();
        $rejectedCount = $allTransfers->where('status', 'rejected')->count();
        $pendingCount = $allTransfers->where('status', 'pending')->count();
        
        $statistics = [
            'approved' => [
                'count' => $approvedCount,
                'percentage' => $total > 0 ? round(($approvedCount / $total) * 100) : 0,
                'stages' => ['approved']
            ],
            'pending' => [
                'count' => $pendingCount,
                'percentage' => $total > 0 ? round(($pendingCount / $total) * 100) : 0,
                'stages' => ['pending']
            ],
            'in_process' => [
                'count' => $inProcessCount,
                'percentage' => $total > 0 ? round(($inProcessCount / $total) * 100) : 0,
                'stages' => ['in_process']
            ],
            'dispatched' => [
                'count' => $dispatchedCount,
                'percentage' => $total > 0 ? round(($dispatchedCount / $total) * 100) : 0,
                'stages' => ['dispatched']
            ],
            'received' => [
                'count' => $receivedCount,
                'percentage' => $total > 0 ? round(($receivedCount / $total) * 100) : 0,
                'stages' => ['received']
            ],
            'rejected' => [
                'count' => $rejectedCount,
                'percentage' => $total > 0 ? round(($rejectedCount / $total) * 100) : 0,
                'stages' => ['rejected']
            ]
        ];
        
        // Get data for filter dropdowns
        $facilities = Facility::pluck('name')->toArray();
        $warehouses = Warehouse::pluck('name')->toArray();
        $locations = DB::table('locations')->select('id', 'location')->orderBy('location')->get();

        return inertia('Transfer/Index', [
            'transfers' => TransferResource::collection($transfers),
            'statistics' => $statistics,
            'facilities' => $facilities,
            'warehouses' => $warehouses,
            'locations' => $locations,
            'filters' => $request->only(['search', 'facility', 'warehouse', 'date_from', 'date_to', 'tab','per_page','pgae'])
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'source_type' => 'required|in:warehouse,facility',
                'source_id' => 'required|integer',
                'destination_type' => 'required|in:warehouse,facility',
                'destination_id' => 'required|integer',
                'transfer_date' => 'required|date',
                'transferID' => 'required',
                'items' => 'required|array',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:500'
            ]);
    
            $transferData = [
                'transferID' => $request->transferID,
                'transfer_date' => $request->transfer_date,
                'from_warehouse_id' => $request->source_type === 'warehouse' ? $request->source_id : null,
                'from_facility_id' => $request->source_type === 'facility' ? $request->source_id : null,
                'to_warehouse_id' => $request->destination_type === 'warehouse' ? $request->destination_id : null,
                'to_facility_id' => $request->destination_type === 'facility' ? $request->destination_id : null,
                'created_by' => auth()->id(),
            ];
    
            $transfer = Transfer::create($transferData);
    
            foreach ($request->items as $item) {
                $remainingQty = $item['quantity'];
                $sourceId = $request->source_id;

                if ($request->source_type === 'warehouse') {
                    $inventories = InventoryItem::where('product_id', $item['product_id'])
                        ->where('warehouse_id', $sourceId)
                        ->with('product:id,name')
                        ->where('quantity', '>', 0)
                        ->where('expiry_date', '>', \Carbon\Carbon::now())
                        ->orderBy('expiry_date', 'asc')
                        ->get();
                } else {
                    $inventories = FacilityInventoryItem::where('product_id', $item['product_id'])
                        ->whereHas('inventory', function($query) use ($request) {
                            $query->where('facility_id', $request->source_id);
                        })
                        ->where('quantity', '>', 0)
                        ->where('expiry_date', '>', \Carbon\Carbon::now())
                        ->orderBy('expiry_date', 'asc')
                        ->get();
                }

                // Calculate total quantity on hand for this product (excluding expired)
                $warehouseQuantity = InventoryItem::where('product_id', $item['product_id'])
                    ->where('quantity', '>', 0)
                    ->where('expiry_date', '>', \Carbon\Carbon::now())
                    ->sum('quantity');

                $facilityQuantity = FacilityInventoryItem::where('product_id', $item['product_id'])
                    ->where('quantity', '>', 0)
                    ->where('expiry_date', '>', \Carbon\Carbon::now())
                    ->sum('quantity');

                $totalQuantityOnHand = $warehouseQuantity + $facilityQuantity;

                // Create transfer item for this product
                $transferItem = $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'], // Total requested quantity
                    'quantity_to_release' => $item['quantity'],
                    'quantity_per_unit' => $totalQuantityOnHand // Save total quantity on hand at time of transfer creation
                ]);

                // Process each inventory item to fulfill the transfer quantity
                foreach ($inventories as $inventory) {
                    if ($remainingQty <= 0) break;

                    $deductQty = min($remainingQty, $inventory->quantity);
                    
                    // Create inventory allocation record for detailed tracking
                    $transferItem->inventory_allocations()->create([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $request->source_type === 'warehouse' ? $sourceId : null,
                        'batch_number' => $inventory->batch_number ?? null,
                        'expiry_date' => $inventory->expiry_date ?? null,
                        'allocated_quantity' => $deductQty,
                        'allocation_type' => 'transfer',
                        'unit_cost' => $inventory->unit_cost ?? 0,
                        'total_cost' => $deductQty * ($inventory->unit_cost ?? 0),
                    ]);

                    // Deduct from source inventory
                    $inventory->quantity -= $deductQty;
                    $inventory->save();

                    // Update remaining quantity needed
                    $remainingQty -= $deductQty;
                }
        
                // Check if we couldn't fulfill the complete request
                if ($remainingQty > 0) {
                    DB::rollBack();
                    return response()->json("Not enough stock to fulfill {$item['quantity']} units for Item: {$item['product']['name']}", 400);
                }
            }
    
            $transfer->load(['fromWarehouse', 'toWarehouse', 'fromFacility', 'toFacility', 'items.product']);
    
            if ($transfer->to_warehouse_id && $transfer->toWarehouse?->manager_email) {
                Notification::route('mail', $transfer->toWarehouse->manager_email)
                    ->notify(new TransferCreated($transfer));
            } elseif ($transfer->to_facility_id && $transfer->toFacility?->email) {
                Notification::route('mail', $transfer->toFacility->email)
                    ->notify(new TransferCreated($transfer));
            }
    
            DB::commit();
            return response()->json('Transfer created successfully', 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return response()->json('Failed to create transfer: ' . $e->getMessage(), 500);
        }
    }

    public function show($id){
        $transfer = Transfer::where('id', $id)->with([
            'items.product.category', 
            'items.backorders', 
            'fromWarehouse', 
            'toWarehouse', 
            'fromFacility', 
            'toFacility',
            'items.inventory_allocations.location',
            'items.inventory_allocations.back_order','reviewedBy', 'approvedBy', 'processedBy','dispatchedBy','deliveredBy','receivedBy'
        ])->first();

        return inertia('Transfer/Show', [
            'transfer' => $transfer
        ]);
    }
    
    public function create(Request $request){
        $warehouses = Warehouse::select('id','name')->get();
        $facilities = Facility::select('id','name')->get();
        $transferID = Transfer::generateTransferId();
        
        return inertia('Transfer/Create', [
            'warehouses' => $warehouses,
            'facilities' => $facilities,
            'transferID' => $transferID
        ]);
    }
    
    /**
     * Delete a transfer item
     */
    public function destroyItem($id)
    {
        try {
            $transferItem = TransferItem::findOrFail($id);
            
            // Check if the transfer is in a state where items can be deleted
            $transfer = Transfer::findOrFail($transferItem->transfer_id);
            
            if (!in_array($transfer->status, ['pending', 'draft'])) {
                return response()->json('Cannot delete items from a transfer that is not in pending or draft status', 500);
            }
            
            // Delete the transfer item
            $transferItem->delete();
            
            return response()->json('Transfer item deleted successfully');
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    // get transfer source imventory
    public function getSourceInventoryDetail(Request $request)
    {
        try {
            return DB::transaction(function() use ($request){
                $request->validate([
                    'source_type' => 'required|in:warehouse,facility',
                    'source_id' => 'required|integer',
                    'product_id' => 'required|integer',
                ]);
                
                // Get current date for expiry comparison
                $currentDate = Carbon::now()->toDateString();
                
                if ($request->source_type === 'warehouse') {
                    $inventory = InventoryItem::where('product_id', $request->product_id)
                        ->where('warehouse_id', $request->source_id)
                        ->where('quantity', '>', 0)
                        ->where(function($query) use ($currentDate) {
                            $query->whereNull('expiry_date')
                                  ->orWhere('expiry_date', '>=', $currentDate);
                        })
                        ->with('warehouse:id,name','product:id,name')
                        ->get();
                } else {
                    $inventory = FacilityInventoryItem::where('product_id', $request->product_id)
                        ->whereHas('inventory', function($query) use ($request) {
                            $query->where('facility_id', $request->source_id);
                        })
                        ->where('quantity', '>', 0)
                        ->where(function($query) use ($currentDate) {
                            $query->whereNull('expiry_date')
                                  ->orWhere('expiry_date', '>=', $currentDate);
                        })
                        ->with('product:id,name')
                        ->get();
                }
                
                // Check if no valid inventory items are available
                if ($inventory->isEmpty()) {
                    return response()->json('No available inventory items for transfer', 500);
                }
                
                return response()->json($inventory, 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
    /**
     * Get inventories based on source type and ID
     */
    // public function getInventories(Request $request)
    // {
    //     $request->validate([
    //         'source_type' => 'required|in:warehouse,facility',
    //         'source_id' => 'required|integer',
    //     ]);
        
    //     try {
    //         if ($request->source_type === 'warehouse') {
    //             // Get warehouse inventories directly with DB query
    //             $products = Product::whereHas('inventories.items', function($query) use ($request) {
    //                 $query->where('warehouse_id', $request->source_id);
    //             })
    //                 ->select('id','name')                   
    //                 ->get();
                
    //             return response()->json($products, 200);
    //         } else {
    //             // Get facility inventories directly with DB query
    //             $products = Product::whereHas('facilityInventories', function($query) use ($request) {
    //                 $query->where('facility_id', $request->source_id)
    //                       ->whereHas('items', function($subQuery) {
    //                           $subQuery->where('quantity', '>', 0);
    //                       });
    //             })
    //                 ->select('id','name')
    //                 ->get();
                
    //             return response()->json($products, 200);
    //         }
    //     } catch (\Throwable $th) {
    //         logger()->info($th->getMessage());
    //         return response()->json($th->getMessage(), 500);
    //     }
    // }

    public function updateItem(Request $request){
        try {
            DB::beginTransaction();
            
            $request->validate([
                'id' => 'required|exists:transfer_items,id',
                'quantity' => 'required|numeric|min:1',
            ]);
            
            $transferItem = TransferItem::with('transfer')->findOrFail($request->id);
            $transfer = $transferItem->transfer;

            if($transferItem->quantity <= 0) {
                $transferItem->quantity = $request->quantity;
                $transferItem->save();
                $transferItem->refresh();
            }

            if (!in_array($transfer->status, ['pending'])) {
                return response()->json('Cannot update quantity for transfers that are not in pending status', 500);
            }

            // Use the requested quantity directly for transfers
            $newQuantityToRelease = (int) ceil($request->quantity);
            $oldQuantityToRelease = $transferItem->quantity_to_release ?? 0;

            // Determine source type (warehouse or facility)
            $isFromWarehouse = !empty($transfer->from_warehouse_id);
            $sourceId = $transfer->from_warehouse_id ?? $transfer->from_facility_id;

            // Case 1: Decrease
            if ($newQuantityToRelease < $oldQuantityToRelease) {
                $quantityToRemove = $oldQuantityToRelease - $newQuantityToRelease;
                $remainingToRemove = $quantityToRemove;

                $allocations = $transferItem->inventory_allocations()->orderBy('expiry_date', 'desc')->get();

                foreach ($allocations as $allocation) {
                    if ($remainingToRemove <= 0) break;

                    if ($isFromWarehouse) {
                        // Handle warehouse inventory
                        $inventory = InventoryItem::where('product_id', $allocation->product_id)
                            ->where('warehouse_id', $allocation->warehouse_id)
                            ->where('batch_number', $allocation->batch_number)
                            ->where('expiry_date', $allocation->expiry_date)
                            ->first();

                        $restoreQty = min($allocation->allocated_quantity, $remainingToRemove);

                        if ($inventory) {
                            $inventory->quantity += $restoreQty;
                            $inventory->save();
                        } else {
                            InventoryItem::create([
                                'product_id'   => $allocation->product_id,
                                'warehouse_id' => $allocation->warehouse_id,
                                'location_id'  => $allocation->location_id,
                                'batch_number' => $allocation->batch_number,
                                'uom'          => $allocation->uom,
                                'barcode'      => $allocation->barcode,
                                'expiry_date'  => $allocation->expiry_date,
                                'quantity'     => $restoreQty
                            ]);
                        }
                    } else {
                        // Handle facility inventory
                        $facilityInventory = FacilityInventory::where('facility_id', $sourceId)
                            ->where('product_id', $allocation->product_id)
                            ->first();

                        if ($facilityInventory) {
                            $facilityInventoryItem = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                                ->where('batch_number', $allocation->batch_number)
                                ->where('expiry_date', $allocation->expiry_date)
                                ->first();

                            $restoreQty = min($allocation->allocated_quantity, $remainingToRemove);

                            if ($facilityInventoryItem) {
                                $facilityInventoryItem->quantity += $restoreQty;
                                $facilityInventoryItem->save();
                            } else {
                                FacilityInventoryItem::create([
                                    'facility_inventory_id' => $facilityInventory->id,
                                    'batch_number' => $allocation->batch_number,
                                    'uom'          => $allocation->uom,
                                    'barcode'      => $allocation->barcode,
                                    'expiry_date'  => $allocation->expiry_date,
                                    'quantity'     => $restoreQty
                                ]);
                            }
                        }
                    }

                    if ($allocation->allocated_quantity <= $remainingToRemove) {
                        $remainingToRemove -= $allocation->allocated_quantity;
                        $allocation->delete();
                    } else {
                        $allocation->allocated_quantity -= $remainingToRemove;
                        $allocation->save();
                        $remainingToRemove = 0;
                    }
                }

                $transferItem->quantity_to_release = $newQuantityToRelease;
                $transferItem->save();

                DB::commit();
                return response()->json('Quantity to release decreased successfully', 200);
            }

            // Case 2: Increase
            if ($newQuantityToRelease > $oldQuantityToRelease) {
                $quantityToAdd = $newQuantityToRelease - $oldQuantityToRelease;
                $remainingToAllocate = $quantityToAdd;

                if ($isFromWarehouse) {
                    // Handle warehouse inventory
                    $inventoryItems = InventoryItem::where('product_id', $transferItem->product_id)
                        ->where('warehouse_id', $sourceId)
                        ->where('quantity', '>', 0)
                        ->where(function($query) {
                            $query->where('expiry_date', '>', \Carbon\Carbon::now())
                                  ->orWhereNull('expiry_date');
                        })
                        ->orderBy('expiry_date', 'asc')
                        ->get();

                    if ($inventoryItems->isEmpty()) {
                        DB::rollBack();
                        return response()->json('No inventory available for this product in the warehouse', 500);
                    }

                    foreach ($inventoryItems as $inventory) {
                        if ($remainingToAllocate <= 0) break;

                        $allocQty = min($inventory->quantity, $remainingToAllocate);

                        $existingAllocation = $transferItem->inventory_allocations()
                            ->where('batch_number', $inventory->batch_number)
                            ->where('expiry_date', $inventory->expiry_date)
                            ->first();

                        if ($existingAllocation) {
                            $existingAllocation->allocated_quantity += $allocQty;
                            $existingAllocation->save();
                        } else {
                            $transferItem->inventory_allocations()->create([
                                'product_id'       => $inventory->product_id,
                                'warehouse_id'     => $inventory->warehouse_id,
                                'location_id'      => $inventory->location_id,
                                'batch_number'     => $inventory->batch_number,
                                'uom'              => $inventory->uom,
                                'barcode'          => $inventory->barcode ?? null,
                                'expiry_date'      => $inventory->expiry_date,
                                'allocated_quantity' => $allocQty,
                                'allocation_type'  => $transfer->transfer_type,
                                'unit_cost'        => $inventory->unit_cost,
                                'total_cost'       => $inventory->unit_cost * $allocQty,
                                'notes'            => 'Allocated from warehouse inventory ID: ' . $inventory->id
                            ]);
                        }

                        $inventory->quantity -= $allocQty;
                        $inventory->save();
                        $remainingToAllocate -= $allocQty;
                    }
                } else {
                    // Handle facility inventory
                    $facilityInventory = FacilityInventory::where('facility_id', $sourceId)
                        ->where('product_id', $transferItem->product_id)
                        ->first();

                    if (!$facilityInventory) {
                        DB::rollBack();
                        return response()->json('No inventory available for this product in the facility', 500);
                    }

                    $facilityInventoryItems = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                        ->where('quantity', '>', 0)
                        ->where(function($query) {
                            $query->where('expiry_date', '>', \Carbon\Carbon::now())
                                  ->orWhereNull('expiry_date');
                        })
                        ->orderBy('expiry_date', 'asc')
                        ->get();

                    if ($facilityInventoryItems->isEmpty()) {
                        DB::rollBack();
                        return response()->json('No inventory available for this product in the facility', 500);
                    }

                    foreach ($facilityInventoryItems as $facilityItem) {
                        if ($remainingToAllocate <= 0) break;

                        $allocQty = min($facilityItem->quantity, $remainingToAllocate);

                        $existingAllocation = $transferItem->inventory_allocations()
                            ->where('batch_number', $facilityItem->batch_number)
                            ->where('expiry_date', $facilityItem->expiry_date)
                            ->first();

                        if ($existingAllocation) {
                            $existingAllocation->allocated_quantity += $allocQty;
                            $existingAllocation->save();
                        } else {
                            $transferItem->inventory_allocations()->create([
                                'product_id'       => $facilityInventory->product_id,
                                'facility_id'      => $sourceId,
                                'batch_number'     => $facilityItem->batch_number,
                                'uom'              => $facilityItem->uom,
                                'barcode'          => $facilityItem->barcode ?? null,
                                'expiry_date'      => $facilityItem->expiry_date,
                                'allocated_quantity' => $allocQty,
                                'allocation_type'  => $transfer->transfer_type,
                                'unit_cost'        => 0, // Facility items might not have unit cost
                                'total_cost'       => 0,
                                'notes'            => 'Allocated from facility inventory ID: ' . $facilityItem->id
                            ]);
                        }

                        $facilityItem->quantity -= $allocQty;
                        $facilityItem->save();
                        $remainingToAllocate -= $allocQty;
                    }
                }

                // Final adjustment
                $totalAllocated = $transferItem->inventory_allocations()->sum('allocated_quantity');
                if ($totalAllocated < $newQuantityToRelease) {
                    $difference = $newQuantityToRelease - $totalAllocated;
                    $lastAllocation = $transferItem->inventory_allocations()->latest()->first();

                    if ($lastAllocation) {
                        $lastAllocation->allocated_quantity += $difference;
                        $lastAllocation->save();

                        if ($isFromWarehouse) {
                            $inventory = InventoryItem::where('product_id', $lastAllocation->product_id)
                                ->where('warehouse_id', $lastAllocation->warehouse_id)
                                ->where('batch_number', $lastAllocation->batch_number)
                                ->where('expiry_date', $lastAllocation->expiry_date)
                                ->first();

                            if ($inventory) {
                                $inventory->quantity -= $difference;
                                $inventory->save();
                            }
                        } else {
                            $facilityInventory = FacilityInventory::where('facility_id', $sourceId)
                                ->where('product_id', $lastAllocation->product_id)
                                ->first();

                            if ($facilityInventory) {
                                $facilityItem = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                                    ->where('batch_number', $lastAllocation->batch_number)
                                    ->where('expiry_date', $lastAllocation->expiry_date)
                                    ->first();

                                if ($facilityItem) {
                                    $facilityItem->quantity -= $difference;
                                    $facilityItem->save();
                                }
                            }
                        }
                    }
                }

                if ($remainingToAllocate > 0) {
                    DB::rollBack();
                    $sourceType = $isFromWarehouse ? 'warehouse' : 'facility';
                    return response()->json("Insufficient inventory in {$sourceType}. Could only allocate " . ($quantityToAdd - $remainingToAllocate) . ' out of ' . $quantityToAdd, 500);
                }

                $transferItem->quantity_to_release = $newQuantityToRelease;
                $transferItem->save();

                event(new InventoryUpdated());

                DB::commit();
                return response()->json('Quantity to release updated successfully', 200);
            }

            // No change
            DB::commit();
            return response()->json('No change in quantity to release', 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    
    /**
     * Save back orders for a transfer item with detailed issue types
     */
    public function saveBackOrders(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'item_id' => 'required|exists:transfer_items,id',
                'back_orders' => 'required|array',
                'back_orders.*.quantity' => 'required|integer|min:1',
                'back_orders.*.status' => 'required|string|in:Missing,Damaged,Lost,Expired,Low quality',
                'back_orders.*.note' => 'nullable|string'
            ],[
                'back_orders.*.quantity.required' => 'Quantity is required',
                'back_orders.*.status.required' => 'Status is required',
                'back_orders.*.note' => 'Note is required'
            ]);

            $transferItem = TransferItem::findOrFail($request->item_id);
            $transfer = $transferItem->transfer;

            // Verify user has permission to save back orders for this transfer
            if (!in_array($transfer->status, ['pending', 'shipped', 'received'])) {
                return response()->json('Cannot save back orders for transfers with this status', 400);
            }

            // Calculate missing quantity
            $missingQuantity = $transferItem->quantity_to_release - ($transferItem->received_quantity ?? 0);
            
            // Validate total back order quantity doesn't exceed missing quantity
            $totalBackOrderQuantity = collect($request->back_orders)->sum('quantity');
            if ($totalBackOrderQuantity > $missingQuantity) {
                return response()->json('Total back order quantity cannot exceed missing quantity', 400);
            }

            // Clear existing backorders for this transfer item to prevent duplicates
            FacilityBackorder::where('transfer_item_id', $transferItem->id)->delete();

            // Get existing allocations to find where to create back orders
            $allocations = $transferItem->inventory_allocations()
                ->orderBy('expiry_date', 'asc')
                ->get();

            $remainingToAllocate = $totalBackOrderQuantity;
            
            foreach ($request->back_orders as $backOrderData) {
                $quantityForThisStatus = $backOrderData['quantity'];
                $tempRemaining = $quantityForThisStatus;

                foreach ($allocations as $allocation) {
                    if ($tempRemaining <= 0) break;

                    $quantityToAllocateFromThisAllocation = min($tempRemaining, $allocation->allocated_quantity);

                    // Create back order record using updateOrCreate for safety
                    FacilityBackorder::updateOrCreate([
                        'inventory_allocation_id' => $allocation->id,
                        'transfer_item_id' => $transferItem->id,
                        'product_id' => $transferItem->product_id,
                        'type' => $backOrderData['status'],
                    ], [
                        'quantity' => $quantityToAllocateFromThisAllocation,
                        'notes' => $backOrderData['note'] ?? null,
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    $tempRemaining -= $quantityToAllocateFromThisAllocation;
                }
            }

            // Update transfer item received quantity to reflect what was actually received
            // (This helps track the progress and shows the shortfall)
            $actualReceivedQuantity = $transferItem->quantity_to_release - $totalBackOrderQuantity;
            $transferItem->received_quantity = max($transferItem->received_quantity ?? 0, $actualReceivedQuantity);
            $transferItem->save();

            // Update transfer status if needed
            $this->updateTransferStatusIfNeeded($transfer);

            event(new InventoryUpdated());

            DB::commit();
            return response()->json('Back orders saved successfully', 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Helper method to update transfer status based on item completion
     */
    private function updateTransferStatusIfNeeded($transfer)
    {
        $allItemsProcessed = $transfer->items->every(function ($item) {
            $missingQuantity = $item->quantity_to_release - ($item->received_quantity ?? 0);
            $existingBackOrders = $item->inventory_allocations()
                ->whereHas('back_order')
                ->with('back_order')
                ->get()
                ->flatMap(function($allocation) {
                    return $allocation->back_order;
                })
                ->sum('quantity');
            
            return $missingQuantity <= $existingBackOrders;
        });

        if ($allItemsProcessed && $transfer->status === 'shipped') {
            $transfer->status = 'received';
            $transfer->save();
        }
    }
    
    /**
     * Get inventories based on source type and ID
     */
    public function getInventories(Request $request)
    {
        $request->validate([
            'source_type' => 'required|in:warehouse,facility',
            'source_id' => 'required|integer',
        ]);
        
        try {
            if ($request->source_type === 'warehouse') {
                // Get warehouse inventories directly with DB query
                $products = Product::whereHas('inventories.items', function($query) use ($request) {
                    $query->where('warehouse_id', $request->source_id);
                })
                    ->select('id','name')                   
                    ->get();
                
                return response()->json($products, 200);
            } else {
                // Get facility inventories directly with DB query
                $products = Product::whereHas('facilityInventories', function($query) use ($request) {
                    $query->where('facility_id', $request->source_id)
                          ->whereHas('items', function($subQuery) {
                              $subQuery->where('quantity', '>', 0);
                          });
                })
                    ->select('id','name')
                    ->get();
                
                return response()->json($products, 200);
            }
        } catch (\Throwable $th) {
            logger()->info($th->getMessage());
            return response()->json($th->getMessage(), 500);
        }
    }

    public function updateQuantity(Request $request){
        try {
            DB::beginTransaction();

            $request->validate([
                'item_id'  => 'required|exists:transfer_items,id',
                'quantity' => 'required|numeric'
            ]);

            $transferItem = TransferItem::findOrFail($request->item_id);
            $transfer = $transferItem->transfer;

            if($transferItem->quantity <= 0) {
                $transferItem->quantity = $request->quantity;
                $transferItem->save();
                $transferItem->refresh();
            }

            if (!in_array($transfer->status, ['pending'])) {
                return response()->json('Cannot update quantity for transfers that are not in pending status', 500);
            }

            // Use the requested quantity directly for transfers
            $newQuantityToRelease = (int) ceil($request->quantity);
            $oldQuantityToRelease = $transferItem->quantity_to_release ?? 0;

            // Determine source type (warehouse or facility)
            $isFromWarehouse = !empty($transfer->from_warehouse_id);
            $sourceId = $transfer->from_warehouse_id ?? $transfer->from_facility_id;

            // Case 1: Decrease
            if ($newQuantityToRelease < $oldQuantityToRelease) {
                $quantityToRemove = $oldQuantityToRelease - $newQuantityToRelease;
                $remainingToRemove = $quantityToRemove;

                $allocations = $transferItem->inventory_allocations()->orderBy('expiry_date', 'desc')->get();

                foreach ($allocations as $allocation) {
                    if ($remainingToRemove <= 0) break;

                    if ($isFromWarehouse) {
                        // Handle warehouse inventory
                        $inventory = InventoryItem::where('product_id', $allocation->product_id)
                            ->where('warehouse_id', $allocation->warehouse_id)
                            ->where('batch_number', $allocation->batch_number)
                            ->where('expiry_date', $allocation->expiry_date)
                            ->first();

                        $restoreQty = min($allocation->allocated_quantity, $remainingToRemove);

                        if ($inventory) {
                            $inventory->quantity += $restoreQty;
                            $inventory->save();
                        } else {
                            InventoryItem::create([
                                'product_id'   => $allocation->product_id,
                                'warehouse_id' => $allocation->warehouse_id,
                                'location_id'  => $allocation->location_id,
                                'batch_number' => $allocation->batch_number,
                                'uom'          => $allocation->uom,
                                'barcode'      => $allocation->barcode,
                                'expiry_date'  => $allocation->expiry_date,
                                'quantity'     => $restoreQty
                            ]);
                        }
                    } else {
                        // Handle facility inventory
                        $facilityInventory = FacilityInventory::where('facility_id', $sourceId)
                            ->where('product_id', $allocation->product_id)
                            ->first();

                        if ($facilityInventory) {
                            $facilityInventoryItem = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                                ->where('batch_number', $allocation->batch_number)
                                ->where('expiry_date', $allocation->expiry_date)
                                ->first();

                            $restoreQty = min($allocation->allocated_quantity, $remainingToRemove);

                            if ($facilityInventoryItem) {
                                $facilityInventoryItem->quantity += $restoreQty;
                                $facilityInventoryItem->save();
                            } else {
                                FacilityInventoryItem::create([
                                    'facility_inventory_id' => $facilityInventory->id,
                                    'batch_number' => $allocation->batch_number,
                                    'uom'          => $allocation->uom,
                                    'barcode'      => $allocation->barcode,
                                    'expiry_date'  => $allocation->expiry_date,
                                    'quantity'     => $restoreQty
                                ]);
                            }
                        }
                    }

                    if ($allocation->allocated_quantity <= $remainingToRemove) {
                        $remainingToRemove -= $allocation->allocated_quantity;
                        $allocation->delete();
                    } else {
                        $allocation->allocated_quantity -= $remainingToRemove;
                        $allocation->save();
                        $remainingToRemove = 0;
                    }
                }

                $transferItem->quantity_to_release = $newQuantityToRelease;
                $transferItem->save();

                DB::commit();
                return response()->json('Quantity to release decreased successfully', 200);
            }

            // Case 2: Increase
            if ($newQuantityToRelease > $oldQuantityToRelease) {
                $quantityToAdd = $newQuantityToRelease - $oldQuantityToRelease;
                $remainingToAllocate = $quantityToAdd;

                if ($isFromWarehouse) {
                    // Handle warehouse inventory
                    $inventoryItems = InventoryItem::where('product_id', $transferItem->product_id)
                        ->where('warehouse_id', $sourceId)
                        ->where('quantity', '>', 0)
                        ->where(function($query) {
                            $query->where('expiry_date', '>', \Carbon\Carbon::now())
                                  ->orWhereNull('expiry_date');
                        })
                        ->orderBy('expiry_date', 'asc')
                        ->get();

                    if ($inventoryItems->isEmpty()) {
                        DB::rollBack();
                        return response()->json('No inventory available for this product in the warehouse', 500);
                    }

                    foreach ($inventoryItems as $inventory) {
                        if ($remainingToAllocate <= 0) break;

                        $allocQty = min($inventory->quantity, $remainingToAllocate);

                        $existingAllocation = $transferItem->inventory_allocations()
                            ->where('batch_number', $inventory->batch_number)
                            ->where('expiry_date', $inventory->expiry_date)
                            ->first();

                        if ($existingAllocation) {
                            $existingAllocation->allocated_quantity += $allocQty;
                            $existingAllocation->save();
                        } else {
                            $transferItem->inventory_allocations()->create([
                                'product_id'       => $inventory->product_id,
                                'warehouse_id'     => $inventory->warehouse_id,
                                'location_id'      => $inventory->location_id,
                                'batch_number'     => $inventory->batch_number,
                                'uom'              => $inventory->uom,
                                'barcode'          => $inventory->barcode ?? null,
                                'expiry_date'      => $inventory->expiry_date,
                                'allocated_quantity' => $allocQty,
                                'allocation_type'  => $transfer->transfer_type,
                                'unit_cost'        => $inventory->unit_cost,
                                'total_cost'       => $inventory->unit_cost * $allocQty,
                                'notes'            => 'Allocated from warehouse inventory ID: ' . $inventory->id
                            ]);
                        }

                        $inventory->quantity -= $allocQty;
                        $inventory->save();
                        $remainingToAllocate -= $allocQty;
                    }
                } else {
                    // Handle facility inventory
                    $facilityInventory = FacilityInventory::where('facility_id', $sourceId)
                        ->where('product_id', $transferItem->product_id)
                        ->first();

                    if (!$facilityInventory) {
                        DB::rollBack();
                        return response()->json('No inventory available for this product in the facility', 500);
                    }

                    $facilityInventoryItems = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                        ->where('quantity', '>', 0)
                        ->where(function($query) {
                            $query->where('expiry_date', '>', \Carbon\Carbon::now())
                                  ->orWhereNull('expiry_date');
                        })
                        ->orderBy('expiry_date', 'asc')
                        ->get();

                    if ($facilityInventoryItems->isEmpty()) {
                        DB::rollBack();
                        return response()->json('No inventory available for this product in the facility', 500);
                    }

                    foreach ($facilityInventoryItems as $facilityItem) {
                        if ($remainingToAllocate <= 0) break;

                        $allocQty = min($facilityItem->quantity, $remainingToAllocate);

                        $existingAllocation = $transferItem->inventory_allocations()
                            ->where('batch_number', $facilityItem->batch_number)
                            ->where('expiry_date', $facilityItem->expiry_date)
                            ->first();

                        if ($existingAllocation) {
                            $existingAllocation->allocated_quantity += $allocQty;
                            $existingAllocation->save();
                        } else {
                            $transferItem->inventory_allocations()->create([
                                'product_id'       => $facilityInventory->product_id,
                                'facility_id'      => $sourceId,
                                'batch_number'     => $facilityItem->batch_number,
                                'uom'              => $facilityItem->uom,
                                'barcode'          => $facilityItem->barcode ?? null,
                                'expiry_date'      => $facilityItem->expiry_date,
                                'allocated_quantity' => $allocQty,
                                'allocation_type'  => $transfer->transfer_type,
                                'unit_cost'        => 0, // Facility items might not have unit cost
                                'total_cost'       => 0,
                                'notes'            => 'Allocated from facility inventory ID: ' . $facilityItem->id
                            ]);
                        }

                        $facilityItem->quantity -= $allocQty;
                        $facilityItem->save();
                        $remainingToAllocate -= $allocQty;
                    }
                }

                // Final adjustment
                $totalAllocated = $transferItem->inventory_allocations()->sum('allocated_quantity');
                if ($totalAllocated < $newQuantityToRelease) {
                    $difference = $newQuantityToRelease - $totalAllocated;
                    $lastAllocation = $transferItem->inventory_allocations()->latest()->first();

                    if ($lastAllocation) {
                        $lastAllocation->allocated_quantity += $difference;
                        $lastAllocation->save();

                        if ($isFromWarehouse) {
                            $inventory = InventoryItem::where('product_id', $lastAllocation->product_id)
                                ->where('warehouse_id', $lastAllocation->warehouse_id)
                                ->where('batch_number', $lastAllocation->batch_number)
                                ->where('expiry_date', $lastAllocation->expiry_date)
                                ->first();

                            if ($inventory) {
                                $inventory->quantity -= $difference;
                                $inventory->save();
                            }
                        } else {
                            $facilityInventory = FacilityInventory::where('facility_id', $sourceId)
                                ->where('product_id', $lastAllocation->product_id)
                                ->first();

                            if ($facilityInventory) {
                                $facilityItem = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                                    ->where('batch_number', $lastAllocation->batch_number)
                                    ->where('expiry_date', $lastAllocation->expiry_date)
                                    ->first();

                                if ($facilityItem) {
                                    $facilityItem->quantity -= $difference;
                                    $facilityItem->save();
                                }
                            }
                        }
                    }
                }

                if ($remainingToAllocate > 0) {
                    DB::rollBack();
                    $sourceType = $isFromWarehouse ? 'warehouse' : 'facility';
                    return response()->json("Insufficient inventory in {$sourceType}. Could only allocate " . ($quantityToAdd - $remainingToAllocate) . ' out of ' . $quantityToAdd, 500);
                }

                $transferItem->quantity_to_release = $newQuantityToRelease;
                $transferItem->save();

                event(new InventoryUpdated());

                DB::commit();
                return response()->json('Quantity to release updated successfully', 200);
            }

            // No change
            DB::commit();
            return response()->json('No change in quantity to release', 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    
    /**
     * Delete a specific back order record
     */
    public function deleteBackOrder(Request $request)
    {
        try {
            $request->validate([
                'backorder_id' => 'required|exists:facility_backorders,id',
            ]);

            $backorder = FacilityBackorder::findOrFail($request->backorder_id);
            $transferItem = $backorder->transferItem;
            
            // Verify user has permission to delete back orders for this transfer
            if (!in_array($transferItem->transfer->status, ['pending', 'shipped', 'received'])) {
                return response()->json(['error' => 'Cannot delete back orders for transfers with this status'], 400);
            }

            $backorder->delete();

            event(new InventoryUpdated());

            return response()->json('Back order deleted successfully', 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    
    /**
     * Change transfer status with proper permissions and workflow validation
     */
    public function changeStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'transfer_id' => 'required|exists:transfers,id',
                'status' => 'required|string|in:pending,reviewed,approved,in_process,dispatched,delivered,received'
            ]);

            $transfer = Transfer::findOrFail($request->transfer_id);
            $newStatus = $request->status;
            $oldStatus = $transfer->status;
            $user = auth()->user();

            // Define status progression order
            $statusOrder = ['pending', 'reviewed', 'approved', 'in_process', 'dispatched', 'delivered', 'received'];
            $currentStatusIndex = array_search($transfer->status, $statusOrder);
            $newStatusIndex = array_search($newStatus, $statusOrder);

            // Validate status progression (can only move forward, except for certain cases)
            if ($newStatusIndex <= $currentStatusIndex && $newStatus !== $transfer->status) {
                DB::rollBack();
                return response()->json('Cannot move backwards in transfer workflow', 400);
            }

            // Permission checks based on status
            switch ($newStatus) {
                case 'reviewed':
                    if (!$user->can('transfer_review')) {
                        DB::rollBack();
                        return response()->json('You do not have permission to review transfers', 403);
                    }
                    if ($transfer->status !== 'pending') {
                        DB::rollBack();
                        return response()->json('Transfer must be pending to review', 400);
                    }
                    $transfer->reviewed_at = now();
                    $transfer->reviewed_by = $user->id;
                    break;

                case 'approved':
                    if (!$user->can('transfer_approve')) {
                        DB::rollBack();
                        return response()->json('You do not have permission to approve transfers', 403);
                    }
                    if ($transfer->status !== 'reviewed') {
                        DB::rollBack();
                        return response()->json('Transfer must be reviewed to approve', 400);
                    }
                    $transfer->approved_at = now();
                    $transfer->approved_by = $user->id;
                    break;

                case 'in_process':
                    // Can be done by from warehouse/facility staff
                    if ($user->warehouse_id !== $transfer->from_warehouse_id && 
                        $user->facility_id !== $transfer->from_facility_id) {
                        DB::rollBack();
                        return response()->json('You can only process transfers from your warehouse/facility', 403);
                    }
                    if ($transfer->status !== 'approved') {
                        DB::rollBack();
                        return response()->json('Transfer must be approved to process', 400);
                    }
                    $transfer->processed_at = now();
                    $transfer->processed_by = $user->id;
                    break;

                case 'dispatched':
                    // Can be done by from warehouse/facility staff
                    if ($user->warehouse_id !== $transfer->from_warehouse_id && 
                        $user->facility_id !== $transfer->from_facility_id) {
                        DB::rollBack();
                        return response()->json('You can only dispatch transfers from your warehouse/facility', 403);
                    }
                    if ($transfer->status !== 'in_process') {
                        DB::rollBack();
                        return response()->json('Transfer must be in process to dispatch', 400);
                    }
                    $transfer->dispatched_at = now();
                    $transfer->dispatched_by = $user->id;
                    break;

                case 'delivered':
                    // Can be done by to warehouse/facility staff
                    if ($user->warehouse_id !== $transfer->to_warehouse_id && 
                        $user->facility_id !== $transfer->to_facility_id) {
                        DB::rollBack();
                        return response()->json('You can only mark transfers as delivered to your warehouse/facility', 403);
                    }
                    if ($transfer->status !== 'dispatched') {
                        DB::rollBack();
                        return response()->json('Transfer must be dispatched to deliver', 400);
                    }
                    $transfer->delivered_at = now();
                    $transfer->delivered_by = $user->id;
                    break;

                case 'received':
                    // Can be done by to warehouse/facility staff
                    if ($user->warehouse_id !== $transfer->to_warehouse_id && 
                        $user->facility_id !== $transfer->to_facility_id) {
                        DB::rollBack();
                        return response()->json('You can only receive transfers to your warehouse/facility', 403);
                    }
                    if ($transfer->status !== 'delivered') {
                        DB::rollBack();
                        return response()->json('Transfer must be delivered to receive', 400);
                    }
                    $transfer->received_at = now();
                    $transfer->received_by = $user->id;
                    break;

                default:
                    DB::rollBack();
                    return response()->json('Invalid status', 400);
            }

            // Update the status
            $transfer->status = $newStatus;
            $transfer->save();

            // Dispatch event for real-time updates
            event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, $user->id));

            DB::commit();
            return response()->json('Transfer status updated successfully', 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }
    }
    
    /**
     * Receive a back order
     */
    public function receiveBackOrder(Request $request){
        try {
            DB::beginTransaction();
            
            $request->validate([
                'backorder' => 'required',
                'quantity' => 'required|numeric|min:1',
            ]);
            
            $backorderData = $request->backorder;
            $receivedQuantity = $request->quantity;
            
            // Find the backorder record
            $backorder = FacilityBackorder::findOrFail($backorderData['id']);
            
            // Find the transfer item
            $transferItem = TransferItem::findOrFail($backorder->transfer_item_id);
            
            // Update the received quantity of the transfer item
            $transferItem->received_quantity += $receivedQuantity;
            $transferItem->save();
            
            // Deduct the received quantity from the backorder
            $backorder->quantity -= $receivedQuantity;
            
            // If quantity becomes zero, delete the backorder
            if ($backorder->quantity <= 0) {
                $backorder->delete();
            } else {
                // Otherwise, save the updated quantity
                $backorder->save();
            }
            
            // Get the warehouse ID from the transfer
            $transfer = Transfer::with('toWarehouse')->findOrFail($transferItem->transfer_id);
            $warehouseId = $transfer->to_warehouse_id;
            
            if (!$warehouseId) {
                throw new \Exception('No destination warehouse found for this transfer');
            }
            
            // Check if inventory exists for this product in the warehouse
            $inventory = Inventory::where('warehouse_id', $warehouseId)
                ->where('product_id', $backorder->product_id)
                ->where('batch_number', $transferItem->batch_number)
                ->where('expiry_date', $transferItem->expire_date)
                ->first();
            
            if ($inventory) {
                // Update existing inventory
                $inventory->quantity += $receivedQuantity;
                $inventory->save();
                ReceivedQuantity::create([
                    'quantity' => $receivedQuantity,
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                    'product_id' => $backorder->product_id,
                    'warehouse_id' => $warehouseId,
                    'transfer_id' => $transfer->id,
                    'expiry_date' => $transferItem->expire_date,
                    'uom' => $transferItem->uom,
                    'barcode' => $transferItem->barcode,
                    'batch_number' => $transferItem->batch_number,
                    'unit_cost' => $transferItem->unit_cost,
                    'total_cost' => $transferItem->unit_cost
                ]);
            } else {
                // Create new inventory record
                $inventory = Inventory::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $backorder->product_id,
                    'batch_number' => $transferItem->batch_number,
                    'expiry_date' => $transferItem->expire_date,
                    'barcode' => $transferItem->barcode,
                    'quantity' => $receivedQuantity,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
                ReceivedQuantity::create([
                    'transfer_id' => $transfer->id,
                    'quantity' => $receivedQuantity,
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                    'product_id' => $backorder->product_id,
                    'expiry_date' => $transferItem->expire_date,
                    'uom' => $transferItem->uom,
                    'warehouse_id' => $warehouseId,
                    'unit_cost' => $transferItem->unit_cost,
                    'total_cost' => $transferItem->unit_cost * $receivedQuantity,
                    'barcode' => $transferItem->barcode,
                    'batch_number' => $transferItem->batch_number,
                ]);
            }
            
            // Create backorder history record
            BackOrderHistory::create([
                'packing_list_id' => null, // No packing list for transfer backorders
                'transfer_id' => $transfer->id,
                'product_id' => $backorder->product_id,
                'quantity' => $receivedQuantity,
                'status' => "Received", // 'Missing' or 'Damaged'
                'note' => 'Backorder received and added to inventory',
                'performed_by' => auth()->id()
            ]);
            
            DB::commit();
            
            // Dispatch event for real-time inventory updates
            $inventoryData = [
                'product_id' => $backorder->product_id,
                'warehouse_id' => $warehouseId,
                'quantity' => $inventory->quantity,
                'batch_number' => $transferItem->batch_number,
                'expiry_date' => $transferItem->expire_date,
                'action' => 'received',
                'source' => 'backorder'
            ];
            event(new \App\Events\InventoryUpdated($inventoryData));
            return response()->json([
                'message' => 'Backorder received successfully'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }
    
}
