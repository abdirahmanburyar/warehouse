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
use App\Models\IssueQuantityReport;
use App\Models\IssueQuantityItem;
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
        // 1. Determine the last 4 distinct report months
        $lastFourReportMonths = IssueQuantityReport::select('month_year')
            ->distinct()
            ->orderBy('month_year', 'desc')
            ->limit(4)
            ->pluck('month_year');

        // Prepare a subquery for AMC calculation
        $amcSubqueryBase = IssueQuantityItem::query()
            ->join('issue_quantity_reports', 'issue_quantity_items.parent_id', '=', 'issue_quantity_reports.id')
            ->whereIn('issue_quantity_reports.month_year', $lastFourReportMonths)
            ->select(
                'issue_quantity_items.product_id',
                'issue_quantity_items.batch_number',
                DB::raw('COALESCE(SUM(issue_quantity_items.quantity) / 4, 0) as amc')
            )
            ->groupBy('issue_quantity_items.product_id', 'issue_quantity_items.batch_number');

        // Main inventory query
        $query = Inventory::query()
            ->leftJoinSub($amcSubqueryBase, 'amc_data', function ($join) {
                $join->on('inventories.product_id', '=', 'amc_data.product_id')
                     ->on('inventories.batch_number', '=', 'amc_data.batch_number');
            })
            ->addSelect('inventories.*')
            ->addSelect(DB::raw('COALESCE(amc_data.amc, 0) as amc'))
            ->addSelect(DB::raw('ROUND(COALESCE(amc_data.amc, 0) * 6) as reorder_level')); // AMC * 6

        $user = auth()->user();
        
        $query = $query->with(['product.dosage:id,name', 'product.category:id,name', 'warehouse','location:id,location']);

        // Apply filters
        if ($request->has('search') && $request->search) { // Ensure search is not empty
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inventories.barcode', 'like', "%{$search}%")
                  ->orWhere('inventories.batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($prodQ) use ($search) {
                      $prodQ->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('product_id') && $request->product_id) {
            $query->where('inventories.product_id', $request->product_id);
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
            $query->whereHas('warehouse', function($q_warehouse) use($request){ // Renamed variable
                $q_warehouse->where('name','like', "%{$request->warehouse}%");
            });
        }

        if ($request->has('location') && $request->location) {
            $query->whereHas('location', function($q_location) use($request){ // Renamed variable
                $q_location->where('location','like', "%{$request->location}%");
            }); 
        }
        
        $perPage = $request->input('per_page', 25); // Default to 25
        $inventories = $query->paginate($perPage)
            ->withQueryString();

        // Debug log for AMC and Reorder Level
        if ($inventories->isNotEmpty()) {
            Log::debug('Inventory AMC and Reorder Level Calculation Debug:');
            foreach ($inventories->take(5) as $item) { // Log first 5 items
                Log::debug(sprintf(
                    'Product ID: %s, Batch: %s, AMC: %s, Reorder Level: %s, Current Qty: %s',
                    $item->product_id,
                    $item->batch_number,
                    $item->amc,         // This is the calculated AMC from the query
                    $item->reorder_level, // This is the calculated Reorder Level
                    $item->quantity
                ));
            }
        }

        $products = Product::select('id', 'name')->get();
        
        $userWarehouses = \App\Models\Warehouse::query();
        if ($user->warehouse_id) {
            $userWarehouses->where('id', $user->warehouse_id);
        }
        $warehouses = $userWarehouses->pluck('name')->toArray();
        
        // Inventory Status Counts

        // in stock: quantity > reorder_level
        $inStockCount = DB::table('inventories as inv')
            ->leftJoinSub($amcSubqueryBase, 'amc_data', function($join) {
                $join->on('inv.product_id', '=', 'amc_data.product_id')
                     ->on('inv.batch_number', '=', 'amc_data.batch_number');
            })
            ->whereRaw('inv.quantity > ROUND(COALESCE(amc_data.amc, 0) * 6)')
            ->count();

        // low stock: 0 < quantity <= reorder_level
        $lowStockCount = DB::table('inventories as inv')
            ->leftJoinSub($amcSubqueryBase, 'amc_data', function($join) {
                $join->on('inv.product_id', '=', 'amc_data.product_id')
                     ->on('inv.batch_number', '=', 'amc_data.batch_number');
            })
            ->where('inv.quantity', '>', 0)
            ->whereRaw('inv.quantity <= ROUND(COALESCE(amc_data.amc, 0) * 6)')
            ->count();

        $outOfStockCount = DB::table('inventories')->where('quantity', 0)->count();
        $soonExpiringCount = DB::table('inventories')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(160))
            ->count();
        $expiredCount = DB::table('inventories')->where('expiry_date', '<', now())->count();
        
        $inventoryStatusCounts = [
            ['status' => 'in_stock', 'count' => $inStockCount],
            ['status' => 'low_stock', 'count' => $lowStockCount],
            ['status' => 'out_of_stock', 'count' => $outOfStockCount],
            ['status' => 'soon_expiring', 'count' => $soonExpiringCount],
            ['status' => 'expired', 'count' => $expiredCount],
        ];

        return Inertia::render('Inventory/Index', [
            'inventories' => InventoryResource::collection($inventories),
            'inventoryStatusCounts' => $inventoryStatusCounts,
            'products' => $products,
            'warehouses' => $warehouses,
            'filters' => $request->only('search', 'product_id', 'warehouse', 'dosage','category', 'location', 'batch_number', 'expiry_date_from', 'expiry_date_to', 'per_page', 'page'),
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
