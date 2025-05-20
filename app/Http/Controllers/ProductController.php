<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use App\Models\EligibleItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DosageResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Dosage filter
        if ($request->filled('dosage_id')) {
            $query->where('dosage_id', $request->dosage_id);
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $query->with('dosage','category');

        // Handle pagination parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        
        // Get paginated products
        $products = $query->paginate(
            perPage: $perPage,
            page: $page
        );

        // Transform the paginator instance
        $products->through(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'dosage' => $product->dosage ? [
                    'id' => $product->dosage->id,
                    'name' => $product->dosage->name
                ] : null,
                'reorder_level' => $product->reorder_level
            ];
        });

        // Ensure proper URL generation for pagination
        $products->withPath(route('products.index'));
        $products->appends($request->except('page'));

        // Add current page info to filters
        $filters = $request->all();
        $filters['page'] = $page;
        $filters['per_page'] = $perPage;

        return Inertia::render('Product/Index', [
            'products' => ProductResource::collection($products),
            'categories' => CategoryResource::collection(Category::all()),
            'dosages' => DosageResource::collection(Dosage::all()),
            'filters' => $filters
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return Inertia::render('Product/Create', [
            'categories' => CategoryResource::collection(Category::all()),
            'dosages' => DosageResource::collection(Dosage::all())
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return Inertia::render('Product/Edit', [
            'product' => $product,
            'categories' => CategoryResource::collection(Category::all()),
            'dosages' => DosageResource::collection(Dosage::all())
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:products,id',
                'name' => $request->id ? 'required|string|max:255' : 'required|string|max:255|unique:products,name',
                'category_id' => 'nullable|exists:categories,id',
                'dosage_id' => 'nullable|exists:dosages,id',
                'movement' => 'required',
                'reorder_level' => 'nullable|numeric',
                'facility_types' => 'nullable|array'
            ]);

            DB::beginTransaction();
            
            // Create or update the product
            $product = Product::updateOrCreate(['id' => $validated['id']], [
                'name' => $validated['name'],
                'category_id' => $validated['category_id'] ?? null,
                'dosage_id' => $validated['dosage_id'] ?? null,
                'movement' => $validated['movement'],
                'reorder_level' => $request->reorder_level ?? null
            ]);
            
            // For a new product, we need to make sure we have the correct ID
            if ($request->id === null) {
                // Get the latest product with this name
                $freshProduct = Product::where('name', $validated['name'])->latest()->first();
                
                if ($freshProduct) {
                    $product = $freshProduct;
                    logger()->info('Using product with ID: ' . $product->id);
                } else {
                    logger()->error('Could not find the newly created product');
                }
            }
            
            // Handle facility types if provided - only for new products
            if (!empty($request->facility_types) && $request->id === null && $product) {
                // Create eligible items for each facility type
                foreach ($request->facility_types as $facilityType) {
                    EligibleItem::create([
                        'product_id' => $product->id,
                        'facility_type' => $facilityType
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json($request->id ? 'Product updated successfully.' : 'Product created successfully.', 200);
        } catch (Throwable $e) {
            DB::rollBack();
            logger()->info($e->getMessage());
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
