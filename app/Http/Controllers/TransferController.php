<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Facility;
use App\Models\FacilityInventory;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

            logger()->info($transfer);
            logger()->info($request->all());

            if($transfer->status == 'pending' && $request->status == 'approved'){
                $transfer->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
            }
            
            if($transfer->status == 'approved' && $request->status == 'in_process' && $transfer->from_warehouse_id == auth()->user()->warehouse_id){
                $transfer->update([
                    'status' => 'in_process',
                ]);
            }

            if($transfer->status == 'in_process' && $request->status == 'dispatched' && $transfer->from_warehouse_id == auth()->user()->warehouse_id){
                $transfer->update([
                    'status' => 'dispatched',
                    'dispatched_by' => auth()->id(),    
                    'dispatched_at' => now()
                ]);
            }
            
            DB::commit();
            
            // Return debug information along with success message
            return response()->json("Transfer status changed successfully", 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return response()->json($e->getMessage(), 500);
        }
    }

    public function index(Request $request)
    {
        // Start building the query
        $query = Transfer::with('fromWarehouse', 'toWarehouse', 'fromFacility','fromFacility', 'fromWarehouse', 'toFacility', 'items');
        
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
        
        // Filter by facility (supports multiple selections)
        if ($request->has('facility_id') && !empty($request->facility_id)) {
            $facilityIds = explode(',', $request->facility_id);
            $query->where(function($q) use ($facilityIds) {
                $q->whereIn('from_facility_id', $facilityIds)
                  ->orWhereIn('to_facility_id', $facilityIds);
            });
        }
        
        // Filter by warehouse (supports multiple selections)
        if ($request->has('warehouse_id') && !empty($request->warehouse_id)) {
            $warehouseIds = explode(',', $request->warehouse_id);
            $query->where(function($q) use ($warehouseIds) {
                $q->whereIn('from_warehouse_id', $warehouseIds)
                  ->orWhereIn('to_warehouse_id', $warehouseIds);
            });
        }
        
        // Filter by location (supports multiple selections)
        if ($request->has('location_id') && !empty($request->location_id)) {
            $locationIds = explode(',', $request->location_id);
            $query->whereHas('items', function($q) use ($locationIds) {
                $q->whereIn('location_id', $locationIds);
            });
        }
        
        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }
        
        // Execute the query
        $transfers = $query->get();
        
        // Get all transfers for statistics (unfiltered)
        $allTransfers = Transfer::all();
        $total = $allTransfers->count();
        $approvedCount = $allTransfers->whereIn('status', ['approved', 'in_process', 'dispatched', 'transferred'])->count();
        $inProcessCount = $allTransfers->whereIn('status', ['in_process', 'dispatched'])->count();
        $transferredCount = $allTransfers->where('status', 'transferred')->count();
        $rejectedCount = $allTransfers->where('status', 'rejected')->count();
        $pendingCount = $allTransfers->where('status', 'pending')->count();
        
        $statistics = [
            'approved' => [
                'count' => $approvedCount,
                'percentage' => $total > 0 ? round(($approvedCount / $total) * 100) : 0,
                'stages' => ['approved', 'in_process', 'dispatched', 'transferred']
            ],
            'pending' => [
                'count' => $pendingCount,
                'percentage' => $total > 0 ? round(($pendingCount / $total) * 100) : 0,
                'stages' => ['pending']
            ],
            'in_process' => [
                'count' => $inProcessCount,
                'percentage' => $total > 0 ? round(($inProcessCount / $total) * 100) : 0,
                'stages' => ['in_process', 'dispatched']
            ],
            'transferred' => [
                'count' => $transferredCount,
                'percentage' => $total > 0 ? round(($transferredCount / $total) * 100) : 0,
                'stages' => ['transferred']
            ],
            'rejected' => [
                'count' => $rejectedCount,
                'percentage' => $total > 0 ? round(($rejectedCount / $total) * 100) : 0,
                'stages' => ['rejected']
            ]
        ];
        
        // Get data for filter dropdowns
        $facilities = Facility::select('id', 'name')->orderBy('name')->get();
        $warehouses = Warehouse::select('id', 'name')->orderBy('name')->get();
        $locations = DB::table('locations')->select('id', 'location')->orderBy('location')->get();

        return inertia('Transfer/Index', [
            'transfers' => $transfers,
            'statistics' => $statistics,
            'facilities' => $facilities,
            'warehouses' => $warehouses,
            'locations' => $locations,
            'filters' => $request->only(['search', 'facility_id', 'warehouse_id', 'location_id', 'date_from', 'date_to', 'tab'])
        ]);
    }

    public function store(Request $request)
    {
        logger()->info($request->all());
        DB::beginTransaction();
        try {
            $request->validate([
                'source_type' => 'required|in:warehouse,facility',
                'source_id' => 'required|integer',
                'destination_type' => 'required|in:warehouse,facility',
                'destination_id' => 'required|integer',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.batch_number' => 'required|string',
                'items.*.barcode' => 'nullable|string',
                'items.*.expiry_date' => 'nullable|date',
                'items.*.uom' => 'nullable|string',
                'notes' => 'nullable|string|max:500'
            ]);
            
            // Prepare transfer data
            $transferData = [
                'transferID' => str_pad(Transfer::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'from_warehouse_id' => $request->source_type === 'warehouse' ? $request->source_id : null,
                'from_facility_id' => $request->source_type === 'facility' ? $request->source_id : null,
                'to_warehouse_id' => $request->destination_type === 'warehouse' ? $request->destination_id : null,
                'to_facility_id' => $request->destination_type === 'facility' ? $request->destination_id : null,
                'created_by' => auth()->id(),
                'quantity' => collect($request->items)->sum('quantity'),
                'transfer_date' => now(),
                'status' => 'pending',
                'note' => $request->notes
            ];
            
            // Create the transfer record
            $transfer = Transfer::create($transferData);
            
            // Process each inventory item
            foreach ($request->items as $item) {
                // Validate inventory exists and has sufficient quantity
                if ($request->source_type === 'warehouse') {
                    $inventory = Inventory::where('warehouse_id', $request->source_id)
                        ->where('id', $item['id'])
                        ->first();
                        
                    if (!$inventory) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Inventory item not found with ID: ' . $item['id']
                        ], 404);
                    }
                } else {
                    $inventory = FacilityInventory::where('facility_id', $request->source_id)
                        ->where('id', $item['id'])
                        ->first();
                        
                    if (!$inventory) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Insufficient stock hand in the inventory for ID: ' . $item['id']
                        ], 404);
                    }
                }
                
                if ($item['quantity'] > $inventory->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Transfer quantity cannot exceed available quantity for product: ' . 
                            ($inventory->product->name ?? 'Unknown')
                    ], 400);
                }
                
                // Create transfer item record
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'barcode' => $item['barcode'] ?? '',
                    'uom' => $item['uom'] ?? '',
                    'quantity' => $item['quantity'],
                    'batch_number' => $item['batch_number'],
                    'expire_date' => $item['expiry_date'] ?? null,
                ]);
                
                // Update inventory quantity
                $inventory->decrement('quantity', $item['quantity']);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Transfer created successfully',
                'transfer_id' => $transfer->transferID,
                'transfer' => $transfer->load('fromWarehouse', 'toWarehouse', 'fromFacility', 'toFacility')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to create transfer: ' . $e->getMessage()], 500);
        }
    }

    public function show($id){
        $transfer = Transfer::where('id', $id)->with([
            'items.product', 
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
        
        return inertia('Transfer/Create', [
            'warehouses' => $warehouses,
            'facilities' => $facilities,
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
                return response()->json([
                    'message' => 'Cannot delete items from a transfer that is not in pending or draft status'
                ], 403);
            }
            
            // Delete the transfer item
            $transferItem->delete();
            
            return response()->json([
                'message' => 'Transfer item deleted successfully'
            ]);
        } catch (\Exception $e) {
            logger()->error('Error deleting transfer item: ' . $e->getMessage(), [
                'item_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to delete transfer item: ' . $e->getMessage()
            ], 500);
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
                $result = DB::table('inventories as i')
                    ->join('products as p', 'i.product_id', '=', 'p.id')
                    ->leftJoin('warehouses as w', 'i.warehouse_id', '=', 'w.id')
                    ->where('i.warehouse_id', $request->source_id)
                    ->where('i.quantity', '>', 0)
                    ->whereNotNull('p.id') // Ensure product exists
                    ->select([
                        'i.id',
                        'i.product_id',
                        'p.name',
                        'i.batch_number',
                        'i.quantity',
                        'i.expiry_date',
                        'i.warehouse_id',
                        'w.name as warehouse_name',
                        // Add JSON for product object
                        DB::raw("JSON_OBJECT('id', p.id, 'name', p.name) as product_json")
                    ])
                    ->get()
                    ->map(function($item) {
                        // Convert product_json to actual product array
                        $item->product = json_decode($item->product_json);
                        unset($item->product_json);
                        
                        // Add missing fields with default values
                        $item->barcode = ''; // Default barcode since column doesn't exist
                        $item->batch_number = $item->batch_number ?? '';
                        $item->id = $item->id ?? 'Unknown';
                        $item->product_id = $item->product_id ?? 'Unknown';
                        $item->warehouse_name = $item->warehouse_name ?? 'Unknown';
                        $item->expiry_date = $item->expiry_date ?? 'Unknown';
                        $item->uom = $item->uom ?? 'Unknown';
                        
                        return $item;
                    });
            } else {
                // Get facility inventories directly with DB query
                $result = DB::table('facility_inventories as fi')
                    ->join('products as p', 'fi.product_id', '=', 'p.id')
                    ->leftJoin('facilities as f', 'fi.facility_id', '=', 'f.id')
                    ->where('fi.facility_id', $request->source_id)
                    ->where('fi.quantity', '>', 0)
                    ->whereNotNull('p.id') // Ensure product exists
                    ->select([
                        'fi.id',
                        'fi.product_id',
                        'p.name',
                        'fi.batch_number',
                        'fi.quantity',
                        'fi.expiry_date',
                        'fi.facility_id',
                        'f.name as facility_name',
                        // Add JSON for product object
                        DB::raw("JSON_OBJECT('id', p.id, 'name', p.name) as product_json")
                    ])
                    ->get()
                    ->map(function($item) {
                        // Convert product_json to actual product array
                        $item->product = json_decode($item->product_json);
                        unset($item->product_json);
                        
                        // Add missing fields with default values
                        $item->uom = 'N/A'; // Default UoM since column doesn't exist
                        $item->barcode = ''; // Default barcode since column doesn't exist
                        $item->batch_number = $item->batch_number ?? '';
                        $item->facility_name = $item->facility_name ?? 'Unknown';
                        $item->product_id = $item->product_id ?? 'Unknown';
                        $item->product = $item->product ?? 'Unknown';
                        $item->quantity = $item->quantity ?? 'Unknown';
                        $item->available_quantity = $item->available_quantity ?? 'Unknown';
                        $item->expire_date = $item->expire_date ?? 'Unknown';
                        $item->uom = $item->uom ?? 'Unknown';
                        $item->id = $item->id ?? 'Unknown';
                        return $item;
                    });
            }
            
            // Log the count of items found
            $count = $result->count();
            logger()->info("Found {$count} inventory items for {$request->source_type} ID: {$request->source_id}");
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            logger()->error('Error in getInventories: ' . $e->getMessage(), [
                'source_type' => $request->source_type,
                'source_id' => $request->source_id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
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
                    $inventoryQuery = Inventory::where('product_id', $transferItem->product_id)
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
                    $inventoryQuery = Inventory::where('product_id', $transferItem->product_id)
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
                        Inventory::create([
                            'product_id' => $transferItem->product_id,
                            'warehouse_id' => $transfer->from_warehouse_id,
                            'quantity' => abs($differences),
                            'batch_number' => $transferItem->batch_number,
                            'expiry_date' => $transferItem->expire_date,
                            'is_active' => true
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

            if($transfer->to_facility_id != auth()->user()->facility_id) {
                return response()->json('You are not authorized to receive this transfer', 500);
            }

            // Check if all items.quantity and items.received_quantity are equal
            foreach ($request->items as $item) {
                $areEqual = (int) $item['quantity'] == array_sum(array_column($item['backorders'], 'quantity')) + (int) $item['received_quantity'];
                logger()->info('Item ID: ' . $item['id'] . ', Equal check: ' . ($areEqual ? 'true' : 'false'));
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
}
