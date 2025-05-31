<?php

namespace App\Http\Controllers;

use App\Http\Resources\EligibleItemResource;
use App\Models\EligibleItem;
use App\Models\Product;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EligibleItemController extends Controller
{
    public function index(Request $request)
    {
        $query = EligibleItem::query()->with('product');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('facility_type')) {
            $type = $request->input('facility_type');
            $query->where('facility_type', 'like', "%{$type}%");
        }
        
        $query = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
        ->withQueryString();
        $query->setPath(url()->current()); // Force Laravel to use full URLs

        return Inertia::render('Product/Eligible/Index', [
            'eligibleItems' => EligibleItemResource::collection($query),
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'per_page','facility_type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Product/Eligible/Create', [
            'products' => Product::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'facility_types' => 'required|array|min:1'
            ]);

            foreach ($request->facility_types as $type) {
                foreach ($request->products as $product) {
                    $eligibleItem = EligibleItem::firstOrCreate([
                        'product_id' => $product['product_id'],
                        'facility_type' => $type,
                    ]);
                }
            }   

            return response()->json("Created eligible items successfully", 200);

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:eligible_items,id',
                'product_id' => 'required|exists:products,id',
                'facility_type' => 'required'
            ]);

            $eligibleItem = EligibleItem::updateOrCreate([
                'id' => $request->id
            ],[
                'product_id' => $request->product_id,
                'facility_type' => $request->facility_type,
            ]);
           
            return response()->json("Successfully updated eligible item", 200);

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function edit($eligibleItem)
    {
        $eligible = EligibleItem::with('product')->find($eligibleItem);
        return inertia('Product/Eligible/Edit', [
            'eligible' => $eligible,
            'products' => Product::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function destroy(EligibleItem $eligible)
    {
        try {
            $eligible->delete();

            return response()->json('Eligible item deleted successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
    /**
     * Import eligible items from Excel file
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.item_description' => 'required|string',
                'items.*.facility_type' => 'required|string',
            ]);
            
            $items = $request->input('items');
            
            $facilityTypes = ["Regional Hospital", "District Hospital", "Health Centre", "Primary Health Unit"];
            $successCount = 0;
            $errorItems = [];
            
            DB::beginTransaction();
            
            foreach ($items as $item) {
                // Check if facility type is valid
                if (!in_array($item['facility_type'], $facilityTypes)) {
                    $errorItems[] = ["item" => $item['item_description'], "error" => "Invalid facility type: {$item['facility_type']}"];
                    continue;
                }
                
                // Find product by name (case-insensitive exact match)
                logger()->info('Looking for product', ['item_description' => $item['item_description']]);
                $product = Product::whereRaw('LOWER(name) = ?', [strtolower($item['item_description'])])->first();
                
                // If not found, try with a more flexible search
                if (!$product) {
                    logger()->info('Trying more flexible search');
                    $product = Product::where('name', 'like', "{$item['item_description']}")->first();
                }
                
                if (!$product) {
                    // Debug: Log products with similar names to help troubleshoot
                    $similarProducts = Product::where('name', 'like', "%{$item['item_description']}%")->get();
                    logger()->warning('Product not found', [
                        'searched_for' => $item['item_description'],
                        'similar_products' => $similarProducts->pluck('name')
                    ]);
                    
                    // If strict validation is required, we can break the entire process
                    DB::rollBack();
                    return response()->json([
                        'error' => "Product not found: {$item['item_description']}",
                        'message' => 'Import failed. Please ensure all products exist in the system.'
                    ], 422);
                }
                
                logger()->info('Product found', ['product_id' => $product->id, 'product_name' => $product->name]);
                
                // Check if eligible item already exists
                $exists = EligibleItem::where('product_id', $product->id)
                    ->where('facility_type', $item['facility_type'])
                    ->exists();
                
                logger()->info('Checking if eligible item exists', [
                    'product_id' => $product->id, 
                    'facility_type' => $item['facility_type'],
                    'exists' => $exists
                ]);
                    
                if (!$exists) {
                    $eligibleItem = EligibleItem::create([
                        'product_id' => $product->id,
                        'facility_type' => $item['facility_type']
                    ]);
                    logger()->info('Created new eligible item', ['id' => $eligibleItem->id]);
                    $successCount++;
                } else {
                    logger()->info('Eligible item already exists, skipping');
                }
            }
            
            DB::commit();
            logger()->info('Import completed successfully', ['success_count' => $successCount, 'error_count' => count($errorItems)]);
            
            if (count($errorItems) > 0) {
                logger()->info('Import completed with errors', ['errors' => $errorItems]);
                return response()->json([
                    'message' => "Imported {$successCount} eligible items with " . count($errorItems) . " errors",
                    'errors' => $errorItems
                ], 200);
            }
            
            return response()->json([
                'message' => "Successfully imported {$successCount} eligible items"
            ], 200);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('Error during import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
