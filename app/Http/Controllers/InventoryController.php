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
use App\Events\InventoryUpdated;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::query();

        $user = auth()->user();

        // if(!$user->hasRole('admin')) {
            // $query = $query->where('warehouse_id', $user->warehouse_id);
        // }
        
        $query = $query->with(['product.dosage.category', 'warehouse']);

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

        $inventories = $query->paginate($request->input('per_page', 6), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        // Get products for dropdown
        $products = Product::where('is_active', true)->select('id', 'name')->get();

        // Get warehouses for dropdown
        $warehouses = \App\Models\Warehouse::where('id', auth()->user()->warehouse_id)->select('id', 'name', 'code')->get();

        // Get inventory status counts
        $inStockCount = Inventory::where('warehouse_id', $user->warehouse_id)->where('quantity', '>', DB::raw('reorder_level'))->where('is_active', true)->count();
        $lowStockCount = Inventory::where('warehouse_id', $user->warehouse_id)->where('quantity', '>', 0)->where('quantity', '<=', DB::raw('reorder_level'))->where('is_active', true)->count();
        $outOfStockCount = Inventory::where('warehouse_id', $user->warehouse_id)->where('quantity', 0)->where('is_active', true)->count();
        $soonExpiringCount = Inventory::where('warehouse_id', $user->warehouse_id)->where('expiry_date', '>', now())->where('expiry_date', '<=', now()->addDays(30))->where('is_active', true)->count();
        $expiredCount = Inventory::where('warehouse_id', $user->warehouse_id)->where('expiry_date', '<', now())->where('is_active', true)->count();
        
        $inventoryStatusCounts = [
            ['status' => 'in_stock', 'count' => $inStockCount],
            ['status' => 'low_stock', 'count' => $lowStockCount],
            ['status' => 'out_of_stock', 'count' => $outOfStockCount],
            ['status' => 'soon_expiring', 'count' => $soonExpiringCount],
            ['status' => 'expired', 'count' => $expiredCount],
        ];

        $inventories->setPath(url()->current()); // Force Laravel to use full URLs


        return Inertia::render('Inventory/Index', [
            'inventories' => InventoryResource::collection($inventories),
            'products' => $products,
            'warehouses' => $warehouses,
            'filters' => $request->all(['search', 'product_id', 'warehouse_id', 'location', 'batch_number', 'expiry_date_from', 'expiry_date_to', 'sort_field', 'sort_direction', 'per_page', 'page']),
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

        $isNew = !$request->id;
        
        $inventory = Inventory::updateOrCreate(
            ['id' => $request->id],
            $validated
        );

        event(new InventoryUpdated());
        
        return response()->json( $request->id ? 'Inventory updated successfully' : 'Inventory created successfully', 200);
        } catch (\Throwable $th) {
            logger()->error('[PUSHER-DEBUG] Error in store method: ' . $th->getMessage());
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
        try {
            $inventory->delete();
            event(new InventoryEvent());
            Log::info('Successfully dispatched InventoryEvent for deleted inventory ID: ' . $inventory->id);
            return response()->json([
                'success' => true,
                'message' => 'Inventory item deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }   
    }
    
    /**
     * Handle bulk actions for inventory items
     */
    public function bulk(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:inventories,id',
                'action' => 'required|string|in:delete'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();
            try {
                if ($request->action === 'delete') {
                    $inventories = Inventory::whereIn('id', $request->ids)->get();
                    foreach ($inventories as $inventory) {
                        $inventory->delete();
                    }
                    
                    // Broadcast the event to refresh other clients
                    broadcast(new InventoryUpdated())->toOthers();
                }
                DB::commit();

                return response()->json(['message' => 'Bulk action completed successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Bulk action failed: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing the bulk action'], 500);
        }
    }
}
