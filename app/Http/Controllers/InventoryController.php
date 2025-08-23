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
use App\Models\WarehouseAmc;
use App\Events\InventoryUpdated;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UploadInventory;
use App\Services\InventoryAnalyticsService;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InventoryController extends Controller
{
	public function index(Request $request)
	{
        try {
            // Log incoming request parameters for debugging
            logger()->info('Inventory index request received', [
                'filters' => $request->only(['search', 'product_id', 'category', 'dosage', 'status', 'per_page', 'page', 'sort_by', 'sort_order']),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl()
            ]);
            
            // Product-first pagination so products with zero or no items still appear
            $productQuery = Product::query()
                ->select('products.id', 'products.name', 'products.category_id', 'products.dosage_id')
                ->groupBy('products.id', 'products.name', 'products.category_id', 'products.dosage_id')
                ->with(['category:id,name', 'dosage:id,name'])
                ->addSelect(DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM warehouse_amcs WHERE warehouse_amcs.product_id = products.id) as monthly_consumption'))
                ->addSelect(DB::raw('0 as amc')) // Will be calculated dynamically
                ->addSelect(DB::raw('0 as buffer_stock')) // Will be calculated dynamically
                ->addSelect(DB::raw('0 as reorder_level')); // Will be calculated dynamically

            // Default sort by product name for consistency
            $productQuery->orderBy('products.name', 'asc');

            if ($request->filled('search')) {
                $search = $request->search;
                $productQuery->where(function($q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                      ->orWhereExists(function($sub) use ($search) {
                          $sub->select(DB::raw(1))
                              ->from('inventories')
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
            $productsPaginator = $productQuery->paginate($perPage, ['products.*'], 'page', $page)
                ->withQueryString();
            $productsPaginator->setPath(url()->current());

            $productIds = collect($productsPaginator->items())->pluck('id')->all();

            // Debug: Log the products to check for duplicates
            Log::info('Products from paginator', [
                'total_products' => count($productsPaginator->items()),
                'unique_product_ids' => count(array_unique($productIds)),
                'product_ids' => $productIds,
                'duplicate_check' => array_count_values($productIds)
            ]);

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

            // Debug: Log the structure to understand what's happening
            Log::info('Inventory data structure', [
                'total_products' => count($productIds),
                'inventory_groups' => $existingInventories->map(function($group) {
                    return [
                        'product_id' => $group->first()->product_id,
                        'inventory_count' => $group->count(),
                        'total_items' => $group->sum(function($inv) {
                            return $inv->items ? $inv->items->count() : 0;
                        })
                    ];
                })->toArray()
            ]);

            // Ensure expiry dates are properly converted to Carbon instances
            foreach ($existingInventories as $productInventories) {
                foreach ($productInventories as $inventory) {
                    foreach ($inventory->items as $item) {
                        if ($item->expiry_date && !($item->expiry_date instanceof Carbon)) {
                            try {
                                $item->expiry_date = Carbon::parse($item->expiry_date);
                            } catch (\Exception $e) {
                                logger()->warning("Could not parse expiry date for item {$item->id}: {$item->expiry_date}");
                                $item->expiry_date = null;
                            }
                        }
                    }
                }
            }

            // Merge: ensure every product has at least one row
            $merged = collect();
            foreach ($productsPaginator->items() as $product) {
                // Calculate AMC using the new formula (for reorder level calculation only)
                $amcData = $this->calculateAMC($product->id);
                $amc = $amcData['amc'];
                $selectedMonths = $amcData['selected_months'];
                
                // Calculate Reorder Level using the new formula
                $reorderLevel = $this->calculateReorderLevel($amc, $selectedMonths);

                if (isset($existingInventories[$product->id]) && $existingInventories[$product->id]->isNotEmpty()) {
                    // Get the first inventory record for this product
                    $inventory = $existingInventories[$product->id]->first();
                    
                    // Consolidate all inventory items for this product into one collection
                    $allItems = collect();
                    foreach ($existingInventories[$product->id] as $inv) {
                        if ($inv->items) {
                            $allItems = $allItems->merge($inv->items);
                        }
                    }
                    
                    // Set the consolidated items
                    $inventory->setRelation('items', $allItems);
                    $inventory->setAttribute('reorder_level', $reorderLevel);
                    $inventory->setRelation('product', $inventory->product->loadMissing('category:id,name', 'dosage:id,name'));
                    $merged->push($inventory);
                    
                    Log::info("Product {$product->id} ({$product->name}) merged", [
                        'inventory_records' => $existingInventories[$product->id]->count(),
                        'total_items' => $allItems->count(),
                        'amc' => $amc,
                        'reorder_level' => $reorderLevel
                    ]);
                } else {
                    $placeholder = new Inventory();
                    $placeholder->setAttribute('id', -$product->id); // synthetic id to keep rows unique
                    $placeholder->setAttribute('product_id', $product->id);
                    $placeholder->setAttribute('reorder_level', $reorderLevel);
                    $placeholder->setRelation('product', $product);

                    // Create only one placeholder item
                    $item = new InventoryItem();
                    $item->setAttribute('id', -$product->id);
                    $item->setAttribute('product_id', $product->id);
                    $item->setAttribute('quantity', 0);
                    $item->setAttribute('batch_number', null);
                    $item->setAttribute('barcode', null);
                    $item->setAttribute('location', null);
                    $item->setAttribute('expiry_date', null);
                    $item->setAttribute('uom', null);
                    $item->setRelation('warehouse', null);

                    $placeholder->setRelation('items', collect([$item]));
                    $merged->push($placeholder);
                    
                    Log::info("Product {$product->id} ({$product->name}) created placeholder", [
                        'amc' => $amc,
                        'reorder_level' => $reorderLevel
                    ]);
                }
            }
            
            Log::info("Final merged data", [
                'total_merged_records' => $merged->count(),
                'products_with_inventory' => $merged->where('id', '>', 0)->count(),
                'placeholder_products' => $merged->where('id', '<', 0)->count(),
                'unique_product_ids' => $merged->pluck('product_id')->unique()->count(),
                'duplicate_product_check' => $merged->pluck('product_id')->duplicates()->values()->toArray()
            ]);

            // Final safeguard: ensure no duplicate products in the merged collection
            $merged = $merged->unique('product_id')->values();
            
            Log::info("After deduplication", [
                'total_merged_records' => $merged->count(),
                'unique_product_ids' => $merged->pluck('product_id')->unique()->count()
            ]);

            // Apply sorting to the merged data after creating the collection
            if ($request->filled('sort_by')) {
                $sortBy = $request->input('sort_by');
                $sortOrder = $request->input('sort_order', 'asc');
                
                logger()->info("Applying sorting: {$sortBy} {$sortOrder}");
                
                // Convert to array for better sorting control
                $mergedArray = $merged->toArray();
                
                usort($mergedArray, function ($a, $b) use ($sortBy, $sortOrder) {
                    $aValue = null;
                    $bValue = null;
                    
                    switch ($sortBy) {
                        case 'name':
                            $aValue = strtolower($a['product']['name'] ?? '');
                            $bValue = strtolower($b['product']['name'] ?? '');
                            break;
                        case 'quantity':
                            // Sort by the minimum quantity of any item
                            $aQuantities = collect($a['items'])->where('quantity', '!=', null)->pluck('quantity');
                            $bQuantities = collect($b['items'])->where('quantity', '!=', null)->pluck('quantity');
                            $aValue = $aQuantities->isEmpty() ? 0 : $aQuantities->min();
                            $bValue = $bQuantities->isEmpty() ? 0 : $bQuantities->min();
                            break;
                        case 'expiry_date':
                            // Sort by the earliest expiry date
                            $aEarliest = null;
                            $bEarliest = null;
                            
                            foreach ($a['items'] as $item) {
                                if (isset($item['expiry_date']) && $item['expiry_date']) {
                                    try {
                                        $date = Carbon::parse($item['expiry_date']);
                                        if ($aEarliest === null || $date->lt($aEarliest)) {
                                            $aEarliest = $date;
                                        }
                                    } catch (\Exception $e) {
                                        // Skip invalid dates
                                    }
                                }
                            }
                            
                            foreach ($b['items'] as $item) {
                                if (isset($item['expiry_date']) && $item['expiry_date']) {
                                    try {
                                        $date = Carbon::parse($item['expiry_date']);
                                        if ($bEarliest === null || $date->lt($bEarliest)) {
                                            $bEarliest = $date;
                                        }
                                    } catch (\Exception $e) {
                                        // Skip invalid dates
                                    }
                                }
                            }
                            
                            $aValue = $aEarliest ? $aEarliest->timestamp : PHP_INT_MAX;
                            $bValue = $bEarliest ? $bEarliest->timestamp : PHP_INT_MAX;
                            break;
                        default:
                            $aValue = strtolower($a['product']['name'] ?? '');
                            $bValue = strtolower($b['product']['name'] ?? '');
                            break;
                    }
                    
                    // Handle null values
                    if ($aValue === null) $aValue = '';
                    if ($bValue === null) $bValue = '';
                    
                    // Compare values
                    if ($aValue === $bValue) return 0;
                    
                    if ($sortOrder === 'asc') {
                        return $aValue < $bValue ? -1 : 1;
                    } else {
                        return $aValue > $bValue ? -1 : 1;
                    }
                });
                
                // Convert back to collection
                $merged = collect($mergedArray);
                
                logger()->info("Sorting completed. First few items: " . $merged->take(3)->map(fn($inv) => $inv['product']['name'])->join(', '));
            }

            // Apply status filters to the merged data
            if ($request->filled('status')) {
                try {
                    logger()->info('Applying status filter: ' . $request->status . ' to ' . $merged->count() . ' items');
                    
                    // Log sample data structure for debugging
                    if ($merged->count() > 0) {
                        $sampleInventory = $merged->first();
                        logger()->info('Sample inventory structure:', [
                            'product_name' => $sampleInventory->product->name ?? 'N/A',
                            'items_count' => $sampleInventory->items ? $sampleInventory->items->count() : 0,
                            'first_item_quantity' => $sampleInventory->items && $sampleInventory->items->count() > 0 ? $sampleInventory->items->first()->quantity : 'N/A',
                            'reorder_level' => $sampleInventory->reorder_level ?? 'N/A'
                        ]);
                    }
                    
                    $merged = $merged->filter(function ($inventory) use ($request) {
                        try {
                            $totalQuantity = $inventory->items->sum('quantity');
                            $reorderLevel = (float) ($inventory->reorder_level ?? 0);
                            
                            // Log the values being used for filtering
                            logger()->info("Filtering inventory: {$inventory->product->name}, TotalQty: {$totalQuantity}, ReorderLevel: {$reorderLevel}");
                            
                            switch ($request->status) {
                                case 'in_stock':
                                    // Items that are in stock (total quantity > 0)
                                    $result = $totalQuantity > 0;
                                    logger()->info("Product {$inventory->product->name}: Qty={$totalQuantity}, InStock={$result} (qty > 0)");
                                    return $result;
                                
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
                    
                    // Log sample of filtered results for debugging
                    if ($merged->count() > 0) {
                        $filteredSample = $merged->take(3);
                        logger()->info('Sample filtered results:', $filteredSample->map(function($inv) {
                            $totalQty = $inv->items->sum('quantity');
                            $reorderLevel = (float) ($inv->reorder_level ?? 0);
                            return [
                                'product_name' => $inv->product->name,
                                'total_quantity' => $totalQty,
                                'reorder_level' => $reorderLevel,
                                'status' => $request->status
                            ];
                        })->toArray());
                    }
                } catch (\Exception $e) {
                    logger()->error('Error applying status filter: ' . $e->getMessage());
                    // If filtering fails, return all items
                    $merged = $merged;
                }
            }

            logger()->info('Final merged data count: ' . $merged->count() . ', sample products: ' . $merged->take(3)->map(fn($inv) => $inv->product->name)->join(', '));

            // Build paginator compatible with the frontend
            $filteredCount = $merged->count();
            
            logger()->info('Building paginator - filteredCount: ' . $filteredCount . ', perPage: ' . $perPage . ', page: ' . $page);
            
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
                logger()->info('Created empty paginator');
            } else {
                $inventories = new \Illuminate\Pagination\LengthAwarePaginator(
                    $merged->values(),
                    $filteredCount,
                    $perPage,
                    $page,
                    ['path' => $productsPaginator->path(), 'pageName' => $productsPaginator->getPageName()]
                );
            }

            // Calculate status counts independently of pagination
            $statusCounts = $this->calculateInventoryStatusCounts($request);


            return Inertia::render('Inventory/Index', [
                'inventories' => InventoryResource::collection($inventories),
                'inventoryStatusCounts' => collect($statusCounts)->map(fn($count, $status) => ['status' => $status, 'count' => $count]),
                'products'   => Product::select('id', 'name')->get(),
                'warehouses' => Warehouse::pluck('name')->toArray(),
                'filters'    => $request->only(['search', 'product_id', 'category', 'dosage', 'status', 'per_page', 'page', 'sort_by', 'sort_order']),
                'category'   => Category::pluck('name')->toArray(),
                'dosage'     => Dosage::pluck('name')->toArray(),
                'locations'  => Location::pluck('location')->toArray(),
                'errors'     => null,
            ]);
        } catch (\Throwable $th) {
        	logger()->error('[INVENTORY-ERROR] Error in index method: ' . $th->getMessage());
            logger()->error('[INVENTORY-ERROR] Stack trace: ' . $th->getTraceAsString());
            logger()->error('[INVENTORY-ERROR] Request data: ' . json_encode($request->all()));
            logger()->error('[INVENTORY-ERROR] User: ' . (auth()->user() ? auth()->user()->id : 'not authenticated'));
            
            // Return a safe fallback response
            return Inertia::render('Inventory/Index', [
                'inventories' => InventoryResource::collection(new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 25, 1)),
                'inventoryStatusCounts' => collect([]),
                'products'   => collect([]),
                'warehouses' => [],
                'filters'    => $request->only(['search', 'product_id', 'category', 'dosage', 'status', 'per_page', 'page']),
                'category'   => [],
                'dosage'     => [],
                'locations'  => [],
                'errors'     => 'An error occurred while loading inventory data: ' . $th->getMessage(),
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
			->addSelect('inventories.*');

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
			$query->whereHas('product.dosage', fn($q) => $q->where('name', $request->category));
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
			// Calculate AMC, Buffer Stock, and Reorder Level dynamically
			$amcData = $this->calculateAMC($inventory->product_id);
			$amc = $amcData['amc'];
			$selectedMonths = $amcData['selected_months'];
			$bufferStock = $this->calculateBufferStock($amc, $selectedMonths);
			$reorderLevel = $this->calculateReorderLevel($amc, $selectedMonths);

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
			// Simplified logic: in_stock = quantity > 0, low_stock = quantity > 0 but <= reorder level
			if ($totalQuantity > 0) {
				$statusCounts['in_stock']++;
				
				// Check if it's also low stock (when reorder level is set)
				if ($reorderLevel > 0 && $totalQuantity <= $reorderLevel) {
					$statusCounts['low_stock']++;
				}
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

    /**
     * Calculate Average Monthly Consumption (AMC) for a product.
     * This method uses the new percentage deviation screening formula.
     *
     * @param int $productId
     * @return array ['amc' => float, 'selected_months' => array]
     */
    private function calculateAMC(int $productId): array
    {
        try {
            // Get all consumption values for the product from warehouse_amcs table
            $consumptionsWithMonth = WarehouseAmc::where('product_id', $productId)
                ->where('quantity', '>', 0) // Only consider positive consumption values
                ->orderBy('month_year', 'desc') // Most recent first (bottom to top)
                ->get(['month_year', 'quantity']);

            // If we have less than 3 values, return 0
            if ($consumptionsWithMonth->count() < 3) {
                return ['amc' => 0, 'selected_months' => []];
            }

            // Extract quantities and months
            $quantities = $consumptionsWithMonth->pluck('quantity')->values();
            $months = $consumptionsWithMonth->pluck('month_year')->values();
            
            Log::info("AMC calculation for product {$productId}", [
                'quantities' => $quantities->toArray(),
                'months' => $months->toArray()
            ]);

            // Start with the 3 most recent months
            $selectedMonths = [];
            $passedMonths = [];
            $failedMonths = [];
            
            // Initial selection: 3 most recent months
            for ($i = 0; $i < 3; $i++) {
                $selectedMonths[] = [
                    'month' => $months[$i],
                    'quantity' => $quantities[$i]
                ];
            }
            
            $attempt = 1;
            $maxAttempts = 10; // Prevent infinite loops
            
            while ($attempt <= $maxAttempts) {
                Log::info("AMC calculation attempt {$attempt}", [
                    'selected_months' => $selectedMonths,
                    'passed_months' => $passedMonths,
                    'failed_months' => $failedMonths
                ]);
                
                // Calculate average of selected months
                $average = collect($selectedMonths)->avg('quantity');
                
                Log::info("Calculated average", ['average' => $average]);
                
                // Check each month's deviation
                $allPassed = true;
                $newPassedMonths = [];
                $newFailedMonths = [];
                
                foreach ($selectedMonths as $monthData) {
                    $quantity = $monthData['quantity'];
                    $deviation = abs($quantity - $average) / $average * 100;
                    
                    Log::info("Checking month {$monthData['month']}", [
                        'quantity' => $quantity,
                        'deviation_percentage' => round($deviation, 2)
                    ]);
                    
                    if ($deviation <= 70) {
                        // Month passed screening
                        $newPassedMonths[] = $monthData;
                        Log::info("Month {$monthData['month']} PASSED screening");
                    } else {
                        // Month failed screening
                        $newFailedMonths[] = $monthData;
                        $allPassed = false;
                        Log::info("Month {$monthData['month']} FAILED screening");
                    }
                }
                
                // Add newly passed months to the global passed list
                foreach ($newPassedMonths as $monthData) {
                    if (!collect($passedMonths)->contains('month', $monthData['month'])) {
                        $passedMonths[] = $monthData;
                    }
                }
                
                // Add newly failed months to the global failed list
                foreach ($newFailedMonths as $monthData) {
                    if (!collect($failedMonths)->contains('month', $monthData['month'])) {
                        $failedMonths[] = $monthData;
                    }
                }
                
                // If all months passed, we're done
                if ($allPassed) {
                    Log::info("All months passed screening!", [
                        'final_selected_months' => $selectedMonths,
                        'final_passed_months' => $passedMonths
                    ]);
                    break;
                }
                
                // If we have 3 or more passed months, use them
                if (count($passedMonths) >= 3) {
                    $selectedMonths = array_slice($passedMonths, 0, 3);
                    Log::info("Using 3 passed months", ['selected_months' => $selectedMonths]);
                    break;
                }
                
                // Need to reselect months including passed ones
                $newSelection = [];
                
                // First, include all passed months
                foreach ($passedMonths as $monthData) {
                    $newSelection[] = $monthData;
                }
                
                // Then add more months from the original list until we have 3
                $monthIndex = 0;
                while (count($newSelection) < 3 && $monthIndex < count($quantities)) {
                    $monthData = [
                        'month' => $months[$monthIndex],
                        'quantity' => $quantities[$monthIndex]
                    ];
                    
                    // Only add if not already in selection and not in failed months
                    $alreadySelected = collect($newSelection)->contains('month', $monthData['month']);
                    $isFailed = collect($failedMonths)->contains('month', $monthData['month']);
                    
                    if (!$alreadySelected && !$isFailed) {
                        $newSelection[] = $monthData;
                    }
                    
                    $monthIndex++;
                }
                
                // Update selected months for next iteration
                $selectedMonths = $newSelection;
                
                Log::info("Reselected months for next attempt", [
                    'new_selection' => $selectedMonths
                ]);
                
                $attempt++;
            }
            
            // Calculate final AMC
            if (count($selectedMonths) >= 3) {
                $amc = collect($selectedMonths)->avg('quantity');
                $result = round($amc, 2);
                
                Log::info("AMC calculation completed", [
                    'product_id' => $productId,
                    'final_selected_months' => $selectedMonths,
                    'amc' => $result
                ]);
                
                return ['amc' => $result, 'selected_months' => $selectedMonths];
            } else {
                Log::warning("Could not find 3 suitable months for AMC calculation", [
                    'product_id' => $productId,
                    'selected_months_count' => count($selectedMonths)
                ]);
                return ['amc' => 0, 'selected_months' => []];
            }

        } catch (\Exception $e) {
            Log::error('Error calculating AMC for product ' . $productId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['amc' => 0, 'selected_months' => []];
        }
    }

    /**
     * Calculate Buffer Stock for a product.
     * Buffer Stock = (Max AMC - AMC) × 3
     *
     * @param float $amc
     * @param array $selectedMonths
     * @return float
     */
    private function calculateBufferStock(float $amc, array $selectedMonths = []): float
    {
        if (empty($selectedMonths)) {
            return 0;
        }
        
        // Find the maximum quantity from the selected months (Max AMC)
        $maxQuantity = max(array_column($selectedMonths, 'quantity'));
        
        // Calculate buffer stock: (Max AMC - AMC) × 3
        $bufferStock = ($maxQuantity - $amc) * 3;
        
        return round(max(0, $bufferStock), 2); // Ensure non-negative value
    }

    /**
     * Calculate Reorder Level for a product.
     * Reorder Level = (AMC × 3) + Buffer Stock
     *
     * @param float $amc
     * @param array $selectedMonths
     * @return float
     */
    private function calculateReorderLevel(float $amc, array $selectedMonths = []): float
    {
        $leadTime = 3; // Lead Time is 3 months
        $bufferStock = $this->calculateBufferStock($amc, $selectedMonths);
        return round(($amc * $leadTime) + $bufferStock, 2);
    }

    /**
     * Check if an item needs reorder action (out of stock)
     *
     * @param mixed $item
     * @return bool
     */
    private function needsReorderAction($item): bool
    {
        // If item doesn't exist or has zero quantity, it needs reorder
        if (!$item || !isset($item->quantity) || (float) $item->quantity <= 0) {
            return true;
        }
        return false;
	}
}
