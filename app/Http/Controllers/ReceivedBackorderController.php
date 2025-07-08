<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReceivedBackorder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Facility;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\ReceivedQuantity;
use App\Models\PackingListItem;
use App\Http\Resources\ReceivedBackorderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\FacilityInventory;
use App\Models\FacilityInventoryItem;
use App\Models\Transfer;
use App\Models\FacilityInventoryMovement;

class ReceivedBackorderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReceivedBackorder::with(['product', 'receivedBy', 'reviewedBy', 'approvedBy', 'rejectedBy', 'backOrder']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('received_backorder_number', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%')
                  ->orWhere('batch_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('product', function ($productQuery) use ($request) {
                      $productQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('backOrder', function ($backOrderQuery) use ($request) {
                      $backOrderQuery->where('back_order_number', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $query->where('received_at', '>=', $request->date_from);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->where('received_at', '<=', $request->date_to)
                  ->where('received_at', '>=', $request->date_from);
        }

        // Filter by warehouse
        if ($request->filled('warehouse') && $request->warehouse != 'All Warehouses') {
            $query->where('reported_by', $request->warehouse);
        }

        // Filter by facility
        if ($request->filled('facility') && $request->facility != 'All Facilities') {
            $query->where('reported_by', $request->facility);
        }

        $receivedBackorders = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $receivedBackorders->setPath(url()->current()); // Force Laravel to use full URLs

        // Get statistics
        $stats = [
            'total' => ReceivedBackorder::count(),
            'pending' => ReceivedBackorder::where('status', 'pending')->count(),
            'reviewed' => ReceivedBackorder::where('status', 'reviewed')->count(),
            'approved' => ReceivedBackorder::where('status', 'approved')->count(),
            'rejected' => ReceivedBackorder::where('status', 'rejected')->count(),
            'total_quantity' => ReceivedBackorder::sum('quantity'),
            'total_cost' => ReceivedBackorder::sum('total_cost'),
        ];

        // Get warehouses and facilities for filters
        $warehouses = Warehouse::pluck('name')->toArray();
        $facilities = Facility::pluck('name')->toArray();

        return Inertia::render('Supplies/ReceivedBackorder', [
            'receivedBackorders' => ReceivedBackorderResource::collection($receivedBackorders),
            'filters' => $request->only('search', 'status', 'type', 'date_from', 'date_to', 'per_page', 'warehouse', 'facility'),
            'stats' => $stats,
            'warehouses' => $warehouses,
            'facilities' => $facilities,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::select('id', 'name', 'productID')->get();
        $warehouses = Warehouse::select('id', 'name')->get();
        $locations = Location::select('id', 'location')->get();
        $facilities = Facility::select('id', 'name')->get();

        return Inertia::render('Supplies/ReceivedBackorder/Create', [
            'products' => $products,
            'warehouses' => $warehouses,
            'locations' => $locations,
            'facilities' => $facilities,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'barcode' => 'nullable|string|max:255',
            'expire_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:255',
            'uom' => 'nullable|string|max:50',
            'received_at' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|string|in:backorder,return,damaged,expired',
            'location' => 'nullable|string|max:255',
            'facility' => 'nullable|string|max:255',
            'warehouse' => 'nullable|string|max:255',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            // Additional fields for back order integration
            'back_order_id' => 'nullable|string|max:255',
            'packing_list_id' => 'nullable|string|max:255',
            'packing_list_number' => 'nullable|string|max:255',
            'purchase_order_id' => 'nullable|string|max:255',
            'purchase_order_number' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total cost if not provided
            if (!isset($validated['total_cost']) && isset($validated['unit_cost'])) {
                $validated['total_cost'] = $validated['unit_cost'] * $validated['quantity'];
            }

            $validated['received_by'] = auth()->id();
            $validated['status'] = 'pending';

            // Handle file uploads
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('received-backorders', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
            }
            $validated['attachments'] = $attachments;

            $receivedBackorder = ReceivedBackorder::create($validated);

            DB::commit();

            return redirect()->route('supplies.received-backorder.index')
                ->with('success', 'Received backorder created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create received backorder: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ReceivedBackorder $receivedBackorder)
    {
        $receivedBackorder->load(['product', 'receivedBy', 'reviewedBy', 'approvedBy', 'rejectedBy']);

        return Inertia::render('Supplies/ReceivedBackorder/Show', [
            'receivedBackorder' => new ReceivedBackorderResource($receivedBackorder),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReceivedBackorder $receivedBackorder)
    {
        $products = Product::select('id', 'name', 'productID')->get();
        $warehouses = Warehouse::select('id', 'name')->get();
        $locations = Location::select('id', 'location')->get();
        $facilities = Facility::select('id', 'name')->get();

        return Inertia::render('Supplies/ReceivedBackorder/Edit', [
            'receivedBackorder' => new ReceivedBackorderResource($receivedBackorder),
            'products' => $products,
            'warehouses' => $warehouses,
            'locations' => $locations,
            'facilities' => $facilities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReceivedBackorder $receivedBackorder)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'barcode' => 'nullable|string|max:255',
            'expire_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:255',
            'uom' => 'nullable|string|max:50',
            'received_at' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|string|in:backorder,return,damaged,expired',
            'location' => 'nullable|string|max:255',
            'facility' => 'nullable|string|max:255',
            'warehouse' => 'nullable|string|max:255',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total cost if not provided
            if (!isset($validated['total_cost']) && isset($validated['unit_cost'])) {
                $validated['total_cost'] = $validated['unit_cost'] * $validated['quantity'];
            }

            // Handle file uploads
            $attachments = $receivedBackorder->attachments ?? [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('received-backorders', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
            }
            $validated['attachments'] = $attachments;

            $receivedBackorder->update($validated);

            DB::commit();

            return redirect()->route('supplies.received-backorder.index')
                ->with('success', 'Received backorder updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update received backorder: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReceivedBackorder $receivedBackorder)
    {
        try {
            DB::beginTransaction();

            // Delete attachments from storage
            if ($receivedBackorder->attachments) {
                foreach ($receivedBackorder->attachments as $attachment) {
                    if (isset($attachment['path'])) {
                        Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }

            $receivedBackorder->delete();

            DB::commit();

            return redirect()->route('supplies.received-backorder.index')
                ->with('success', 'Received backorder deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete received backorder: ' . $e->getMessage());
        }
    }

    /**
     * Review a received backorder
     */
    public function review(Request $request, ReceivedBackorder $receivedBackorder)
    {
        $validated = $request->validate([
            'note' => 'nullable|string',
        ]);

        $receivedBackorder->update([
            'status' => 'reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'note' => $validated['note'] ?? $receivedBackorder->note,
        ]);

        return back()->with('success', 'Received backorder reviewed successfully.');
    }

    /**
     * Approve a received backorder
     */
    public function approve(Request $request, ReceivedBackorder $receivedBackorder)
    {
        $validated = $request->validate([
            'note' => 'nullable|string',
        ]);

        try {
            // Test database connection
            logger()->info('Testing database connection', [
                'connection' => DB::connection()->getName(),
                'database' => DB::connection()->getDatabaseName()
            ]);

            DB::beginTransaction();

            logger()->info('Starting backorder approval process', [
                'received_backorder_id' => $receivedBackorder->id,
                'order_id' => $receivedBackorder->order_id,
                'transfer_id' => $receivedBackorder->transfer_id,
                'product_id' => $receivedBackorder->product_id,
                'quantity' => $receivedBackorder->quantity,
                'current_status' => $receivedBackorder->status
            ]);

            // Update the received back order status
            $receivedBackorder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'note' => $validated['note'] ?? $receivedBackorder->note,
            ]);

            // Determine if this is an order or transfer and handle inventory accordingly
            if ($receivedBackorder->order_id) {
                logger()->info('Processing as order inventory');
                // Handle ORDER - Use FacilityInventory and FacilityInventoryItem
                $this->handleOrderInventory($receivedBackorder);
                // Create facility inventory movement record for orders
                $this->createFacilityInventoryMovement($receivedBackorder, 'order');
            } elseif ($receivedBackorder->transfer_id) {
                logger()->info('Processing as transfer inventory');
                // Handle TRANSFER - Determine if warehouse or facility and use appropriate inventory
                $this->handleTransferInventory($receivedBackorder);
                // Create appropriate movement record based on transfer destination
                $this->createMovementRecord($receivedBackorder);
            } else {
                logger()->info('Processing as warehouse inventory (fallback)');
                // Fallback to original warehouse inventory logic
                $this->handleWarehouseInventory($receivedBackorder);
                // Create received quantity record for warehouse operations
                $this->createReceivedQuantityRecord($receivedBackorder);
            }

            // Update the packing list quantity if packing_list_id exists
            if ($receivedBackorder->packing_list_id) {
                $packingList = PackingListItem::where('packing_list_id', $receivedBackorder->packing_list_id)
                    ->where('product_id', $receivedBackorder->product_id)
                    ->first();
                
                if ($packingList) {
                    $packingList->increment('quantity', $receivedBackorder->quantity);
                    $packingList->save();
                    logger()->info('Updated packing list item', ['packing_list_item_id' => $packingList->id]);
                }
            }

            // Final verification before commit
            logger()->info('About to commit transaction', [
                'received_backorder_id' => $receivedBackorder->id,
                'final_status' => $receivedBackorder->status
            ]);

            // Verify facility inventory before commit
            if ($receivedBackorder->order_id) {
                $order = Order::find($receivedBackorder->order_id);
                if ($order) {
                    $facilityInventory = FacilityInventory::where('product_id', $receivedBackorder->product_id)
                        ->where('facility_id', $order->facility_id)
                        ->first();
                    
                    if ($facilityInventory) {
                        logger()->info('Facility inventory before commit', [
                            'facility_inventory_id' => $facilityInventory->id,
                            'quantity' => $facilityInventory->quantity
                        ]);
                    } else {
                        logger()->warning('Facility inventory not found before commit');
                    }
                }
            }

            DB::commit();
            logger()->info('Successfully approved received back order', ['received_backorder_id' => $receivedBackorder->id]);

            // Verify facility inventory after commit
            if ($receivedBackorder->order_id) {
                $order = Order::find($receivedBackorder->order_id);
                if ($order) {
                    $facilityInventory = FacilityInventory::where('product_id', $receivedBackorder->product_id)
                        ->where('facility_id', $order->facility_id)
                        ->first();
                    
                    if ($facilityInventory) {
                        logger()->info('Facility inventory after commit', [
                            'facility_inventory_id' => $facilityInventory->id,
                            'quantity' => $facilityInventory->quantity
                        ]);
                    } else {
                        logger()->warning('Facility inventory not found after commit');
                    }
                }
            }

            return back()->with('success', 'Received backorder approved successfully and inventory updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to approve received back order', [
                'received_backorder_id' => $receivedBackorder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to approve received backorder: ' . $e->getMessage());
        }
    }

    /**
     * Handle inventory for orders (facility inventory)
     */
    private function handleOrderInventory($receivedBackorder)
    {
        try {
            // Get facility_id from the order
            $order = Order::find($receivedBackorder->order_id);
            if (!$order) {
                throw new \Exception('Order not found for received backorder');
            }

            $facilityId = $order->facility_id;
            logger()->info('Processing order inventory', [
                'received_backorder_id' => $receivedBackorder->id,
                'order_id' => $receivedBackorder->order_id,
                'facility_id' => $facilityId,
                'product_id' => $receivedBackorder->product_id,
                'quantity' => $receivedBackorder->quantity
            ]);

            // Update or create facility inventory
            logger()->info('Attempting to find or create facility inventory', [
                'product_id' => $receivedBackorder->product_id,
                'facility_id' => $facilityId
            ]);

            $facilityInventory = FacilityInventory::firstOrCreate([
                'product_id' => $receivedBackorder->product_id,
                'facility_id' => $facilityId,
            ], [
                'quantity' => 0,
            ]);

            logger()->info('Facility inventory found/created', [
                'facility_inventory_id' => $facilityInventory->id,
                'was_created' => $facilityInventory->wasRecentlyCreated,
                'current_quantity' => $facilityInventory->quantity
            ]);

            $oldQuantity = $facilityInventory->quantity;
            
            // Use direct update instead of increment to ensure it works
            $newQuantity = $oldQuantity + $receivedBackorder->quantity;
            $facilityInventory->update(['quantity' => $newQuantity]);
            
            logger()->info('Updated facility inventory quantity', [
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'added_quantity' => $receivedBackorder->quantity
            ]);
            
            logger()->info('Updated facility inventory', [
                'facility_inventory_id' => $facilityInventory->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $facilityInventory->quantity,
                'added_quantity' => $receivedBackorder->quantity
            ]);

            // Update or create facility inventory item
            $facilityInventoryItem = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                ->where('batch_number', $receivedBackorder->batch_number)
                ->first();

            if ($facilityInventoryItem) {
                $oldItemQuantity = $facilityInventoryItem->quantity;
                $facilityInventoryItem->increment('quantity', $receivedBackorder->quantity);
                $facilityInventoryItem->save();
                logger()->info('Updated existing facility inventory item', [
                    'facility_inventory_item_id' => $facilityInventoryItem->id,
                    'old_quantity' => $oldItemQuantity,
                    'new_quantity' => $facilityInventoryItem->quantity,
                    'added_quantity' => $receivedBackorder->quantity
                ]);
            } else {
                $facilityInventoryItem = FacilityInventoryItem::create([
                    'facility_inventory_id' => $facilityInventory->id,
                    'product_id' => $receivedBackorder->product_id,
                    'quantity' => $receivedBackorder->quantity,
                    'batch_number' => $receivedBackorder->batch_number,
                    'expiry_date' => $receivedBackorder->expire_date,
                    'barcode' => $receivedBackorder->barcode,
                    'uom' => $receivedBackorder->uom,
                    'unit_cost' => $receivedBackorder->unit_cost,
                    'total_cost' => $receivedBackorder->total_cost,
                    'notes' => 'Received from backorder'
                ]);
                logger()->info('Created new facility inventory item', [
                    'facility_inventory_item_id' => $facilityInventoryItem->id,
                    'quantity' => $facilityInventoryItem->quantity
                ]);
            }

            // Verify the update was successful
            $facilityInventory->refresh();
            logger()->info('Final facility inventory verification', [
                'facility_inventory_id' => $facilityInventory->id,
                'final_quantity' => $facilityInventory->quantity,
                'expected_quantity' => $oldQuantity + $receivedBackorder->quantity
            ]);

        } catch (\Exception $e) {
            logger()->error('Error in handleOrderInventory', [
                'received_backorder_id' => $receivedBackorder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle inventory for transfers (warehouse or facility inventory)
     */
    private function handleTransferInventory($receivedBackorder)
    {
        // Get transfer details
        $transfer = Transfer::find($receivedBackorder->transfer_id);
        if (!$transfer) {
            throw new \Exception('Transfer not found for received backorder');
        }

        // Determine if transfer is to warehouse or facility
        if ($transfer->to_warehouse_id) {
            // Transfer to warehouse - use warehouse inventory
            $this->handleWarehouseTransferInventory($receivedBackorder, $transfer);
        } elseif ($transfer->to_facility_id) {
            // Transfer to facility - use facility inventory
            $this->handleFacilityTransferInventory($receivedBackorder, $transfer);
        } else {
            throw new \Exception('Transfer has no destination warehouse or facility');
        }
    }

    /**
     * Handle warehouse transfer inventory
     */
    private function handleWarehouseTransferInventory($receivedBackorder, $transfer)
    {
        $warehouseId = $transfer->to_warehouse_id;

        // Update or create warehouse inventory
        $inventory = Inventory::firstOrCreate([
            'product_id' => $receivedBackorder->product_id,
        ], [
            'quantity' => 0,
        ]);

        $inventory->increment('quantity', $receivedBackorder->quantity);
        $inventory->save();

        // Update or create warehouse inventory item
        $inventoryItem = InventoryItem::where('inventory_id', $inventory->id)
            ->where('batch_number', $receivedBackorder->batch_number)
            ->first();

        if ($inventoryItem) {
            $inventoryItem->increment('quantity', $receivedBackorder->quantity);
            $inventoryItem->save();
            logger()->info('Updated existing warehouse inventory item', ['inventory_item_id' => $inventoryItem->id]);
        } else {
            $inventoryItem = InventoryItem::create([
                'inventory_id' => $inventory->id,
                'quantity' => $receivedBackorder->quantity,
                'batch_number' => $receivedBackorder->batch_number,
                'expiry_date' => $receivedBackorder->expire_date,
                'barcode' => $receivedBackorder->barcode,
                'warehouse_id' => $warehouseId,
                'location' => $receivedBackorder->location,
                'unit_cost' => $receivedBackorder->unit_cost,
                'total_cost' => $receivedBackorder->total_cost,
                'uom' => $receivedBackorder->uom,
                'status' => 'active'
            ]);
            logger()->info('Created new warehouse inventory item', ['inventory_item_id' => $inventoryItem->id]);
        }
    }

    /**
     * Handle facility transfer inventory
     */
    private function handleFacilityTransferInventory($receivedBackorder, $transfer)
    {
        $facilityId = $transfer->to_facility_id;

        // Update or create facility inventory
        $facilityInventory = FacilityInventory::firstOrCreate([
            'product_id' => $receivedBackorder->product_id,
            'facility_id' => $facilityId,
        ], [
            'quantity' => 0,
        ]);

        $facilityInventory->increment('quantity', $receivedBackorder->quantity);
        $facilityInventory->save();

        // Update or create facility inventory item
        $facilityInventoryItem = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
            ->where('batch_number', $receivedBackorder->batch_number)
            ->first();

        if ($facilityInventoryItem) {
            $facilityInventoryItem->increment('quantity', $receivedBackorder->quantity);
            $facilityInventoryItem->save();
            logger()->info('Updated existing facility inventory item', ['facility_inventory_item_id' => $facilityInventoryItem->id]);
        } else {
            $facilityInventoryItem = FacilityInventoryItem::create([
                'facility_inventory_id' => $facilityInventory->id,
                'product_id' => $receivedBackorder->product_id,
                'quantity' => $receivedBackorder->quantity,
                'batch_number' => $receivedBackorder->batch_number,
                'expiry_date' => $receivedBackorder->expire_date,
                'barcode' => $receivedBackorder->barcode,
                'uom' => $receivedBackorder->uom,
                'unit_cost' => $receivedBackorder->unit_cost,
                'total_cost' => $receivedBackorder->total_cost,
                'notes' => 'Received from transfer'
            ]);
            logger()->info('Created new facility inventory item', ['facility_inventory_item_id' => $facilityInventoryItem->id]);
        }
    }

    /**
     * Handle warehouse inventory (fallback method)
     */
    private function handleWarehouseInventory($receivedBackorder)
    {
        // Update inventory with the received items
        $inventory = InventoryItem::where('product_id', $receivedBackorder->product_id)
            ->where('batch_number', $receivedBackorder->batch_number)
            ->first();
        
        if ($inventory) {
            $inventory->increment('quantity', $receivedBackorder->quantity);
            $inventory->save();
            logger()->info('Updated existing inventory item', ['inventory_id' => $inventory->id]);
        } else {
            // Create a new inventory record if it doesn't exist
            $mainInventory = Inventory::firstOrCreate([
                'product_id' => $receivedBackorder->product_id,
            ], [
                'quantity' => 0,
            ]);
            $mainInventory->increment('quantity', $receivedBackorder->quantity);
            $mainInventory->save();
            
            // Get warehouse_id from packing list item if available
            $warehouseId = null;
            if ($receivedBackorder->packing_list_id) {
                $packingListItem = PackingListItem::where('packing_list_id', $receivedBackorder->packing_list_id)
                    ->where('product_id', $receivedBackorder->product_id)
                    ->first();
                if ($packingListItem) {
                    $warehouseId = $packingListItem->warehouse_id;
                }
            }
            
            // Fallback to first warehouse if not found in packing list
            if (!$warehouseId) {
                $defaultWarehouse = Warehouse::first();
                $warehouseId = $defaultWarehouse ? $defaultWarehouse->id : 1;
            }
            
            $inventory = InventoryItem::create([
                'inventory_id' => $mainInventory->id,
                'quantity' => $receivedBackorder->quantity,
                'batch_number' => $receivedBackorder->batch_number,
                'expiry_date' => $receivedBackorder->expire_date,
                'barcode' => $receivedBackorder->barcode,
                'warehouse_id' => $warehouseId,
                'location' => $receivedBackorder->location,
                'unit_cost' => $receivedBackorder->unit_cost,
                'total_cost' => $receivedBackorder->total_cost,
                'uom' => $receivedBackorder->uom,
                'status' => 'active'
            ]);
            logger()->info('Created new inventory item', ['inventory_id' => $inventory->id]);
        }
    }

    /**
     * Reject a received backorder
     */
    public function reject(Request $request, ReceivedBackorder $receivedBackorder)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $receivedBackorder->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Received backorder rejected successfully.');
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment(Request $request, ReceivedBackorder $receivedBackorder)
    {
        $validated = $request->validate([
            'attachment_index' => 'required|integer|min:0',
        ]);

        $attachments = $receivedBackorder->attachments ?? [];
        $index = $validated['attachment_index'];

        if (isset($attachments[$index])) {
            $attachment = $attachments[$index];
            
            // Delete file from storage
            if (isset($attachment['path'])) {
                Storage::disk('public')->delete($attachment['path']);
            }

            // Remove from array
            unset($attachments[$index]);
            $attachments = array_values($attachments); // Re-index array

            $receivedBackorder->update(['attachments' => $attachments]);

            return back()->with('success', 'Attachment deleted successfully.');
        }

        return back()->with('error', 'Attachment not found.');
    }

    /**
     * Create facility inventory movement record for orders
     */
    private function createFacilityInventoryMovement($receivedBackorder, $type)
    {
        // Get facility_id from the order
        $order = Order::find($receivedBackorder->order_id);
        if (!$order) {
            throw new \Exception('Order not found for received backorder');
        }

        $facilityId = $order->facility_id;

        // Create facility inventory movement record
        $movementData = [
            'facility_id' => $facilityId,
            'product_id' => $receivedBackorder->product_id,
            'source_type' => $type,
            'source_id' => $receivedBackorder->order_id,
            'source_item_id' => null, // Could be order_item_id if available
            'facility_received_quantity' => $receivedBackorder->quantity,
            'batch_number' => $receivedBackorder->batch_number,
            'expiry_date' => $receivedBackorder->expire_date,
            'barcode' => $receivedBackorder->barcode,
            'uom' => $receivedBackorder->uom,
            'movement_date' => now(),
            'reference_number' => $receivedBackorder->received_backorder_number,
            'notes' => 'Received from backorder approval',
        ];

        $facilityMovement = FacilityInventoryMovement::recordFacilityReceived($movementData);
        logger()->info('Created facility inventory movement record', ['facility_movement_id' => $facilityMovement->id]);
    }

    /**
     * Create received quantity record for warehouse operations
     */
    private function createReceivedQuantityRecord($receivedBackorder)
    {
        // Create received quantity record
        $receivedQuantity = ReceivedQuantity::create([
            'quantity' => $receivedBackorder->quantity,
            'received_by' => auth()->id(),
            'received_at' => now(),
            'transfer_id' => $receivedBackorder->transfer_id,
            'order_id' => $receivedBackorder->order_id,
            'product_id' => $receivedBackorder->product_id,
            'packing_list_id' => $receivedBackorder->packing_list_id,
            'uom' => $receivedBackorder->uom,
            'barcode' => $receivedBackorder->barcode,
            'batch_number' => $receivedBackorder->batch_number,
            'warehouse_id' => $receivedBackorder->warehouse_id,
            'facility_id' => $receivedBackorder->facility_id,
            'expiry_date' => $receivedBackorder->expire_date,
            'unit_cost' => $receivedBackorder->unit_cost,
            'total_cost' => $receivedBackorder->total_cost
        ]);
        logger()->info('Created received quantity record', ['received_quantity_id' => $receivedQuantity->id]);
    }

    /**
     * Create movement record based on transfer destination
     */
    private function createMovementRecord($receivedBackorder)
    {
        // Get transfer details
        $transfer = Transfer::find($receivedBackorder->transfer_id);
        if (!$transfer) {
            throw new \Exception('Transfer not found for received backorder');
        }

        // Determine if transfer is to warehouse or facility
        if ($transfer->to_warehouse_id) {
            // Transfer to warehouse - use ReceivedQuantity
            $this->createReceivedQuantityRecord($receivedBackorder);
        } elseif ($transfer->to_facility_id) {
            // Transfer to facility - use FacilityInventoryMovement
            $this->createFacilityTransferMovement($receivedBackorder, $transfer);
        } else {
            throw new \Exception('Transfer has no destination warehouse or facility');
        }
    }

    /**
     * Create facility transfer movement record
     */
    private function createFacilityTransferMovement($receivedBackorder, $transfer)
    {
        $facilityId = $transfer->to_facility_id;

        // Create facility inventory movement record
        $movementData = [
            'facility_id' => $facilityId,
            'product_id' => $receivedBackorder->product_id,
            'source_type' => 'transfer',
            'source_id' => $receivedBackorder->transfer_id,
            'source_item_id' => null, // Could be transfer_item_id if available
            'facility_received_quantity' => $receivedBackorder->quantity,
            'batch_number' => $receivedBackorder->batch_number,
            'expiry_date' => $receivedBackorder->expire_date,
            'barcode' => $receivedBackorder->barcode,
            'uom' => $receivedBackorder->uom,
            'movement_date' => now(),
            'reference_number' => $receivedBackorder->received_backorder_number,
            'notes' => 'Received from transfer backorder approval',
        ];

        $facilityMovement = FacilityInventoryMovement::recordFacilityReceived($movementData);
        logger()->info('Created facility transfer movement record', ['facility_movement_id' => $facilityMovement->id]);
    }
}
