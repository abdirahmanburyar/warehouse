<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Location;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Events\InventoryEvent;
use Illuminate\Support\Facades\Event;
use App\Events\InventoryUpdated;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventoryImport;
use App\Services\InventoryAnalyticsService;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Join AMC subquery for each inventory row (amc as integer, reorder_level = amc * 5)
        $fiveMonthsAgo = now()->subMonths(5)->startOfMonth();
        $leadTime = 5;
        $now = now();
        $months = [];
        // Get last 5 completed months (excluding current month)
        for ($i = 1; $i <= 5; $i++) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth()->format('Y-m-01');
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth()->format('Y-m-t');
            $months[] = [
                'start' => $monthStart,
                'end' => $monthEnd,
                'label' => $now->copy()->subMonths($i)->format('Y-m')
            ];
        }

        // Build a union of 5 subqueries, one per month, to ensure missing months are counted as 0
        $union = null;
        foreach ($months as $idx => $m) {
            $sub = DB::table('issue_quantity_items')
                ->select(
                    'product_id',
                    'batch_number',
                    DB::raw($idx . ' as month_idx'),
                    DB::raw('SUM(quantity) as month_qty')
                )
                ->whereBetween('issued_date', [$m['start'], $m['end']])
                ->groupBy('product_id', 'batch_number');
            if ($union === null) {
                $union = $sub;
            } else {
                $union = $union->unionAll($sub);
            }
        }

        // Now sum the 5 months per product/batch, filling missing months with 0
        $amcSubquery = DB::query()->fromSub(function($q) use ($union) {
            $q->fromSub($union, 'monthly')
                ->select('product_id', 'batch_number', DB::raw('SUM(month_qty) as total_qty'))
                ->groupBy('product_id', 'batch_number');
        }, 'amc_base')
            ->select(
                'product_id',
                'batch_number',
                DB::raw('FLOOR(COALESCE(total_qty, 0) / 5) as amc'),
                DB::raw('FLOOR(COALESCE(total_qty, 0) / 5 * ' . $leadTime . ') as reorder_level')
            );

        $query = Inventory::query()
            ->leftJoinSub($amcSubquery, 'amc_data', function($join) {
                $join->on('inventories.product_id', '=', 'amc_data.product_id')
                     ->on('inventories.batch_number', '=', 'amc_data.batch_number');
            })
            ->addSelect('inventories.*')
            ->addSelect(DB::raw('COALESCE(amc_data.amc, 0) as amc'))
            ->addSelect(DB::raw('COALESCE(amc_data.reorder_level, 0) as reorder_level'));


        $user = auth()->user();
        
        $query = $query->with(['product.dosage:id,name', 'product.category:id,name', 'warehouse','location:id,location']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('barcode', 'like', "%{$search}%")
                ->orWhere('batch_number', 'like', "%{$search}%")
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }
        
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category')) {
            $query->whereHas('product.category', function ($q) use ($request) {
                $q->where('name', $request->category);
              });
        }

        if ($request->filled('dosage')) {
            $query->whereHas('product.dosage', function ($q) use ($request) {
                $q->where('name', $request->dosage);
              });
        }


        if ($request->filled('warehouse')) {
            $query->whereHas('warehouse', function($query) use($request){
                $query->where('name','like', "%{$request->warehouse}%");
            });
        }

        if ($request->has('location') && $request->location) {
            $query->whereHas('location', function($query) use($request){
                $query->where('location','like', "%{$request->location}%");
            }); 
        }

        $inventories = $query->paginate($request->input('per_page', 2), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        // Get products for dropdown
        $products = Product::select('id', 'name')->get();

        // Get warehouses for dropdown
        $warehouses = \App\Models\Warehouse::where('id', auth()->user()->warehouse_id)->select('id', 'name', 'code')->pluck('name')->toArray();
        
        // Get inventory status counts
       // Define AMC subquery
        $fiveMonthsAgo = now()->subMonths(5)->startOfMonth();
        $amcSubquery = DB::table('issued_quantities')
            ->select(
                'product_id',
                'batch_number',
                DB::raw('COALESCE(SUM(quantity), 0) / 5 as amc')
            )
            ->where('issued_date', '>=', $fiveMonthsAgo)
            ->groupBy('product_id', 'batch_number');

        // Format AMC as integer for logging
        $amcResults = $amcSubquery->get()->map(function($row) {
            $row->amc = (int) round($row->amc);
            return $row;
        });
        logger()->info($amcResults);
        // in stock: quantity > reorder_level
        $inStockCount = DB::table('inventories')
            ->leftJoinSub($amcSubquery, 'amc_data', function($join) {
                $join->on('inventories.product_id', '=', 'amc_data.product_id')
                    ->on('inventories.batch_number', '=', 'amc_data.batch_number');
            })
            ->whereRaw('inventories.quantity > (COALESCE(amc_data.amc, 0) * 5)')
            ->count();

        // low stock: 0 < quantity <= reorder_level
        $lowStockCount = DB::table('inventories')
            ->leftJoinSub($amcSubquery, 'amc_data', function($join) {
                $join->on('inventories.product_id', '=', 'amc_data.product_id')
                    ->on('inventories.batch_number', '=', 'amc_data.batch_number');
            })
            ->where('inventories.quantity', '>', 0)
            ->whereRaw('inventories.quantity <= (COALESCE(amc_data.amc, 0) * 6)')
            ->count();

        // out of stock: quantity = 0
        $outOfStockCount = DB::table('inventories')
            ->where('inventories.quantity', 0)
            ->count();

        // soon expiring: expiry_date > today and expiry_date <= 30 days from today
        $soonExpiringCount = DB::table('inventories')
            ->where('inventories.expiry_date', '>', now())
            ->where('inventories.expiry_date', '<=', now()->addDays(30))
            ->count();

        // expired: expiry_date < today
        $expiredCount = DB::table('inventories')
            ->where('inventories.expiry_date', '<', now())
            ->count();
        
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
            'filters' => $request->only('search', 'product_id', 'warehouse', 'dosage','category', 'location', 'batch_number', 'expiry_date_from', 'expiry_date_to', 'per_page', 'page'),
            'inventoryStatusCounts' => $inventoryStatusCounts,
            'category' => Category::pluck('name')->toArray(),
            'dosage' => Dosage::pluck('name')->toArray(),
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
    
    public function getLocations(Request $request){
        try {
            $locations = Location::whereHas('warehouse', function($query) use($request){
                $query->where('name', $request->warehouse);
            })->pluck('location')->toArray();
            return response()->json($locations, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
    /**
     * Import inventory items from Excel file
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max file size
            ]);
            
            // Process the import
            $import = new InventoryImport();
            Excel::import($import, $request->file('file'));
            
            // Get import statistics
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();
            
            // Dispatch event to notify other users of the update
            // Pass a dummy inventory object with import summary data
            $dummyInventory = [
                'product_id' => 0,
                'quantity' => $importedCount,
                'type' => 'bulk_import',
                'message' => "Bulk import: {$importedCount} items imported"
            ];
            event(new InventoryUpdated($dummyInventory));
            
            return response()->json([
                'success' => true,
                'message' => "Import completed: {$importedCount} items imported, {$skippedCount} items skipped",
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ]);
        } catch (\Throwable $th) {
            Log::error('Inventory import error: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $th->getMessage()
            ], 500);
        }
    }
}
