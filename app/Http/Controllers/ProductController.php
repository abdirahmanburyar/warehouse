<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DosageResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by dosage
        if ($request->has('dosage_id') && $request->dosage_id) {
            $query->where('dosage_id', $request->dosage_id);
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' ? true : false);
        }
        
        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $products = $query->with(['dosage.category'])->paginate($request->input('per_page', 10));
        
        // Handle category listing functionality
        $categoryQuery = Category::query();
        
        // Handle category search
        if ($request->has('categorySearch')) {
            $searchTerm = $request->categorySearch;
            $categoryQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('dosages', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        // Handle category sorting
        $categorySortField = $request->input('category_sort_field', 'id');
        $categorySortDirection = $request->input('category_sort_direction', 'desc');
        
        $categoryQuery->orderBy($categorySortField, $categorySortDirection);
        
        // Get paginated category results
        $categories = $categoryQuery->paginate($request->input('category_per_page', 10));
        
        // Handle dosage listing functionality
        $dosageQuery = Dosage::query();
        
        // Handle dosage search
        if ($request->has('dosageSearch')) {
            $searchTerm = $request->dosageSearch;
            $dosageQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle dosage sorting
        $dosageSortField = $request->input('dosage_sort_field', 'id');
        $dosageSortDirection = $request->input('dosage_sort_direction', 'desc');
        
        $dosageQuery->orderBy($dosageSortField, $dosageSortDirection);
        
        // Get paginated dosage results
        $dosages = $dosageQuery->with('category')->paginate($request->input('dosage_per_page', 10));
        
        return Inertia::render('Product/Index', [
            'products' => ProductResource::collection($products),
            'filters' => $request->only(['search', 'dosage_id', 'category_id', 'is_active', 'sort_field', 'sort_direction', 'per_page']),
            'categoryFilters' => $request->only(['categorySearch', 'category_sort_field', 'category_sort_direction', 'category_per_page']),
            'categories' => CategoryResource::collection($categories),
            'dosages' => DosageResource::collection($dosages),
            'dosageFilters' => $request->only(['dosageSearch', 'dosage_sort_field', 'dosage_sort_direction', 'dosage_per_page']),
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
                'name' => 'required|string|max:255|unique:products,name,' . $request->id,
                'sku' => 'required|string|max:100|unique:products,sku,' . $request->id,
                'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $request->id,
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'dosage_id' => 'nullable|exists:dosages,id',
                'is_active' => 'boolean',
            ]);

        $product = Product::updateOrCreate([ 'id' => $validated['id']], $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'product' => new ProductResource($product)
        ], 200);
    } catch (Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while saving the product',
            'error' => $e->getMessage()
        ], 500);
    }
    }

    /**
     * Store or update a category.
     */
    public function storeCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:categories,id',
                'name' => 'required|string|max:255|unique:categories,name,' . $request->id,
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);
            
            $category = Category::updateOrCreate(
                ['id' => $validated['id']],
                [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                ]
            );
            
            $action = $request->id ? 'updated' : 'created';
            
            return response()->json([
                'success' => true,
                'message' => "Category {$action} successfully",
                'category' => new CategoryResource($category)
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(Category $category)
    {
        try {
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the category',
                'error' => $e->getMessage()
            ], 500);
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

    /**
     * Search for products by name or barcode
     */
    public function search(Request $request)
    {
        try {
            $search = $request->input('search');
            $products = Product::where('name', 'like', '%' . $search . '%')
                ->orWhere('barcode', 'like', '%' . $search . '%')
                ->select('id', 'name', 'barcode')
                ->get()
                ->map(function ($product) {
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                    ];
                });
                    
            return response()->json($products, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
