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
use App\Models\OrderItem;
use App\Models\TransferItem;
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
        $query = ReceivedBackorder::with(['receivedBy', 'reviewedBy', 'approvedBy', 'rejectedBy', 'backOrder', 'warehouse', 'facility']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('received_backorder_number', 'like', '%' . $request->search . '%')
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
            'total_quantity' => \App\Models\ReceivedBackorderItem::sum('quantity'),
            'total_cost' => \App\Models\ReceivedBackorderItem::sum('total_cost'),
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
        $receivedBackorder->load([
            'items.product',
            'receivedBy',
            'reviewedBy',
            'approvedBy',
            'rejectedBy',
            'backOrder',
            'warehouse',
            'facility'
        ]);

        return inertia('Supplies/ReceivedBackorder/Show', [
            'receivedBackorder' => $receivedBackorder,
        ]);
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

            DB::beginTransaction();

            // Update the received back order status
            $receivedBackorder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'note' => $validated['note'] ?? $receivedBackorder->note,
            ]);

            // Handle physical count adjustment type specifically
            if ($receivedBackorder->type === 'physical_count_adjustment') {
                logger()->info('Processing physical count adjustment received backorder', [
                    'received_backorder_id' => $receivedBackorder->id,
                    'warehouse_id' => $receivedBackorder->warehouse_id,
                    'items_count' => $receivedBackorder->items->count()
                ]);
                $this->handlePhysicalCountAdjustmentInventory($receivedBackorder);
            }
            // Determine if this is an order or transfer and handle inventory accordingly
            elseif ($receivedBackorder->order_id) {
                // Handle ORDER - Use FacilityInventory and FacilityInventoryItem
                $this->handleOrderInventory($receivedBackorder);
                // Create facility inventory movement record for orders
                $this->createFacilityInventoryMovement($receivedBackorder, 'order');
            } elseif ($receivedBackorder->transfer_id) {
                // Handle TRANSFER - Determine if warehouse or facility and use appropriate inventory
                $this->handleTransferInventory($receivedBackorder);
                // Create appropriate movement record based on transfer destination
                $this->createMovementRecord($receivedBackorder);
            } else {
                // Fallback to original warehouse inventory logic
                $this->handleWarehouseInventory($receivedBackorder);
                // Create received quantity record for warehouse operations
                $this->createReceivedQuantityRecord($receivedBackorder);
            }

            // Update the packing list quantity if packing_list_id exists
            // Note: For physical count adjustments, packing list updates are handled in the job
            if ($receivedBackorder->packing_list_id && $receivedBackorder->type !== 'physical_count_adjustment') {
                $packingList = PackingListItem::where('packing_list_id', $receivedBackorder->packing_list_id)
                    ->where('product_id', $receivedBackorder->product_id)
                    ->first();
                
                if ($packingList) {
                    $packingList->increment('quantity', $receivedBackorder->quantity);
                    $packingList->save();
                }
            }

            DB::commit();

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
     * Handle inventory for physical count adjustments
     */
    private function handlePhysicalCountAdjustmentInventory($receivedBackorder)
    {
        try {
            // Get the warehouse ID from the received backorder
            $warehouseId = $receivedBackorder->warehouse_id;
            
            if (!$warehouseId) {
                throw new \Exception('No warehouse_id found in physical count adjustment received backorder');
            }

            // Load the items for this received backorder
            $items = $receivedBackorder->items;
            
            if ($items->isEmpty()) {
                throw new \Exception('No items found in physical count adjustment received backorder');
            }

            // Process each item
            foreach ($items as $item) {
                // Update or create main inventory
                $inventory = Inventory::firstOrCreate([
                    'product_id' => $item->product_id,
                ], [
                    'quantity' => 0,
                ]);

                $oldQuantity = $inventory->quantity;
                $newQuantity = $oldQuantity + $item->quantity;
                $inventory->update(['quantity' => $newQuantity]);

                // Update or create inventory item with batch details
                $inventoryItem = InventoryItem::where('inventory_id', $inventory->id)
                    ->where('batch_number', $item->batch_number)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($inventoryItem) {
                    $inventoryItem->increment('quantity', $item->quantity);
                    $inventoryItem->save();
                } else {
                    $inventoryItem = InventoryItem::create([
                        'inventory_id' => $inventory->id,
                        'quantity' => $item->quantity,
                        'batch_number' => $item->batch_number,
                        'expiry_date' => $item->expire_date,
                        'barcode' => $item->barcode,
                        'warehouse_id' => $warehouseId,
                        'location' => $item->location,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $item->total_cost,
                        'uom' => $item->uom,
                        'status' => 'active'
                    ]);
                }

                // Create received quantity record for each item
                $this->createReceivedQuantityRecordForItem($receivedBackorder, $item);

                logger()->info('Physical count adjustment item processed', [
                    'received_backorder_id' => $receivedBackorder->id,
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_added' => $item->quantity,
                    'warehouse_id' => $warehouseId
                ]);
            }

            logger()->info('Physical count adjustment inventory updated successfully', [
                'received_backorder_id' => $receivedBackorder->id,
                'items_processed' => $items->count(),
                'warehouse_id' => $warehouseId
            ]);

        } catch (\Exception $e) {
            logger()->error('Error in handlePhysicalCountAdjustmentInventory', [
                'received_backorder_id' => $receivedBackorder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle inventory for orders (facility inventory)
     */
    private function handleOrderInventory($receivedBackorder)
    {
        try {
            // Use facility_id directly from receivedBackorder
            $facilityId = $receivedBackorder->facility_id;
            
            if (!$facilityId) {
                throw new \Exception('No facility_id found in received backorder');
            }

            $facilityInventory = FacilityInventory::firstOrCreate([
                'product_id' => $receivedBackorder->product_id,
                'facility_id' => $facilityId,
            ], [
                'quantity' => 0,
            ]);

            $oldQuantity = $facilityInventory->quantity;
            
            // Use direct update instead of increment to ensure it works
            $newQuantity = $oldQuantity + $receivedBackorder->quantity;
            $facilityInventory->update(['quantity' => $newQuantity]);

            // Update or create facility inventory item
            $facilityInventoryItem = FacilityInventoryItem::where('facility_inventory_id', $facilityInventory->id)
                ->where('batch_number', $receivedBackorder->batch_number)
                ->first();

            if ($facilityInventoryItem) {
                $facilityInventoryItem->increment('quantity', $receivedBackorder->quantity);
                $facilityInventoryItem->save();
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
            }

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
        // Use facility_id directly from receivedBackorder
        $facilityId = $receivedBackorder->facility_id;
        $orderId = $receivedBackorder->order_id;
        $productId = $receivedBackorder->product_id;

        // Find the order item for this product/order
        $orderItem = OrderItem::where('order_id', $orderId)
            ->where('product_id', $productId)
            ->first();
        if (!$orderItem) {
            throw new \Exception('OrderItem not found for order_id ' . $orderId . ' and product_id ' . $productId);
        }

        // Create facility inventory movement record
        $movementData = [
            'facility_id' => $facilityId,
            'product_id' => $productId,
            'source_type' => $type,
            'source_id' => $orderId,
            'source_item_id' => $orderItem->id,
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
    }

    /**
     * Create received quantity record for individual items
     */
    private function createReceivedQuantityRecordForItem($receivedBackorder, $item)
    {
        // Create received quantity record for the specific item
        $receivedQuantity = ReceivedQuantity::create([
            'quantity' => $item->quantity,
            'received_by' => auth()->id(),
            'received_at' => now(),
            'transfer_id' => $receivedBackorder->transfer_id,
            'order_id' => $receivedBackorder->order_id,
            'product_id' => $item->product_id,
            'packing_list_id' => $receivedBackorder->packing_list_id,
            'uom' => $item->uom,
            'barcode' => $item->barcode,
            'batch_number' => $item->batch_number,
            'warehouse_id' => $receivedBackorder->warehouse_id,
            'facility_id' => $receivedBackorder->facility_id,
            'expiry_date' => $item->expire_date,
            'unit_cost' => $item->unit_cost,
            'total_cost' => $item->total_cost
        ]);
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

        // Find the transfer item for this product/transfer
        $transferItem = TransferItem::where('transfer_id', $receivedBackorder->transfer_id)
            ->where('product_id', $receivedBackorder->product_id)
            ->first();
        
        if (!$transferItem) {
            // If no transfer item found, use a default value or skip the movement record
            logger()->warning('TransferItem not found for transfer_id ' . $receivedBackorder->transfer_id . ' and product_id ' . $receivedBackorder->product_id);
            return;
        }

        // Create facility inventory movement record
        $movementData = [
            'facility_id' => $facilityId,
            'product_id' => $receivedBackorder->product_id,
            'source_type' => 'transfer',
            'source_id' => $receivedBackorder->transfer_id,
            'source_item_id' => $transferItem->id,
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
    }
}
