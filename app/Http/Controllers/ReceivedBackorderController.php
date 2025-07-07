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

        if ($request->filled('date_from')) {
            $query->where('received_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('received_at', '<=', $request->date_to);
        }

        // Filter by warehouse
        if ($request->filled('warehouse') && $request->warehouse) {
            $query->where('reported_by', $request->warehouse);
        }

        // Filter by facility
        if ($request->filled('facility') && $request->facility) {
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
            DB::beginTransaction();

            // Update the received back order status
            $receivedBackorder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'note' => $validated['note'] ?? $receivedBackorder->note,
            ]);

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

            // Create received quantity record
            $receivedQuantity = ReceivedQuantity::create([
                'quantity' => $receivedBackorder->quantity,
                'received_by' => auth()->id(),
                'received_at' => now(),
                'transfer_id' => null,
                'product_id' => $receivedBackorder->product_id,
                'packing_list_id' => $receivedBackorder->packing_list_id,
                'uom' => $receivedBackorder->uom,
                'barcode' => $receivedBackorder->barcode,
                'batch_number' => $receivedBackorder->batch_number,
                'warehouse_id' => $inventory->warehouse_id,
                'expiry_date' => $receivedBackorder->expire_date,
                'unit_cost' => $receivedBackorder->unit_cost,
                'total_cost' => $receivedBackorder->total_cost
            ]);
            logger()->info('Created received quantity record', ['received_quantity_id' => $receivedQuantity->id]);

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

            DB::commit();
            logger()->info('Successfully approved received back order', ['received_backorder_id' => $receivedBackorder->id]);

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
}
