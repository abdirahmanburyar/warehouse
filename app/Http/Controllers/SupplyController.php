<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supply;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Inventory;
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
        // Determine active tab
        $activeTab = $request->tab ?? 'supplies';
        
        // Get supplies data
        $supplies = Supply::query()
            ->with(['product', 'warehouse', 'supplier'])
            ->orderBy('created_at', 'desc');
            
        // Apply filters for supplies
        if ($request->filled('search')) {
            $search = $request->search;
            $supplies->where(function ($query) use ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('supplier', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhere('invoice_number', 'like', "%{$search}%")
                ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('warehouse_id')) {
            $supplies->where('warehouse_id', $request->warehouse_id);
        }
        
        if ($request->filled('date_from')) {
            $supplies->whereDate('supply_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $supplies->whereDate('supply_date', '<=', $request->date_to);
        }
        
        // Get suppliers data
        $suppliers = Supplier::query()
            ->withCount('supplies')
            ->orderBy('name');
            
        // Apply filters for suppliers
        if ($request->filled('search') && $activeTab === 'suppliers') {
            $search = $request->search;
            $suppliers->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('active')) {
            $suppliers->where('is_active', $request->active);
        }
        
        // Paginate results
        $supplies = $supplies->paginate(10)->withQueryString();
        $suppliers = $suppliers->paginate(10)->withQueryString();
        
        // Get all warehouses for filter
        $warehouses = Warehouse::all();
        
        // Get all products for supply form
        $products = Product::all();
        
        return Inertia::render('Supplies/Index', [
            'supplies' => $supplies,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'supplyFilters' => $request->only(['search', 'warehouse_id', 'date_from', 'date_to']),
            'supplierFilters' => $request->only(['search', 'active']),
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Show the form for creating a new supply.
     */
    public function create()
    {
        $products = Product::all();
        $warehouses = Warehouse::get();
        $suppliers = Supplier::where('is_active', true)->get();

        return Inertia::render('Supplies/Create', [
            'products' => $products,
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created supply in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'supply_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:255',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:manufacturing_date',
            'notes' => 'nullable|string',
        ]);

        // Calculate total price
        $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];

        DB::beginTransaction();

        try {
            // Create the supply record
            $supply = Supply::create($validated);

            // Update inventory
            $inventory = Inventory::firstOrNew([
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'batch_number' => $validated['batch_number'],
            ]);

            // If it's a new inventory item, set these properties
            if (!$inventory->exists) {
                $inventory->manufacturing_date = $validated['manufacturing_date'];
                $inventory->expiry_date = $validated['expiry_date'];
                $inventory->quantity = 0;
            }

            // Increase the quantity
            $inventory->quantity += $validated['quantity'];
            $inventory->save();

            DB::commit();

            return redirect()->route('supplies.index')
                ->with('success', 'Supply added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to add supply: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified supply.
     */
    public function show(Supply $supply)
    {
        $supply->load(['product', 'warehouse', 'supplier']);
        
        return Inertia::render('Supplies/Show', [
            'supply' => $supply,
        ]);
    }

    /**
     * Show the form for editing the specified supply.
     */
    public function edit(Supply $supply)
    {
        $supply->load(['product', 'warehouse', 'supplier']);
        $products = Product::all();
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();
        
        return Inertia::render('Supplies/Edit', [
            'supply' => $supply,
            'products' => $products,
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Update the specified supply in storage.
     */
    public function update(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'supply_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:255',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:manufacturing_date',
            'notes' => 'nullable|string',
        ]);

        // Calculate total price
        $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];

        DB::beginTransaction();

        try {
            // Get the old supply data for inventory adjustment
            $oldSupply = $supply->toArray();
            
            // Update the supply record
            $supply->update($validated);

            // Adjust inventory
            if ($oldSupply['product_id'] == $validated['product_id'] && 
                $oldSupply['warehouse_id'] == $validated['warehouse_id'] &&
                $oldSupply['batch_number'] == $validated['batch_number']) {
                
                // Same product, warehouse and batch - just adjust quantity
                $inventory = Inventory::where([
                    'product_id' => $validated['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'batch_number' => $validated['batch_number'],
                ])->first();
                
                if ($inventory) {
                    // Adjust quantity (remove old quantity, add new quantity)
                    $inventory->quantity = $inventory->quantity - $oldSupply['quantity'] + $validated['quantity'];
                    $inventory->save();
                }
            } else {
                // Different product, warehouse or batch - remove from old, add to new
                
                // Remove from old inventory
                $oldInventory = Inventory::where([
                    'product_id' => $oldSupply['product_id'],
                    'warehouse_id' => $oldSupply['warehouse_id'],
                    'batch_number' => $oldSupply['batch_number'],
                ])->first();
                
                if ($oldInventory) {
                    $oldInventory->quantity -= $oldSupply['quantity'];
                    $oldInventory->save();
                }
                
                // Add to new inventory
                $newInventory = Inventory::firstOrNew([
                    'product_id' => $validated['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'batch_number' => $validated['batch_number'],
                ]);
                
                if (!$newInventory->exists) {
                    $newInventory->manufacturing_date = $validated['manufacturing_date'];
                    $newInventory->expiry_date = $validated['expiry_date'];
                    $newInventory->quantity = 0;
                }
                
                $newInventory->quantity += $validated['quantity'];
                $newInventory->save();
            }

            DB::commit();

            return redirect()->route('supplies.index')
                ->with('success', 'Supply updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update supply: ' . $e->getMessage())
                ->withInput();
        }
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
}
