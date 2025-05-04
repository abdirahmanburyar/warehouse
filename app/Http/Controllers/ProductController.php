<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\EligibleItem;
use App\Models\Facility;
use App\Http\Resources\EligibleItemResource;
use Inertia\Inertia;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DosageResource;
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
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by dosage
        if ($request->filled('dosage_id')) {
            $query->where('dosage_id', $request->dosage_id);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $products = $query->with(['dosage', 'category'])
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        // Handle category listing functionality
        $categoryQuery = Category::query();

        // Handle category search
        if ($request->filled('category_search')) {
            $searchTerm = $request->category_search;
            $categoryQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle category sorting
        $categorySortField = $request->input('category_sort_field', 'created_at');
        $categorySortDirection = $request->input('category_sort_direction', 'desc');
        $categoryQuery->orderBy($categorySortField, $categorySortDirection);

        $categories = $categoryQuery
            ->paginate($request->input('category_per_page', 10), ['*'], 'categories_page')
            ->withQueryString();

        // Handle dosage listing functionality
        $dosageQuery = Dosage::query();

        // Handle dosage search
        if ($request->filled('dosage_search')) {
            $searchTerm = $request->dosage_search;
            $dosageQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle dosage sorting
        $dosageSortField = $request->input('dosage_sort_field', 'created_at');
        $dosageSortDirection = $request->input('dosage_sort_direction', 'desc');
        $dosageQuery->orderBy($dosageSortField, $dosageSortDirection);

        $dosages = $dosageQuery
            ->paginate($request->input('dosage_per_page', 10), ['*'], 'dosages_page')
            ->withQueryString();

        // Handle eligible items
        $eligibleQuery = EligibleItem::query()->with(['product']);

        if ($request->filled('eligible_search')) {
            $searchTerm = $request->eligible_search;
            $eligibleQuery->whereHas('product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('facility_type')) {
            $eligibleQuery->where('facility_type', $request->facility_type);
        }

        $eligibleItems = $eligibleQuery
            ->paginate($request->input('eligible_per_page', 10), ['*'], 'eligible_page')
            ->withQueryString();

        return Inertia::render('Product/Index', [
            'products' => ProductResource::collection($products),
            'categories' => CategoryResource::collection($categories),
            'dosages' => DosageResource::collection($dosages),
            'eligibleItems' => EligibleItemResource::collection($eligibleItems),
            'filters' => [
                'search' => $request->search,
                'category_id' => $request->category_id,
                'dosage_id' => $request->dosage_id,
                'is_active' => $request->is_active,
                'per_page' => (int)$request->input('per_page', 10),
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
                
                // Category filters
                'category_search' => $request->category_search,
                'category_per_page' => (int)$request->input('category_per_page', 10),
                'category_sort_field' => $categorySortField,
                'category_sort_direction' => $categorySortDirection,
                
                // Dosage filters
                'dosage_search' => $request->dosage_search,
                'dosage_per_page' => (int)$request->input('dosage_per_page', 10),
                'dosage_sort_field' => $dosageSortField,
                'dosage_sort_direction' => $dosageSortDirection,
                
                // Eligible filters
                'eligible_search' => $request->eligible_search,
                'eligible_per_page' => (int)$request->input('eligible_per_page', 10),
                'facility_type' => $request->facility_type
            ]
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
                'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $request->id,
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'dosage_id' => 'nullable|exists:dosages,id',
                'dose' => 'required',
                'reorder_level' => 'nullable|numeric',
                'is_active' => 'boolean',
            ]);

            $product = Product::updateOrCreate(['id' => $validated['id']], $validated);

            return response()->json($request->id ? 'Product updated successfully.' : 'Product created successfully.', 200);
        } catch (Throwable $e) {
            return response()->json($e->getMessage(), 500);
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
                'facility_type' => 'required'
            ]);

            $validator = Validator::make($request->all(), [
                'product_id' => Rule::unique('eligible_items', 'product_id')->where(function ($query) use ($request) {
                    return $query->where('facility_type', $request->facility_type)->whereNotIn('id', [$request->id]);
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
