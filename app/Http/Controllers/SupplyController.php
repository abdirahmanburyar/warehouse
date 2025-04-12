<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supply;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Events\InventoryUpdated;
use App\Models\Inventory;
use App\Models\SupplyItem;
use Illuminate\Http\Request;
use App\Http\Resources\SupplierResource;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplyController extends Controller
{
    /**
     * Display a listing of the supplies.
     */
    public function index(Request $request)
    {
        // Get suppliers data
        $suppliers = Supplier::query();
        if($request->filled('search')){
            $suppliers->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('contact_person', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        $suppliers = $suppliers->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        
        return Inertia::render('Supplies/Index', [
            'suppliers' => SupplierResource::collection($suppliers),
            'filters' => $request->only('search', 'page','per_page')
        ]);
    }

    /**
     * Store a newly created supply in storage.
     */
    public function store(Request $request)
    {
        
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'invoice_number' => 'required|string',
                'supply_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:supply_items,id',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.product_name' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.batch_number' => 'nullable|string',
                'items.*.manufacturing_date' => 'nullable|date',
                'items.*.expiry_date' => 'nullable|date|after:manufacturing_date',
            ]);
            DB::beginTransaction();

            // Create or update supply
            $supply = Supply::updateOrCreate(
                ['id' => $request->id],
                [
                    'supplier_id' => $validated['supplier_id'],
                    'invoice_number' => $validated['invoice_number'],
                    'supply_date' => $validated['supply_date'],
                    'notes' => $validated['notes'],
                    'warehouse_id' => auth()->user()->warehouse_id,
                ]
            );

            // Get current item IDs
            $currentItemIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete items that are not in the request (only pending items can be deleted)
            if ($request->id) {
                $supply->items()
                    ->where('status', 'pending')
                    ->whereNotIn('id', $currentItemIds)
                    ->delete();
            }

            // Process each item
            foreach ($validated['items'] as $itemData) {
                // If item has ID, update it, otherwise create new
                if (!empty($itemData['id'])) {
                    // Only update if item is still pending
                    SupplyItem::where('id', $itemData['id'])
                        ->where('status', 'pending')
                        ->update([
                            'product_id' => $itemData['product_id'],
                            'product_name' => $itemData['product_name'],
                            'quantity' => $itemData['quantity'],
                            'batch_number' => $itemData['batch_number'] ?? null,
                            'manufacturing_date' => $itemData['manufacturing_date'] ?? null,
                            'expiry_date' => $itemData['expiry_date'] ?? null,
                        ]);
                } else {
                    // Create new item
                    $supply->items()->create([
                        'product_id' => $itemData['product_id'],
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'batch_number' => $itemData['batch_number'] ?? null,
                        'manufacturing_date' => $itemData['manufacturing_date'] ?? null,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'status' => 'pending'
                    ]);
                }
            }

            DB::commit();
            return response()->json('Supply saved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified supply.
     */
    public function show(Supply $supply)
    {
        $supply->load(['items.product', 'warehouse', 'supplier']);
        
        return Inertia::render('Supplies/Show', [
            'supply' => $supply,
        ]);
    }

    /**
     * Remove the specified supply from storage.
     */
    public function destroy(Supply $supply)
    {
        DB::beginTransaction();

        try {
            // Adjust inventory
            $inventory = Inventory::where([
                'product_id' => $supply->product_id,
                'warehouse_id' => $supply->warehouse_id,
                'batch_number' => $supply->batch_number,
            ])->first();
            
            if ($inventory) {
                $inventory->quantity -= $supply->quantity;
                $inventory->save();
            }
            
            // Delete the supply
            $supply->delete();

            DB::commit();

            return redirect()->route('supplies.index')
                ->with('success', 'Supply deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete supply: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple supplies in a batch operation.
     */
    public function storeBatch(Request $request)
    {
        // Validate common fields
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supply_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.batch_number' => 'nullable|string|max:255',
            'products.*.manufacturing_date' => 'nullable|date',
            'products.*.expiry_date' => 'nullable|date|after_or_equal:products.*.manufacturing_date',
        ]);

        DB::beginTransaction();

        try {
            $createdSupplies = [];

            // Process each product in the batch
            foreach ($validated['products'] as $productData) {
                // Calculate total price for this product
                $totalPrice = $productData['quantity'] * $productData['unit_price'];

                // Create supply record
                $supply = Supply::create([
                    'product_id' => $productData['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'supplier_id' => $validated['supplier_id'],
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'total_price' => $totalPrice,
                    'supply_date' => $validated['supply_date'],
                    'invoice_number' => $validated['invoice_number'],
                    'batch_number' => $productData['batch_number'] ?? null,
                    'manufacturing_date' => $productData['manufacturing_date'] ?? null,
                    'expiry_date' => $productData['expiry_date'] ?? null,
                    'notes' => $validated['notes'],
                ]);

                $createdSupplies[] = $supply;

                // Update inventory
                $inventory = Inventory::firstOrNew([
                    'product_id' => $productData['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'batch_number' => $productData['batch_number'] ?? null,
                ]);

                // If it's a new inventory item, set these properties
                if (!$inventory->exists) {
                    $inventory->manufacturing_date = $productData['manufacturing_date'] ?? null;
                    $inventory->expiry_date = $productData['expiry_date'] ?? null;
                    $inventory->quantity = 0;
                }

                // Increase the quantity
                $inventory->quantity += $productData['quantity'];
                $inventory->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplies added successfully',
                'supplies' => $createdSupplies
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add supplies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items for a specific supply
     */
    public function getItems(Supply $supply)
    {
        return $supply->items()->with('product')->get();
    }

    /**
     * Update the status of a supply item.
     */
    public function updateItemStatus(Request $request, SupplyItem $item)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected'
            ]);

            $item->update([
                'status' => $validated['status']
            ]);

            // If approved, update inventory
            if ($validated['status'] === 'approved' && $item->status !== 'approved') {
                $inventory = Inventory::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->supply->warehouse_id,
                        'batch_number' => $item->batch_number,
                    ],
                    [
                        'quantity' => 0,
                        'manufacturing_date' => $item->manufacturing_date,
                        'expiry_date' => $item->expiry_date,
                    ]
                );

                $inventory->quantity += $item->quantity;
                $inventory->save();

                // Fire inventory updated event
                event(new InventoryUpdated($inventory));
            }

            DB::commit();
            return response()->json(['message' => 'Item status updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update item status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve or reject a supply item
     */
    public function approveItem(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'status' => 'required|in:approved,rejected',
            ]);

            $item = SupplyItem::with(['supply', 'product'])->findOrFail($id);
            
            $item->update([
                'status' => $validated['status'],
                'approved_at' => now(),
                'approved_by' => auth()->id()
            ]);

            // If approved, update inventory
            if ($validated['status'] === 'approved') {
                // Check for existing inventory with same product and expiry date
                $inventory = Inventory::where('product_id', $item->product_id)
                    ->where('expiry_date', $item->expiry_date)
                    ->first();

                if ($inventory) {
                    // Update existing inventory quantity
                    $inventory->increment('quantity', $item->quantity);
                } else {
                    // Create new inventory record
                    Inventory::create([
                        'product_id' => $item->product_id,
                        'batch_number' => $item->batch_number,
                        'quantity' => $item->quantity,
                        'expiry_date' => $item->expiry_date,
                        'manufacturing_date' => $item->manufacturing_date,
                        'warehouse_id' => $item->supply->warehouse_id,
                        'created_by' => auth()->id()
                    ]);
                }
            }

            logger()->info('Inventory updated for supply item ' . $item->id);

            event(new InventoryUpdated());

            DB::commit();
            return response()->json(['message' => 'Item status updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update item status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve or reject all pending items in a supply.
     */
    public function approveBulk(Request $request, Supply $supply)
    {
        try {
            return DB::transaction(function () use ($request, $supply) {
                $validated = $request->validate([
                    'status' => 'required|in:approved,rejected',
                    'notes' => 'nullable|string',
                ]);

                $items = $supply->items()->where('status', 'pending')->get();

                foreach ($items as $item) {
                    // Update the item with approval info
                    $item->update([
                        'status' => $validated['status'],
                        'approval_notes' => $validated['notes'],
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    // If approved, update inventory
                    if ($validated['status'] === 'approved') {
                        $inventory = Inventory::firstOrNew([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $supply->warehouse_id,
                            'batch_number' => $item->batch_number,
                        ]);

                        if (!$inventory->exists) {
                            $inventory->manufacturing_date = $item->manufacturing_date;
                            $inventory->expiry_date = $item->expiry_date;
                            $inventory->quantity = 0;
                        }

                        $inventory->quantity += $item->quantity;
                        $inventory->save();
                    }
                }

                return response()->json('Supply items ' . $validated['status'] . ' successfully', 200);
            });
        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Delete multiple supplies at once
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:supplies,id'
        ]);

        try {
            DB::beginTransaction();

            // Delete supplies that have no approved items
            $supplies = Supply::whereIn('id', $request->ids)
                ->whereDoesntHave('items', function ($query) {
                    $query->where('status', 'approved');
                })
                ->get();

            if ($supplies->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Cannot delete supplies because they have approved items.'
                ], 422);
            }

            // Delete the supply items first
            foreach ($supplies as $supply) {
                $supply->items()->delete();
                $supply->delete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Supplies deleted successfully',
                'deleted_count' => $supplies->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete supplies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete supplies after checking if they can be deleted
     */
    public function bulkDeleteSupplies(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:supplies,id'
        ]);

        $supplies = Supply::whereIn('id', $request->ids)
            ->with('items')
            ->get();

        // Check if any supply has approved items
        foreach ($supplies as $supply) {
            if ($supply->items->contains('is_approved', true)) {
                return response()->json([
                    'message' => 'Cannot delete supplies that have approved items.',
                    'supply_id' => $supply->id
                ], 500);
            }
        }

        // If we get here, none of the supplies have approved items
        try {
            DB::beginTransaction();
            
            // Delete all supply items first
            SupplyItem::whereIn('supply_id', $request->ids)->delete();
            
            // Then delete the supplies
            Supply::whereIn('id', $request->ids)->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Supplies deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete supplies: ' . $e->getMessage()
            ], 500);
        }
    }
}
