<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Dosage;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\EligibleItem;
use App\Models\Facility;
use App\Http\Resources\EligibleItemResource;
use Inertia\Inertia;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DosageResource;
use App\Http\Resources\SubCategoryResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $query->where(function ($q) use ($search) {
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

        $products = $query->with(['dosage.category', 'subCategory'])
            ->paginate($request->input('per_page', 5), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $products->setPath(url()->current());

        // Handle category listing functionality
        $categoryQuery = Category::query();

        // Handle category search
        if ($request->has('categorySearch')) {
            $searchTerm = $request->categorySearch;
            $categoryQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('dosages', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Handle category sorting
        $categorySortField = $request->input('category_sort_field', 'id');
        $categorySortDirection = $request->input('category_sort_direction', 'desc');

        $categoryQuery->orderBy($categorySortField, $categorySortDirection);

        // Get paginated category results
        $categories = $categoryQuery
            ->paginate($request->input('category_per_page', 3), ['*'], 'category_page', $request->input('category_page', 1))
            ->withQueryString();

        $categories->setPath(url()->current());

        // Handle dosage listing functionality
        $dosageQuery = Dosage::query();

        // Handle dosage search
        if ($request->has('dosageSearch')) {
            $searchTerm = $request->dosageSearch;
            $dosageQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle dosage sorting
        $dosageSortField = $request->input('dosage_sort_field', 'id');
        $dosageSortDirection = $request->input('dosage_sort_direction', 'desc');

        $dosageQuery->orderBy($dosageSortField, $dosageSortDirection);

        // Get paginated dosage results
        $dosages = $dosageQuery->with('category')
            ->paginate($request->input('dosage_per_page', 6), ['*'], 'dosage_page', $request->input('dosage_page', 1))
            ->withQueryString();

        $dosages->setPath(url()->current());

        // Handle subcategory listing functionality
        $subcategoryQuery = SubCategory::query();

        // Handle subcategory search
        if ($request->has('subcategorySearch')) {
            $searchTerm = $request->subcategorySearch;
            $subcategoryQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle subcategory sorting
        $subcategorySortField = $request->input('subcategory_sort_field', 'id');
        $subcategorySortDirection = $request->input('subcategory_sort_direction', 'desc');

        $subcategoryQuery->orderBy($subcategorySortField, $subcategorySortDirection);

        // Get paginated subcategory results with counts
        $subcategories = $subcategoryQuery
            ->withCount(['products'])
            ->paginate($request->input('subcategory_per_page', 6), ['*'], 'subcategory_page', $request->input('subcategory_page', 1))
            ->withQueryString();

        $subcategories->setPath(url()->current());


        // Handle eligible items functionality
        $eligibleItems = EligibleItem::query();

        if ($request->has('facility') && $request->facility) {
            $eligibleItems->where('facility_id', $request->facility);
        }

        if ($request->filled('eligibleSearch')) {
            $search = $request->eligibleSearch;
            $eligibleItems->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $eligibleItems = $eligibleItems->with('product', 'facility')
            ->paginate($request->input('eligible_per_page', 6), ['*'], 'eligible_page', $request->input('eligible_page', 1))
            ->withQueryString();

        $eligibleItems->setPath(url()->current());


        return Inertia::render('Product/Index', [
            'products' => ProductResource::collection($products),
            'filters' => $request->only(['search', 'dosage_id', 'category_id', 'is_active', 'sort_field', 'sort_direction', 'per_page', 'page', 'eligibleSearch', 'eligible_per_page', 'eligible_page', 'facility']),
            'categoryFilters' => $request->only(['categorySearch', 'category_sort_field', 'category_sort_direction', 'category_per_page', 'category_page']),
            'categories' => CategoryResource::collection($categories),
            'dosages' => DosageResource::collection($dosages),
            'dosageFilters' => $request->only(['dosageSearch', 'dosage_sort_field', 'dosage_sort_direction', 'dosage_per_page', 'dosage_page']),
            'subcategories' => SubCategoryResource::collection($subcategories),
            'subcategoryFilters' => $request->only(['subcategorySearch', 'subcategory_sort_field', 'subcategory_sort_direction', 'subcategory_per_page', 'subcategory_page']),
            'eligibleItems' => EligibleItemResource::collection($eligibleItems),
            'facilities' => Facility::get(),
            'eligibleProducts' => Product::get()
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
                'reorder_level' => 'nullable|numeric',
                'sub_category_id' => 'nullable|exists:sub_categories,id',
                'is_active' => 'boolean',
            ]);

            $product = Product::updateOrCreate(['id' => $validated['id']], $validated);

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
            $product = Product::where('name',  $search)
                ->orWhere('barcode', $search)
                ->select('name as product_name', 'id as product_id')
                ->first();

            return response()->json($product, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }


    /**
     * Handle bulk actions for products
     */
    public function bulk(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:products,id',
                'action' => 'required|string|in:delete'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();
            try {
                if ($request->action === 'delete') {
                    $products = Product::with(['inventories', 'supplyItems', 'dosage'])
                        ->whereIn('id', $request->ids)
                        ->get();

                    foreach ($products as $product) {
                        // Check if product has any related inventories
                        if ($product->inventories->count() > 0) {
                            throw new \Exception("Cannot delete product '{$product->name}' because it has related inventory items.");
                        }

                        // Check if product has any supply items
                        if ($product->supplyItems->count() > 0) {
                            throw new \Exception("Cannot delete product '{$product->name}' because it is used in supplies.");
                        }

                        $product->delete();
                    }
                }
                DB::commit();

                return response()->json(['message' => 'Products deleted successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // eligible items store
    public function addEligibleItemStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:eligible_items,id',
                'product_id' => 'required|exists:products,id',
                'facility_id' => 'required|exists:facilities,id',
            ]);

            $validator = Validator::make($request->all(), [
                'product_id' => Rule::unique('eligible_items', 'product_id')->where(function ($query) use ($request) {
                    return $query->where('facility_id', $request->facility_id)->whereNotIn('id', [$request->id]);
                }),
            ]);

            if ($validator->fails()) {
                return response()->json("Already exists", 500);
            }


            $eligibleItem = EligibleItem::updateOrCreate(['id' => $validated['id']], $validated);

            return response()->json($request->id ? 'updated' : 'created', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    // eligible items destroy
    public function destroyEligibleItem($id)
    {
        try {
            EligibleItem::find($id)->delete();
            return response()->json('Eligible item deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
