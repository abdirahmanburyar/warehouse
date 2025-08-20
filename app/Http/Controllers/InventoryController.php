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
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Events\InventoryEvent;
use Illuminate\Support\Facades\Event;
use App\Models\IssueQuantityReport;
use App\Models\IssueQuantityItem;
use App\Models\ReorderLevel;
use App\Events\InventoryUpdated;
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
            // Product-first pagination so products with zero or no items still appear
            $productQuery = Product::query()
                ->select('products.id', 'products.name', 'products.category_id', 'products.dosage_id')
                ->with(['category:id,name', 'dosage:id,name'])
                ->leftJoin('reorder_levels', 'products.id', '=', 'reorder_levels.product_id')
                ->addSelect(DB::raw('COALESCE(reorder_levels.amc, 0) as amc'))
                ->addSelect(DB::raw('COALESCE(reorder_levels.reorder_level, (reorder_levels.amc * NULLIF(reorder_levels.lead_time, 0)), ROUND(COALESCE(reorder_levels.amc, 0) * 6)) as reorder_level'));

            if ($request->filled('search')) {
                $search = $request->search;
                $productQuery->where(function($q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                      ->orWhereExists(function($sub) use ($search) {
                          $sub->from('inventories')
                              ->join('inventory_items', 'inventories.id', '=', 'inventory_items.inventory_id')
                              ->whereColumn('inventories.product_id', 'products.id')
                              ->where(function($w) use ($search){
                                  $w->where('inventory_items.barcode', 'like', "%{$search}%")
                                    ->orWhere('inventory_items.batch_number', 'like', "%{$search}%");
                              });
                      });
                });
            }

            if ($request->filled('product_id')) {
                $productQuery->where('products.id', $request->product_id);
            }

            if ($request->filled('category')) {
                $productQuery->whereHas('category', fn($q) => $q->where('name', $request->category));
            }

            if ($request->filled('dosage')) {
                $productQuery->whereHas('dosage', fn($q) => $q->where('name', $request->dosage));
            }

            $perPage = $request->input('per_page', 25);
            $page = $request->input('page', 1);
            $productsPaginator = $productQuery->paginate($perPage, ['products.*', 'reorder_levels.amc', 'reorder_levels.reorder_level'], 'page', $page)
                ->withQueryString();
            $productsPaginator->setPath(url()->current());

            $productIds = collect($productsPaginator->items())->pluck('id')->all();

            // Load existing inventories (and items) for products on this page
            $existingInventories = Inventory::query()
                ->with([
                    'items.warehouse:id,name',
                    'product:id,name,category_id,dosage_id',
                    'product.category:id,name',
                    'product.dosage:id,name'
                ])
                ->whereIn('product_id', $productIds)
                ->get()
                ->groupBy('product_id');

            // Merge: ensure every product has at least one row
            $merged = collect();
            foreach ($productsPaginator->items() as $product) {
                $amc = (float) ($product->amc ?? 0);
                $reorderLevel = (float) ($product->reorder_level ?? round($amc * 6));

                if (isset($existingInventories[$product->id]) && $existingInventories[$product->id]->isNotEmpty()) {
                    $inventory = $existingInventories[$product->id]->first();
                    $inventory->setAttribute('amc', $amc);
                    $inventory->setAttribute('reorder_level', $reorderLevel);
                    $inventory->setRelation('product', $inventory->product->loadMissing('category:id,name', 'dosage:id,name'));
                    $merged->push($inventory);
                } else {
                    $placeholder = new Inventory();
                    $placeholder->setAttribute('id', -$product->id); // synthetic id to keep rows unique
                    $placeholder->setAttribute('product_id', $product->id);
                    $placeholder->setAttribute('amc', $amc);
                    $placeholder->setAttribute('reorder_level', $reorderLevel);
                    $placeholder->setRelation('product', $product);

                    $item = new InventoryItem();
                    $item->setAttribute('id', -$product->id);
                    $item->setAttribute('product_id', $product->id);
                    $item->setAttribute('quantity', 0);
                    $item->setAttribute('batch_number', null);
                    $item->setAttribute('barcode', null);
                    $item->setAttribute('location', null);
                    $item->setAttribute('expiry_date', null);
                    $item->setRelation('warehouse', null);

                    $placeholder->setRelation('items', collect([$item]));
                    $merged->push($placeholder);
                }
            }

            // Apply status filters to the merged data
            if ($request->filled('status')) {
                try {
                    logger()->info('Applying status filter: ' . $request->status . ' to ' . $merged->count() . ' items');
                    
                    $merged = $merged->filter(function ($inventory) use ($request) {
                        try {
                            $totalQuantity = $inventory->items->sum('quantity');
                            $reorderLevel = (float) ($inventory->reorder_level ?? 0);
                            
                            switch ($request->status) {
                                case 'reorder_level':
                                    // Items that need reorder (total quantity <= 70% of reorder level)
                                    $result = $reorderLevel > 0 && $totalQuantity <= ($reorderLevel * 0.7);
                                    logger()->info("Product {$inventory->product->name}: Qty={$totalQuantity}, ReorderLevel={$reorderLevel}, NeedsReorder={$result}");
                                    return $result;
                                
                                case 'low_stock':
                                    // Items that are low stock (total quantity > 0 but <= reorder level)
                                    $result = $totalQuantity > 0 && $totalQuantity <= $reorderLevel;
                                    logger()->info("Product {$inventory->product->name}: Qty={$totalQuantity}, ReorderLevel={$reorderLevel}, LowStock={$result}");
                                    return $result;
                                
                                case 'out_of_stock':
                                    // Items that are out of stock (total quantity = 0)
                                    $result = $totalQuantity === 0;
                                    logger()->info("Product {$inventory->product->name}: Qty={$totalQuantity}, OutOfStock={$result}");
                                    return $result;
                                
                                default:
                                    return true;
                            }
                        } catch (\Exception $e) {
                            logger()->error('Error filtering inventory item: ' . $e->getMessage());
                            return false;
                        }
                    });
                    
                    logger()->info('After status filtering: ' . $merged->count() . ' items remain');
                } catch (\Exception $e) {
                    logger()->error('Error applying status filter: ' . $e->getMessage());
                    // If filtering fails, return all items
                    $merged = $merged;
                }
            }

            // Build paginator compatible with the frontend
            $filteredCount = $merged->count();
            
            // Ensure we always have a valid response structure
            if ($filteredCount === 0) {
                // Create an empty paginator when no results
                $inventories = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect([]),
                    0,
                    $perPage,
                    $page,
                    ['path' => $productsPaginator->path(), 'pageName' => $productsPaginator->getPageName()]
                );
            } else {
                $inventories = new \Illuminate\Pagination\LengthAwarePaginator(
                    $merged->values(),
                    $filteredCount,
                    $productsPaginator->perPage(),
                    $productsPaginator->currentPage(),
                    ['path' => $productsPaginator->path(), 'pageName' => $productsPaginator->getPageName()]
                );
            }

            // Calculate status counts independently of pagination
            $statusCounts = $this->calculateInventoryStatusCounts($request);

            logger()->info('Final response - inventories count: ' . $inventories->count() . ', total: ' . $inventories->total() . ', status filter: ' . ($request->status ?? 'none'));

            return Inertia::render('Inventory/Index', [
                'inventories' => InventoryResource::collection($inventories),
                'inventoryStatusCounts' => collect($statusCounts)->map(fn($count, $status) => ['status' => $status, 'count' => $count]),
                'products'   => Product::select('id', 'name')->get(),
                'warehouses' => Warehouse::pluck('name')->toArray(),
                'filters'    => $request->only(['search', 'product_id', 'category', 'dosage', 'status', 'per_page', 'page']),
                'category'   => Category::pluck('name')->toArray(),
                'dosage'     => Dosage::pluck('name')->toArray(),
                'locations'  => Location::pluck('location')->toArray(),
                'errors'     => null,
            ]);
        } catch (\Throwable $th) {
        	logger()->error('[INVENTORY-ERROR] Error in index method: ' . $th->getMessage());
            logger()->error('[INVENTORY-ERROR] Stack trace: ' . $th->getTraceAsString());
            
            // Return a safe fallback response
            return Inertia::render('Inventory/Index', [
                'inventories' => new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 25, 1),
                'inventoryStatusCounts' => collect([]),
                'products'   => collect([]),
                'warehouses' => [],
                'filters'    => $request->only(['search', 'product_id', 'category', 'dosage', 'status', 'per_page', 'page']),
                'category'   => [],
                'dosage'     => [],
                'locations'  => [],
                'errors'     => 'An error occurred while loading inventory data. Please try again.',
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
			->addSelect(DB::raw('COALESCE(reorder_levels.reorder_level, (reorder_levels.amc * NULLIF(reorder_levels.lead_time, 0)), ROUND(COALESCE(reorder_levels.amc, 0) * 6)) as reorder_level'));

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
			'in_stock' => 0,          // number of products with sufficient stock
			'low_stock' => 0,         // number of products at/below 70% of reorder level
			'out_of_stock' => 0,      // number of inventory ITEMS that are zero-quantity
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
			$zeroQuantityItems = 0;

			foreach ($inventory->items ?? [] as $item) {
				$qty = (float) ($item->quantity ?: 0);
				$totalQuantity += $qty;
				if ($qty === 0.0) {
					$zeroQuantityItems++;
				}

				if ($item->expiry_date) {
					if ($item->expiry_date < $now) {
						$hasExpiredItems = true;
					} elseif ($item->expiry_date <= $now->copy()->addDays(160)) {
						$hasSoonExpiringItems = true;
					}
				}
			}

			// Product-level status for in-stock/low-stock
			if ($reorderLevel > 0 && $totalQuantity <= (0.7 * $reorderLevel)) {
				// Low stock when total_on_hand <= 70% of reorder level
				$statusCounts['low_stock']++;
			} else {
				$statusCounts['in_stock']++;
			}

			// Do not calculate out_of_stock here; we'll do a robust item-level count below
			
			// Count expiry status
			if ($hasExpiredItems) {
				$statusCounts['expired']++;
			} elseif ($hasSoonExpiringItems) {
				$statusCounts['soon_expiring']++;
			}
		}

        // Robust Out-of-Stock (product-level): number of products whose total quantity <= 0
        // Build the filtered product set similarly to the index() product-first filters
        $productFilterQuery = Product::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $productFilterQuery->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhereExists(function($sub) use ($search) {
                      $sub->from('inventories')
                          ->join('inventory_items', 'inventories.id', '=', 'inventory_items.inventory_id')
                          ->whereColumn('inventories.product_id', 'products.id')
                          ->where(function($w) use ($search){
                              $w->where('inventory_items.barcode', 'like', "%{$search}%")
                                ->orWhere('inventory_items.batch_number', 'like', "%{$search}%");
                          });
                  });
            });
        }
        if ($request->filled('product_id')) {
            $productFilterQuery->where('products.id', $request->product_id);
        }
        if ($request->filled('category')) {
            $productFilterQuery->whereHas('category', fn($q) => $q->where('name', $request->category));
        }
        if ($request->filled('dosage')) {
            $productFilterQuery->whereHas('dosage', fn($q) => $q->where('name', $request->dosage));
        }

        $filteredProductIds = $productFilterQuery->pluck('products.id');
        if ($filteredProductIds->isNotEmpty()) {
            // Count products with positive total quantity; avoid duplicate id columns by selecting product_id only
            $positiveTotals = Inventory::query()
                ->leftJoin('inventory_items', 'inventories.id', '=', 'inventory_items.inventory_id')
                ->whereIn('inventories.product_id', $filteredProductIds)
                ->select('inventories.product_id')
                ->groupBy('inventories.product_id')
                ->havingRaw('COALESCE(SUM(COALESCE(inventory_items.quantity,0)),0) > 0')
                ->get()
                ->count();

            $statusCounts['out_of_stock'] = $filteredProductIds->count() - $positiveTotals;
        }

		return $statusCounts;
	}
}
