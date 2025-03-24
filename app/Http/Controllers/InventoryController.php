<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Events\InventoryEvent;
use Illuminate\Support\Facades\Event;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::query()->with(['product.dosage.category', 'warehouse']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('location') && $request->location) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        if ($request->has('batch_number') && $request->batch_number) {
            $query->where('batch_number', 'like', "%{$request->batch_number}%");
        }

        if ($request->has('expiry_date_from') && $request->expiry_date_from) {
            $query->whereDate('expiry_date', '>=', $request->expiry_date_from);
        }

        if ($request->has('expiry_date_to') && $request->expiry_date_to) {
            $query->whereDate('expiry_date', '<=', $request->expiry_date_to);
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $inventories = $query->paginate($request->input('per_page', 10));

        // Get products for dropdown
        $products = Product::where('is_active', true)->select('id', 'name')->get();

        // Get warehouses for dropdown
        $warehouses = \App\Models\Warehouse::select('id', 'name', 'code')->get();

        // Get inventory status counts
        $inStockCount = Inventory::where('quantity', '>', DB::raw('reorder_level'))->where('is_active', true)->count();
        $lowStockCount = Inventory::where('quantity', '>', 0)->where('quantity', '<=', DB::raw('reorder_level'))->where('is_active', true)->count();
        $outOfStockCount = Inventory::where('quantity', 0)->where('is_active', true)->count();
        $soonExpiringCount = Inventory::where('expiry_date', '>', now())->where('expiry_date', '<=', now()->addDays(30))->where('is_active', true)->count();
        $expiredCount = Inventory::where('expiry_date', '<', now())->where('is_active', true)->count();
        
        $inventoryStatusCounts = [
            ['status' => 'in_stock', 'count' => $inStockCount],
            ['status' => 'low_stock', 'count' => $lowStockCount],
            ['status' => 'out_of_stock', 'count' => $outOfStockCount],
            ['status' => 'soon_expiring', 'count' => $soonExpiringCount],
            ['status' => 'expired', 'count' => $expiredCount],
        ];

        return Inertia::render('Inventory/Index', [
            'inventories' => InventoryResource::collection($inventories),
            'products' => $products,
            'warehouses' => $warehouses,
            'filters' => $request->all(['search', 'product_id', 'warehouse_id', 'location', 'batch_number', 'expiry_date_from', 'expiry_date_to', 'sort_field', 'sort_direction', 'per_page']),
            'inventoryStatusCounts' => $inventoryStatusCounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
           
        $validated = $request->validate([
            'id' => 'nullable|exists:inventories,id',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:manufacturing_date',
            'batch_number' => 'nullable|string',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $inventory = Inventory::updateOrCreate(
            ['id' => $request->id],
            $validated
        );

        // event(new InventoryEvent());
        logger()->info("done");
        return response()->json( $request->id ? 'Inventory updated successfully' : 'Inventory created successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load(['product.category', 'product.dosage']);
        return response()->json([
            'success' => true,
            'data' => new InventoryResource($inventory),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory item deleted successfully',
        ]);
    }
    
}
