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
use App\Models\ReorderLevel;
use App\Events\InventoryUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UploadInventory;
use App\Services\InventoryAnalyticsService;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        try {
        $query = Inventory::query()
            ->with([
                'items.warehouse:id,name',
                'product:id,name,category_id,dosage_id',
                'product.category:id,name',
                'product.dosage:id,name'
            ])
            ->leftJoin('reorder_levels', 'inventories.product_id', '=', 'reorder_levels.product_id')
            ->addSelect('inventories.*')
            ->addSelect(DB::raw('COALESCE(reorder_levels.amc, 0) as amc'))
            ->addSelect(DB::raw('COALESCE(reorder_levels.reorder_level, 0) as reorder_level'));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('items', function($q) use($search) {
                $q->where('barcode', 'like', "%{$search}%");
                $q->orWhere('batch_number', 'like', "%{$search}%");
            })
            ->orWhereHas('product', function($q) use($search) {
                $q->where('name', 'like', "%{$search}%");
            });
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

        // Get paginated results for display
        $inventories = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
        ->withQueryString();
        $inventories->setPath(url()->current()); // Force Laravel to use full URLs

        // Calculate status counts independently of pagination
        $statusCounts = $this->calculateInventoryStatusCounts($request);
        logger()->info('[PUSHER-DEBUG] Status counts: ' . json_encode($inventories));

        return Inertia::render('Inventory/Index', [
            'inventories' => InventoryResource::collection($inventories),
            'inventoryStatusCounts' => collect($statusCounts)->map(fn($count, $status) => ['status' => $status, 'count' => $count]),
            'products'   => Product::select('id', 'name')->get(),
            'warehouses' => Warehouse::pluck('name')->toArray(),
            'filters'    => $request->only(['search', 'product_id', 'category', 'dosage', 'per_page', 'page']),
            'category'   => Category::pluck('name')->toArray(),
            'dosage'     => Dosage::pluck('name')->toArray(),
            'locations'  => Location::pluck('location')->toArray(),
            'errors'     => null,
        ]);
        } catch (\Throwable $th) {
            logger()->error('[PUSHER-DEBUG] Error in index method: ' . $th->getMessage());
            return Inertia::render('Inventory/Index', [
                'errors' => $th->getMessage(),
            ]);
        }
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

        // event(new InventoryUpdated());
        
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
            $warehouse = $request->input('warehouse');
            
            if (!$warehouse) {
                return response()->json([], 200);
            }

            $locations = Location::where('warehouse', $warehouse)
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

    public function updateLocation(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'location' => 'required|string|max:255'
        ]);

        try {
            $inventoryItem = InventoryItem::findOrFail($request->inventory_item_id);
            $inventoryItem->update([
                'location' => $request->location
            ]);

            // Trigger inventory update event
            // event(new InventoryUpdated($inventoryItem->inventory));

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $inventoryItem
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating inventory location: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import inventory items from Excel file
     */
    public function import(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded'
                ], 422);
            }
    
            $file = $request->file('file');
    
            // Validate file type
            $extension = $file->getClientOriginalExtension();
            if (!$file->isValid() || !in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Please upload an Excel file (.xlsx, .xls) or CSV file'
                ], 422);
            }
    
            // Validate file size (max 50MB)
            if ($file->getSize() > 50 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'File size too large. Maximum allowed size is 50MB'
                ], 422);
            }
    
            $importId = (string) Str::uuid();
    
            Log::info('Queueing product import with Maatwebsite Excel', [
                'import_id' => $importId,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'extension' => $extension
            ]);
    
            // Initialize cache progress to 0
            Cache::put($importId, 0);
    
            // Queue the import job
            Excel::queueImport(new UploadInventory($importId), $file)->onQueue('imports');

            // broadcast(new UpdateProductUpload($importId, 0));

    
            return response()->json([
                'success' => true,
                'message' => 'Import has been queued successfully',
                'import_id' => $importId
            ]);
    
        } catch (\Exception $e) {
            Log::error('Product import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate inventory status counts independently of pagination
     */
    private function calculateInventoryStatusCounts(Request $request)
    {
        $query = Inventory::query()
            ->with([
                'items.warehouse:id,name',
                'product:id,name,category_id,dosage_id',
                'product.category:id,name',
                'product.dosage:id,name'
            ])
            ->leftJoin('reorder_levels', 'inventories.product_id', '=', 'reorder_levels.product_id')
            ->addSelect('inventories.*')
            ->addSelect(DB::raw('COALESCE(reorder_levels.amc, 0) as amc'))
            ->addSelect(DB::raw('COALESCE(reorder_levels.reorder_level, 0) as reorder_level'));

        // Apply the same filters as the main query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('items', function($q) use($search) {
                $q->where('barcode', 'like', "%{$search}%");
                $q->orWhere('batch_number', 'like', "%{$search}%");
            })
            ->orWhereHas('product', function($q) use($search) {
                $q->where('name', 'like', "%{$search}%");
            });
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

        // Get all results without pagination for counting
        $allInventories = $query->get();

        $statusCounts = [
            'in_stock' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'soon_expiring' => 0,
            'expired' => 0,
        ];

        $now = now();
        foreach ($allInventories as $inventory) {
            $amc = (float) ($inventory->amc ?: 0);
            $reorderLevel = (float) ($inventory->reorder_level ?: 0);

            // Calculate total quantity for this inventory
            $totalQuantity = 0.0;
            $hasExpiredItems = false;
            $hasSoonExpiringItems = false;

            foreach ($inventory->items ?? [] as $item) {
                $totalQuantity += (float) ($item->quantity ?: 0);

                if ($item->expiry_date) {
                    if ($item->expiry_date < $now) {
                        $hasExpiredItems = true;
                    } elseif ($item->expiry_date <= $now->copy()->addDays(160)) {
                        $hasSoonExpiringItems = true;
                    }
                }
            }

            // Count at inventory level (not item level)
            if ($totalQuantity === 0.0) {
                $statusCounts['out_of_stock']++;
            } elseif ($reorderLevel > 0 && $totalQuantity <= (0.7 * $reorderLevel)) {
                // Low stock when total_on_hand <= 70% of reorder level
                $statusCounts['low_stock']++;
            } else {
                $statusCounts['in_stock']++;
            }

            // Count expiry status
            if ($hasExpiredItems) {
                $statusCounts['expired']++;
            } elseif ($hasSoonExpiringItems) {
                $statusCounts['soon_expiring']++;
            }
        }

        return $statusCounts;
    }

}
