<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Facility;
use App\Models\FacilityInventory;
use App\Http\Resources\TransferResource;
use App\Models\FacilityBackorder;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Product;
use App\Models\IssuedQuantity;
use App\Models\Disposal;
use App\Models\BackOrderHistory;
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

class TransferController extends Controller
{

    public function changeItemStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'transfer_id' => 'required|exists:transfers,id',
                'status' => 'required|in:approved,in_process,dispatched'
            ]);

            $transfer = Transfer::find($request->transfer_id);
            if(!$transfer){
                return response()->json("Not Found or you are authorized to take this action", 500);
            }
            
            // Store the old status before making any changes
            $oldStatus = $transfer->status;
            $newStatus = $request->status;

            if($oldStatus == 'pending' && $newStatus == 'approved' && auth()->user()->can('transfer.approve')){                
                $transfer->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }
            
            if($oldStatus == 'approved' && $newStatus == 'in_process' && auth()->user()->can('transfer.in_process')){
                $transfer->update([
                    'status' => 'in_process',
                ]);
                
                // Dispatch event for status change
                event(new TransferStatusChanged($transfer, $oldStatus, $newStatus, auth()->id()));
            }

            if($oldStatus == 'in_process' && $newStatus == 'dispatched' && auth()->user()->can('transfer.dispatch')){
                $transfer->update([
                    'status' => 'dispatched',
                    'dispatched_by' => auth()->id(),    
                    'dispatched_at' => now()
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
                        ->orderBy('expiry_date', 'asc')
                        ->get();
                } else {
                    $inventories = FacilityInventory::where('product_id', $item['product_id'])
                        ->where('facility_id', $sourceId)
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->get();
                }
        
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
            'items.product', 
            'items.backorders', 
            'fromWarehouse', 
            'toWarehouse', 
            'fromFacility', 
            'toFacility'
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
                    // Check if any items are expired before fetching
                    $expiredCount = InventoryItem::where('product_id', $request->product_id)
                        ->where('warehouse_id', $request->source_id)
                        ->where('quantity', '>', 0)
                        ->where('expiry_date', '<=', $currentDate)
                        ->count();
                        
                    if ($expiredCount > 0) {
                        return response()->json('Cannot fetch expired inventory items for transfer', 500);
                    }
                    
                    $inventory = InventoryItem::where('product_id', $request->product_id)
                        ->where('warehouse_id', $request->source_id)
                        ->where('quantity', '>', 0)
                        ->where(function($query) use ($currentDate) {
                            $query->whereNull('expiry_date')
                                  ->orWhere('expiry_date', '>=', $currentDate);
                        })
                        ->with('location:id,location','warehouse:id,name','product:id,name')
                        ->get();
                } else {
                    // For facility inventory, also check for expired items
                    $facilityInventoryIds = FacilityInventoryItem::where('product_id', $request->product_id)
                        ->whereHas('inventory.facility', function($query) use ($request) {
                            $query->where('id', $request->source_id);
                        })
                        ->pluck('id');
                    
                    if ($facilityInventoryIds->isNotEmpty()) {
                        $expiredCount = FacilityInventoryItem::whereIn('id', $facilityInventoryIds)
                            ->where('quantity', '>', 0)
                            ->where('expiry_date', '<=', $currentDate)
                            ->count();
                            
                        if ($expiredCount > 0) {
                            return response()->json('This item is expired and cannot be transferred', 500);
                        }
                    }
                    
                    $inventory = FacilityInventoryItem::where('product_id', $request->product_id)
                        ->whereHas('inventory.facility', function($query) use ($request) {
                            $query->where('id', $request->source_id);
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
                $products = Product::whereHas('facilityInventories.items', function($query) use ($request) {
                    $query->where('facility_id', $request->source_id);
                })
                    ->select('id','name')
                    ->get();
                
                return response()->json($products, 200);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function updateItem(Request $request){
        try {
            DB::beginTransaction();
            
            $request->validate([
                'id' => 'required|exists:transfer_items,id',
                'quantity' => 'required|numeric|min:1',
            ]);
            
            $transferItem = TransferItem::with('transfer')->findOrFail($request->id);
            $transfer = $transferItem->transfer;

            if($transfer->status == 'dispatched') {
                return response()->json('Cannot update transfer item that is dispatched', 500);
            }
            
            // Calculate the difference between new and old quantity
            $differences = (int) $request->quantity - (int) $transferItem->quantity;
            
            // Determine if we're using warehouse or facility
            $isWarehouseTransfer = !empty($transfer->from_warehouse_id);
            
            // Check if there's enough inventory for the requested quantity increase
            if ($differences > 0) {
                if ($isWarehouseTransfer) {
                    // Warehouse inventory check
                    $inventoryQuery = InventoryItem::where('product_id', $transferItem->product_id)
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->where('batch_number', $transferItem->batch_number)
                        ->where('is_active', true);
                        
                    if ($transferItem->expire_date) {
                        $inventoryQuery->where('expiry_date', $transferItem->expire_date);
                    }
                    
                    $inventory = $inventoryQuery->first();
                        
                    if (!$inventory) {
                        return response()->json([
                            'message' => 'No inventory available for this product with the specified batch number and expiry date',
                            'quantity' => $transferItem->quantity
                        ], 500);
                    }
                    
                    if ($inventory->quantity < $differences) {
                        return response()->json([
                            'message' => 'Insufficient balance. Requested additional: ' . $differences . ', Available: ' . $inventory->quantity,
                            'quantity' => $transferItem->quantity
                        ], 500);
                    }
                } else {
                    // Facility inventory check
                    $inventoryQuery = FacilityInventory::where('product_id', $transferItem->product_id)
                        ->where('facility_id', $transfer->from_facility_id)
                        ->where('batch_number', $transferItem->batch_number)
                        ->where('is_active', true);
                        
                    if ($transferItem->expire_date) {
                        $inventoryQuery->where('expiry_date', $transferItem->expire_date);
                    }
                    
                    $inventory = $inventoryQuery->first();
                        
                    if (!$inventory) {
                        return response()->json([
                            'message' => 'No inventory available for this product with the specified batch number and expiry date',
                            'quantity' => $transferItem->quantity
                        ], 500);
                    }
                    
                    if ($inventory->quantity < $differences) {
                        return response()->json([
                            'message' => 'Insufficient balance. Requested additional: ' . $differences . ', Available: ' . $inventory->quantity,
                            'quantity' => $transferItem->quantity
                        ], 500);
                    }
                }
            }
            
            // Update the inventory if quantity has changed
            if ($differences != 0) {
                if ($isWarehouseTransfer) {
                    // Update warehouse inventory
                    $inventoryQuery = InventoryItem::where('product_id', $transferItem->product_id)
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->where('batch_number', $transferItem->batch_number);
                        
                    if ($transferItem->expire_date) {
                        $inventoryQuery->where('expiry_date', $transferItem->expire_date);
                    }
                    
                    $inventory = $inventoryQuery->first();
                    
                    if ($inventory) {
                        // Update existing inventory
                        if ($differences > 0) {
                            // Increasing transfer quantity means decreasing warehouse inventory
                            $inventory->quantity = $inventory->quantity - $differences;
                        } else {
                            // Decreasing transfer quantity means increasing warehouse inventory
                            $inventory->quantity = $inventory->quantity + abs($differences);
                        }
                        
                        // Mark as inactive if quantity is zero
                        if ($inventory->quantity <= 0) {
                            $inventory->is_active = false;
                        }
                        
                        $inventory->save();
                    } else if ($differences < 0) {
                        // Create new inventory record if we're returning items to the warehouse
                        $inventory = Inventory::create([
                            'product_id' => $transferItem->product_id
                        ]);
                        $inventory->items()->create([
                            'product_id' => $transferItem->product_id,
                            'batch_number' => $transferItem->batch_number,
                            'expiry_date' => $transferItem->expire_date,
                            'barcode' => $transferItem->barcode,
                            'uom' => $transferItem->uom,
                            'unit_cost' => $transferItem->unit_cost,
                            'total_cost' => $transferItem->unit_cost * $transferItem->quantity,
                            'quantity' => $transferItem->quantity,
                        ]);
                    }
                } else {
                    // Update facility inventory
                    $inventoryQuery = FacilityInventory::where('product_id', $transferItem->product_id)
                        ->where('facility_id', $transfer->from_facility_id)
                        ->where('batch_number', $transferItem->batch_number);
                        
                    if ($transferItem->expire_date) {
                        $inventoryQuery->where('expiry_date', $transferItem->expire_date);
                    }
                    
                    $inventory = $inventoryQuery->first();
                    
                    if ($inventory) {
                        // Update existing inventory
                        if ($differences > 0) {
                            // Increasing transfer quantity means decreasing facility inventory
                            $inventory->quantity = $inventory->quantity - $differences;
                        } else {
                            // Decreasing transfer quantity means increasing facility inventory
                            $inventory->quantity = $inventory->quantity + abs($differences);
                        }
                        
                        // Mark as inactive if quantity is zero
                        if ($inventory->quantity <= 0) {
                            $inventory->is_active = false;
                        }
                        
                        $inventory->save();
                    } else if ($differences < 0) {
                        // Create new inventory record if we're returning items to the facility
                        FacilityInventory::create([
                            'product_id' => $transferItem->product_id,
                            'facility_id' => $transfer->from_facility_id,
                            'quantity' => abs($differences),
                            'batch_number' => $transferItem->batch_number,
                            'expiry_date' => $transferItem->expire_date,
                            'is_active' => true
                        ]);
                    }
                }
            }
            
            // Update the transfer item quantity
            $transferItem->quantity = $request->quantity;
            $transferItem->save();
            
            // Update the transfer total quantity
            $transfer->quantity = $transfer->items()->sum('quantity');
            $transfer->save();
            
            DB::commit();
            return response()->json('Transfer item updated successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }


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

    // liquidate backorder
    public function transferLiquidate(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'backorder' => 'required',
                'quantity' => 'required|numeric|min:1',
                'note' => 'required|string',
                'attachments.*' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            
            $backorderData = json_decode($request->backorder, true);
            
            // Find the backorder record
            $backorder = FacilityBackorder::findOrFail($backorderData['id']);
            
            // Check if requested quantity is valid
            if ($request->quantity > $backorder->quantity) {
                return response()->json([
                    'error' => 'Requested quantity exceeds available backorder quantity'
                ], 422);
            }
            
            // Get the transfer item and transfer
            $transferItem = TransferItem::findOrFail($backorder->transfer_item_id);
            $transfer = Transfer::findOrFail($transferItem->transfer_id);

            // Generate a formatted note for history record
            $transferNumber = $transfer->transferID ?? ('ID: ' . $transfer->id);
            $historyNote = "Transfer ($transferNumber) - Liquidated";
            if ($request->note) {
                $historyNote .= " - {$request->note}";
            }
            
            
            // Handle file attachments if any
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    $fileName = 'liquidate_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('attachments/liquidations'), $fileName);
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => '/attachments/liquidations/' . $fileName,
                        'type' => $file->getClientMimeType(),
                        'size' => filesize(public_path('attachments/liquidations/' . $fileName)),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }
            
            // Create a new liquidation record
            $liquidate = Liquidate::create([
                'product_id' => $backorder->product_id,
                'transfer_id' => $transfer->id,
                'quantity' => $request->quantity,
                'status' => 'pending',
                'batch_number' => $transferItem->batch_number,
                'expire_date' => $transferItem->expire_date,
                'barcode' => $transferItem->barcode,
                'uom' => $transferItem->uom,
                'note' => $historyNote,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
                'liquidated_by' => auth()->id(),
                'liquidated_at' => now()
            ]);
            
            
            // Create backorder history record
            BackOrderHistory::create([
                'packing_list_id' => null,
                'transfer_id' => $transfer->id,
                'product_id' => $backorder->product_id,
                'quantity' => $request->quantity,
                'status' => 'Liquidated',
                'note' => $historyNote,
                'performed_by' => auth()->id()
            ]);
            
            // If all quantity is liquidated, delete the backorder
            // Otherwise, reduce the quantity
            if ($request->quantity >= $backorder->quantity) {
                $backorder->delete();
            } else {
                $backorder->quantity -= $request->quantity;
                $backorder->save();
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Backorder has been sent for liquidation',
                'liquidate' => $liquidate
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // dispose backorder
    public function transferDispose(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'backorder' => 'required',
                'quantity' => 'required|numeric|min:1',
                'note' => 'required|string',
                'attachments.*' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            
            $backorderData = json_decode($request->backorder, true);
            
            // Find the backorder record
            $backorder = FacilityBackorder::findOrFail($backorderData['id']);
            
            // Verify that this is a damaged backorder
            if ($backorder->type !== 'Damaged') {
                return response()->json([
                    'error' => 'Only damaged backorders can be disposed'
                ], 422);
            }
            
            // Check if requested quantity is valid
            if ($request->quantity > $backorder->quantity) {
                return response()->json([
                    'error' => 'Requested quantity exceeds available backorder quantity'
                ], 422);
            }
            
            // Get the transfer item and transfer
            $transferItem = TransferItem::findOrFail($backorder->transfer_item_id);
            $transfer = Transfer::findOrFail($transferItem->transfer_id);
            
            // Generate a formatted note that includes transfer ID, backorder type, and user note
            $transferNumber = $transfer->transferID ?? ('ID: ' . $transfer->id);
            $formattedNote = "Transfer ($transferNumber) - {$backorder->type}";
            if ($request->note) {
                $formattedNote .= " - {$request->note}";
            }
            
            // Handle file attachments if any
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    $fileName = 'disposal_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('attachments/disposals'), $fileName);
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => '/attachments/disposals/' . $fileName,
                        'type' => $file->getClientMimeType(),
                        'size' => filesize(public_path('attachments/liquidations/' . $fileName)),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }
            
            // Create a disposal record
            $disposal = Disposal::create([
                'product_id' => $backorder->product_id,
                'transfer_id' => $transfer->id,
                'quantity' => $request->quantity,
                'status' => 'pending',
                'batch_number' => $transferItem->batch_number,
                'expire_date' => $transferItem->expire_date,
                'barcode' => $transferItem->barcode,
                'uom' => $transferItem->uom,
                'note' => $formattedNote,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
                'disposed_by' => auth()->id(),
                'disposed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Create backorder history record
            BackOrderHistory::create([
                'packing_list_id' => null,
                'transfer_id' => $transfer->id,
                'product_id' => $backorder->product_id,
                'quantity' => $request->quantity,
                'status' => 'Disposed',
                'note' => $formattedNote,
                'performed_by' => auth()->id()
            ]);
            
            // If all quantity is disposed, delete the backorder
            // Otherwise, reduce the quantity
            if ($request->quantity >= $backorder->quantity) {
                $backorder->delete();
            } else {
                $backorder->quantity -= $request->quantity;
                $backorder->save();
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Backorder has been sent for disposal',
                'disposal' => $disposal
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // receive transfer
    public function receiveTransfer(Request $request)
    {
        try {
            $request->validate([
                'transfer_id' => 'required|exists:transfers,id',
                'status' => 'required|in:received',
                'items' => 'required|array',
            ]);
            $transfer = Transfer::findOrFail($request->transfer_id);
            if(!$transfer || $transfer->status !== 'dispatched') {
                return response()->json('Transfer must be dispatched to be received', 500);
            }

           if(!$transfer->to_warehouse_id && auth()->user()->can('permission:transfer_approve')) {
                return response()->json('You are not authorized to receive this transfer', 500);
            }


            // Check if all items.quantity and items.received_quantity are equal
            foreach ($request->items as $item) {
                $areEqual = (int) $item['quantity'] == array_sum(array_column($item['backorders'], 'quantity')) + (int) $item['received_quantity'];
                if(!$areEqual) {
                    return response()->json([
                        'id' => $item['id'],
                        'message' => 'There is mismatch in quantity'
                    ], 500);
                }
            }     
            
            // Process inventory changes
            $this->processInventoryChanges($transfer, $request->items);
            
            // Update transfer status
            $transfer->status = 'received';
            $transfer->save();

            return response()->json(['message' => 'Transfer received successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    private function processInventoryChanges(Transfer $transfer, $items)
    {
        foreach ($items as $item) {
            // First, find or create the parent Inventory record
            $inventory = Inventory::where([
                'product_id' => $item['product_id'],
            ])->first();

            if (!$inventory) {
                $inventory = Inventory::create([
                    'product_id' => $item['product_id'],
                    'quantity' => 0, // Will be updated when InventoryItem is created
                ]);
            }

            // Now find existing inventory item with same batch and warehouse
            $inventoryItem = InventoryItem::where([
                'inventory_id' => $inventory->id,
                'product_id' => $item['product_id'],
                'warehouse_id' => $transfer['to_warehouse_id'],
                'batch_number' => $item['batch_number'],
                'expiry_date' => $item['expire_date'],
            ])->first();

            if ($inventoryItem) {
                // Update existing inventory item
                $inventoryItem->increment('quantity', $item['received_quantity']);
                $inventoryItem->update([
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $inventoryItem->quantity * $item['unit_cost'],
                ]);
            } else {
                // Create new inventory item
                $inventoryItem = InventoryItem::create([
                    'inventory_id' => $inventory->id,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $transfer['to_warehouse_id'],
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expire_date'],
                    'barcode' => $item['barcode'],
                    'uom' => $item['uom'],
                    'unit_cost' => $item['unit_cost'],
                    'quantity' => $item['received_quantity'],
                    'total_cost' => $item['unit_cost'] * $item['received_quantity'],
                ]);
            }

            // Update parent inventory total quantity
            $inventory->quantity = $inventory->items()->sum('quantity');
            $inventory->save();

            // Create received quantity record
            ReceivedQuantity::create([
                'quantity' => $item['received_quantity'],
                'received_by' => auth()->id(),
                'received_at' => Carbon::now(),
                'product_id' => $item['product_id'],
                'transfer_id' => $transfer->id,
                'expiry_date' => $item['expire_date'],
                'warehouse_id' => $transfer['to_warehouse_id'],
                'unit_cost' => $item['unit_cost'],
                'total_cost' => $item['unit_cost'] * $item['received_quantity'],
                'uom' => $item['uom'],
                'barcode' => $item['barcode'],
                'batch_number' => $item['batch_number'],
            ]);
        }
    }

        /**
     * Handle back orders for transfer items
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function backorder(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'transfer_item_id' => 'required|exists:transfer_items,id',
                'backorders' => 'required|array',
                'received_quantity' => 'required|numeric|min:0',
                'backorders.*.quantity' => 'required|numeric|min:0',
                'backorders.*.type' => 'required|string',
                'backorders.*.notes' => 'nullable|string',
            ]);
            
            DB::beginTransaction();
            
            // Get the transfer item
            $transferItem = TransferItem::findOrFail($request->transfer_item_id);
            
            // Update the received quantity for the transfer item
            $transferItem->received_quantity = $request->received_quantity;
            $transferItem->save();
            
            // Process each backorder
            foreach ($request->backorders as $backorderData) {
                // Check if this is an existing backorder (has an ID)
                if (!empty($backorderData['id'])) {
                    // Update existing backorder
                    $backorder = FacilityBackorder::findOrFail($backorderData['id']);
                    $backorder->update([
                        'quantity' => $backorderData['quantity'],
                        'type' => $backorderData['type'],
                        'notes' => $backorderData['notes'] ?? null,
                        'updated_by' => auth()->id()
                    ]);
                } else {
                    // Create new backorder
                    FacilityBackorder::create([
                        'transfer_item_id' => $transferItem->id,
                        'product_id' => $transferItem->product_id,
                        'inventory_allocation_id' => $backorderData['inventory_allocation_id'],
                        'quantity' => $backorderData['quantity'],
                        'type' => $backorderData['type'],
                        'notes' => $backorderData['notes'] ?? null,
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id()
                    ]);
                }
            }
            
            DB::commit();
            return response()->json('Back orders have been recorded successfully', 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }
    }

    public function transferBackOrder(Request $request){
        $backorders = FacilityBackorder::whereHas('transferItem.transfer', function ($query) use ($request) {
            $query->whereHas('toWarehouse');
        })->with(['transferItem.transfer.toWarehouse', 'product','inventoryAllocation.product'])->get();
        
       return inertia('Transfer/BackOrder', [
           'backorders' => $backorders
       ]);

    }
    
}
