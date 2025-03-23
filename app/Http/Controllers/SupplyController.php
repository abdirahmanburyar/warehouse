<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supply;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\SupplyItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SupplyController extends Controller
{
    /**
     * Display a listing of the supplies.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'supplies');
        
        // Get supplies data with relationships
        $supplies = Supply::query()
            ->with(['supplier', 'items.product', 'warehouse'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('items.product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('invoice_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('supplier'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('supply_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('supply_date', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
            
        // Get suppliers data
        $suppliers = Supplier::query()
            ->withCount('supplies')
            ->orderBy('name')
            ->when($request->filled('search') && $activeTab === 'suppliers', function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->paginate(10)
            ->withQueryString();
        
        // Get warehouses for filter
        $warehouses = Warehouse::where('id', auth()->user()->warehouse_id)->get();
        
        // Get all products for supply form
        $products = Product::all();

        return Inertia::render('Supplies/Index', [
            'supplies' => $supplies,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'filters' => [
                'search' => $request->search,
                'supplier' => $request->supplier,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Store a newly created supply in storage.
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'supplier_id' => 'required|exists:suppliers,id',
                    'supply_date' => 'required|date',
                    'invoice_number' => 'nullable|string|max:255',
                    'notes' => 'nullable|string',
                    'products' => 'required|array|min:1',
                    'products.*.product_id' => 'required|exists:products,id',
                    'products.*.quantity' => 'required|integer|min:1',
                    'products.*.manufacturing_date' => 'nullable|date',
                    'products.*.expiry_date' => 'nullable|date|after_or_equal:manufacturing_date',
                    'products.*.batch_number' => 'nullable|string|max:255',
                ]);

                // Create the main supply record
                $supply = Supply::create([
                    'supplier_id' => $validated['supplier_id'],
                    'warehouse_id' => auth()->user()->warehouse_id,
                    'supply_date' => $validated['supply_date'],
                    'invoice_number' => $validated['invoice_number'],
                    'notes' => $validated['notes'],
                ]);

                // Create supply items
                foreach ($validated['products'] as $product) {
                    $supply->items()->create([
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                        'batch_number' => $product['batch_number'],
                        'manufacturing_date' => $product['manufacturing_date'],
                        'expiry_date' => $product['expiry_date'],
                        'status' => 'pending'
                    ]);
                }

                return response()->json('Supply added successfully', 200);
            });
        } catch (\Throwable $e) {
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
     * Approve or reject a supply item.
     */
    public function approveItem(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                // Update the item with approval info
                $item = SupplyItem::findOrFail($id);
                $item->update([
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // If approved, update inventory
                $inventory = Inventory::firstOrNew([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->warehouse_id,
                    'batch_number' => $item->batch_number,
                ]);

                if (!$inventory->exists) {
                    $inventory->manufacturing_date = $item->manufacturing_date;
                    $inventory->expiry_date = $item->expiry_date;
                    $inventory->quantity = 0;
                }

                $inventory->quantity += $item->quantity;
                $inventory->save();

                return response()->json('Approved successfully', 200);
            });
        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 500);
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
}
