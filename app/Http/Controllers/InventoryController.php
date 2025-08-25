<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Location;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Inertia\Inertia;
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

class InventoryController extends Controller
{
	public function index(Request $request)
	{
		try {
			// Base query with relationships
			$productQuery = Product::query()
				->with([
					'category:id,name',
					'dosage:id,name',
					'items.warehouse:id,name'
				])
				->addSelect([
					'reorder_level' => DB::raw('(
						SELECT CASE 
							WHEN COUNT(CASE WHEN wa.quantity > 0 THEN 1 END) < 3 THEN 0
							ELSE ROUND(
								(AVG(wa.quantity) * 3) + ((MAX(wa.quantity) - AVG(wa.quantity)) * 3), 
								2
							)
							END
						FROM warehouse_amcs wa
						WHERE wa.product_id = products.id
					)'),
					'amc' => DB::raw('(
						SELECT CASE 
							WHEN COUNT(CASE WHEN wa.quantity > 0 THEN 1 END) < 3 THEN 0
							ELSE ROUND(AVG(wa.quantity), 2)
							END
						FROM warehouse_amcs wa
						WHERE wa.product_id = products.id
					)')
				]);
	
			// Apply filters
			if ($request->filled('search')) {
				$search = $request->search;
				$productQuery->where(function($q) use ($search) {
					$q->where('products.name', 'like', "%{$search}%")
					  ->orWhereHas('items', function($sub) use ($search) {
						  $sub->where(function($w) use ($search) {
							  $w->where('barcode', 'like', "%{$search}%")
								->orWhere('batch_number', 'like', "%{$search}%");
						  });
					  });
				});
			}
	
			if ($request->filled('category')) {
				$productQuery->whereHas('category', fn($q) => $q->where('name', $request->category));
			}
			if ($request->filled('dosage')) {
				$productQuery->whereHas('dosage', fn($q) => $q->where('name', $request->dosage));
			}
			if ($request->filled('location')) {
				$productQuery->whereHas('items', fn($q) => $q->where('location', $request->location));
			}
			if ($request->filled('warehouse')) {
				$productQuery->whereHas('items.warehouse', fn($q) => $q->where('name', $request->warehouse));
			}
	
			// Status filter using same formula
			if ($request->filled('status')) {
				switch ($request->status) {
					case 'in_stock':
						$productQuery->whereHas('items', fn($q) => $q->where('quantity', '>', 0));
						break;
	
					case 'low_stock':
						$productQuery->whereRaw('
							(
								SELECT COALESCE(SUM(ii.quantity), 0)
								FROM inventory_items ii
								WHERE ii.product_id = products.id
							) <= (
								SELECT COALESCE((
									SELECT ((AVG(wa.quantity) * 3) + ((MAX(wa.quantity) - AVG(wa.quantity)) * 3)) * 1.3
									FROM warehouse_amcs wa
									WHERE wa.product_id = products.id
									AND wa.quantity > 0
									AND wa.month_year >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
									HAVING COUNT(*) >= 3
								), 0)
							)
						');
						break;
	
					case 'low_stock_reorder_level':
						$productQuery->whereRaw('
							(
								SELECT COALESCE(SUM(ii.quantity), 0)
								FROM inventory_items ii
								WHERE ii.product_id = products.id
							) <= (
								SELECT COALESCE((
									SELECT (AVG(wa.quantity) * 3) + ((MAX(wa.quantity) - AVG(wa.quantity)) * 3)
									FROM warehouse_amcs wa
									WHERE wa.product_id = products.id
									AND wa.quantity > 0
									AND wa.month_year >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
									HAVING COUNT(*) >= 3
								), 0)
							)
						');
						break;
	
					case 'out_of_stock':
						$productQuery->whereDoesntHave('items', fn($q) => $q->where('quantity', '>', 0));
						break;
				}
			}
	
			// Paginate
			$products = $productQuery->paginate(
				$request->input('per_page', 25),
				['*'],
				'page',
				$request->input('page', 1)
			)->withQueryString();
	
			$products->setPath(url()->current());
	

	
			// Filters data
			$categories = Category::orderBy('name')->pluck('name')->toArray();
			$dosages = Dosage::orderBy('name')->pluck('name')->toArray();
			$locations = Location::orderBy('location')->pluck('location')->toArray();
			$warehouses = Warehouse::orderBy('name')->pluck('name')->toArray();
	
			// Status counts
			$statusCounts = [
				[ 'status' => 'in_stock', 'count' => 0 ],
				[ 'status' => 'low_stock', 'count' => 0 ],
				[ 'status' => 'low_stock_reorder_level', 'count' => 0 ],
				[ 'status' => 'out_of_stock', 'count' => 0 ],
			];
	
			// Use optimized database queries to calculate status counts
			// Get total quantities for all products in one query
			$quantities = DB::table('products')
				->leftJoin('inventory_items', 'products.id', '=', 'inventory_items.product_id')
				->select('products.id', DB::raw('COALESCE(SUM(inventory_items.quantity), 0) as total_quantity'))
				->groupBy('products.id')
				->get()
				->keyBy('id');
			
			// Get reorder levels for all products using a simpler approach
			$reorderLevels = DB::table('products')
				->leftJoin('warehouse_amcs', 'products.id', '=', 'warehouse_amcs.product_id')
				->select('products.id')
				->selectRaw('
					CASE 
						WHEN COUNT(CASE WHEN warehouse_amcs.quantity > 0 THEN 1 END) < 3 THEN 0
						ELSE (
							SELECT ROUND(
								(AVG(quantity) * 3) + ((MAX(quantity) - AVG(quantity)) * 3), 
								2
							)
							FROM (
								SELECT quantity
								FROM warehouse_amcs wa2 
								WHERE wa2.product_id = products.id 
								AND wa2.quantity > 0
								ORDER BY month_year DESC
								LIMIT 10
							) as recent_consumption
						)
					END as reorder_level
				')
				->groupBy('products.id')
				->get()
				->keyBy('id');
	

	
			// Calculate status counts using the pre-calculated data
			foreach ($quantities as $productId => $quantityData) {
				$totalQuantity = $quantityData->total_quantity;
				$reorderLevel = $reorderLevels->get($productId)?->reorder_level ?? 0;
				

				
				if ($totalQuantity <= 0) {
					$statusCounts[3]['count']++; // out_of_stock
				} elseif ($reorderLevel <= 0) {
					// No reorder level set, default to in stock
					$statusCounts[0]['count']++; // in_stock
				} else {
					// Calculate the low stock threshold (reorder level + 30%)
					$lowStockThreshold = $reorderLevel * 1.3;
					
					if ($totalQuantity <= $reorderLevel) {
						// Items at or below reorder level (1 to 9,000 in your example)
						$statusCounts[2]['count']++; // low_stock_reorder_level
					} elseif ($totalQuantity <= $lowStockThreshold) {
						// Items between reorder level and reorder level + 30% (9,001 to 11,700 in your example)
						$statusCounts[1]['count']++; // low_stock
					} else {
						// Items above reorder level + 30% (above 11,700 in your example)
						$statusCounts[0]['count']++; // in_stock
					}
				}
			}
	
			return Inertia::render('Inventory/Index', [
				'inventories' => InventoryResource::collection($products),
				'inventoryStatusCounts' => $statusCounts,
				'filters' => $request->only(['search', 'per_page', 'page', 'category', 'dosage', 'status', 'location', 'warehouse']),
				'category' => $categories,
				'dosage' => $dosages,
				'locations' => $locations,
				'warehouses' => $warehouses,
			]);
	
		} catch (\Exception $e) {
			Log::error('[INVENTORY-ERROR] Error in index method: ' . $e->getMessage(), [
				'exception' => $e,
				'request' => $request->all()
			]);
	
			return back()->withErrors(['error' => 'An error occurred while loading inventory data.']);
		}
	}	

	/**
	 * Apply status filter to the product query
	 */
	protected function applyStatusFilter($query, $status)
	{
		return $query->whereHas('inventories.items', function($subQuery) use ($status) {
			$subQuery->where('inventory_items.quantity', '>', 0);
		});
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
	protected function calculateInventoryStatusCounts($productQuery, $request)
	{
		$statusCounts = [
			'in_stock' => 0,
			'low_stock' => 0,
			'out_of_stock' => 0
		];

		// Get total registered products for out of stock calculation
		$totalProducts = $productQuery->count();

		// Get products with inventory (for in stock calculation)
		$productsWithInventory = $productQuery->whereHas('inventories.items', function($query) {
			$query->where('inventory_items.quantity', '>=', 0);
		})->count();

		// Out of stock = total products - products with inventory
		$statusCounts['out_of_stock'] = $totalProducts - $productsWithInventory;

		// For the remaining counts, get products that have inventory and calculate their status
		$productsWithQuantity = $productQuery->whereHas('inventories.items', function($query) {
			$query->where('inventory_items.quantity', '>', 0);
		})->get();

		// Now calculate status for each product with inventory
		foreach ($productsWithQuantity as $product) {
			// Get total quantity for this product across all inventories
			$totalQty = $product->inventories->flatMap->items->sum('quantity');
			
			// Simplified logic without AMC calculations
			if ($totalQty > 0) {
				if ($totalQty > 10) { // Simple threshold for now
					// In Stock: quantity > 10
					$statusCounts['in_stock']++;
				} elseif ($totalQty <= 10) {
					// Low Stock: quantity <= 10
					$statusCounts['low_stock']++;
				}
			}
		}

		Log::info('Final status counts', [
			'status_counts' => $statusCounts
		]);

		// Transform to the format frontend expects: array of objects with status and count
		$formattedStatusCounts = collect($statusCounts)->map(function($count, $status) {
			return [
				'status' => $status,
				'count' => $count
			];
		})->values();

		return $formattedStatusCounts;
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
