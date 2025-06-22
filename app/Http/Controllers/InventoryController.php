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
use App\Models\InventoryItem;
use App\Models\Warehouse;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $lastThreeMonths = IssueQuantityReport::select('month_year')
            ->distinct()
            ->orderByDesc('month_year')
            ->limit(3)
            ->pluck('month_year')
            ->toArray();        

        $amcSubquery = IssueQuantityItem::join('issue_quantity_reports', 'issue_quantity_items.parent_id', '=', 'issue_quantity_reports.id')
            ->whereIn('issue_quantity_reports.month_year', $lastThreeMonths)
            ->select('issue_quantity_items.product_id', DB::raw('COALESCE(SUM(issue_quantity_items.quantity) / 3, 0) as amc'))
            ->groupBy('issue_quantity_items.product_id');

        $query = Inventory::query()
            ->with([
                'items.warehouse:id,name',
                'product:id,name,category_id,dosage_id',
                'product.category:id,name',
                'product.dosage:id,name'
            ])
            ->leftJoinSub($amcSubquery, 'amc_data', function ($join) {
                $join->on('inventories.product_id', '=', 'amc_data.product_id');
            })
            ->addSelect('inventories.*')
            ->addSelect(DB::raw('COALESCE(amc_data.amc, 0) as amc'))
            ->addSelect(DB::raw('ROUND(COALESCE(amc_data.amc, 0) * 6) as reorder_level'));

        if ($search = $request->search) {
            $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category')) {
            $query->whereHas('product.category', fn($q) => $q->where('name', $request->category));
        }

        if ($request->filled('dosage')) {
            $query->whereHas('product.dosage', fn($q) => $q->where('name', $request->dosage));
        }

        $inventories = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
        ->withQueryString();
        $inventories->setPath(url()->current()); // Force Laravel to use full URLs

        $statusCounts = [
            'in_stock' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'soon_expiring' => 0,
            'expired' => 0,
        ];

        $now = now();
        foreach ($inventories as $inventory) {
            $amc = $inventory->amc ?: 0;
            $reorderLevel = $inventory->reorder_level ?: ($amc * 6);

            foreach ($inventory->items ?? [] as $item) {
                $qty = $item->quantity;

                if ($qty == 0) {
                    $statusCounts['out_of_stock']++;
                } elseif ($qty <= $reorderLevel) {
                    $statusCounts['low_stock']++;
                } else {
                    $statusCounts['in_stock']++;
                }

                if ($item->expiry_date) {
                    if ($item->expiry_date < $now) {
                        $statusCounts['expired']++;
                    } elseif ($item->expiry_date <= $now->copy()->addDays(160)) {
                        $statusCounts['soon_expiring']++;
                    }
                }
            }
        }

        return Inertia::render('Inventory/Index', [
            'inventories' => InventoryResource::collection($inventories),
            'inventoryStatusCounts' => collect($statusCounts)->map(fn($count, $status) => ['status' => $status, 'count' => $count]),
            'products'   => Product::select('id', 'name')->get(),
            'warehouses' => Warehouse::pluck('name')->toArray(),
            'filters'    => $request->only(['search', 'product_id', 'category', 'dosage', 'per_page', 'page']),
            'category'   => Category::pluck('name')->toArray(),
            'dosage'     => Dosage::pluck('name')->toArray(),
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
            $locations = Location::where('warehouse', $request->warehouse)
                ->select('id', 'location', 'warehouse')
                ->get()
                ->map(function($location) {
                    return [
                        'id' => $location->id,
                        'location' => $location->location,
                        'warehouse' => $location->warehouse
                    ];
                });
            
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
