<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Handle search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle sorting
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);
        
        // Get paginated results
        $categories = $query->paginate(10)->withQueryString();
        
        return Inertia::render('Product/Category/Index', [
            'categories' => CategoryResource::collection($categories),
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return Inertia::render('Product/Category/Create');
    }

    /**
     * Show the form for editing a category.
     */
    public function edit(Category $category)
    {
        return Inertia::render('Product/Category/Edit', [
            'category' => new CategoryResource($category)
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $category = Category::create($request->all());
            
            return redirect()->route('products.categories.index')->with('success', 'Category created successfully');
        } catch (Throwable $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $category->update($request->all());
            
            return redirect()->route('products.categories.index')->with('success', 'Category updated successfully');
        } catch (Throwable $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * API endpoint to get categories for AJAX requests
     */
    public function getCategories(Request $request)
    {
        $query = Category::query();
        
        // Handle search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle sorting
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);
        
        // Get paginated results
        $categories = $query->paginate(10)->withQueryString();
        
        return response()->json([
            'success' => true,
            'categoriesPaginated' => CategoryResource::collection($categories),
            'allCategories' => CategoryResource::collection(Category::all())
        ]);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has any products
            if ($category->products()->exists()) {
                return response()->json("Cannot delete category. It is being used by {$category->products()->count()} product(s).", 500);
            }

            $category->delete();
            return response()->json('Category deleted successfully', 200);
        } catch (Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Bulk delete categories
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:categories,id'
            ]);

            $categories = Category::whereIn('id', $request->ids)->get();
            
            // Check each category for products
            foreach ($categories as $category) {
                if ($category->products()->exists()) {
                    return response()->json("Cannot delete category '{$category->name}'. It is being used by {$category->products()->count()} product(s).", 500);
                }
            }

            // Delete all categories if none have products
            Category::whereIn('id', $request->ids)->delete();

            return response()->json('Categories deleted successfully', 200);
        } catch (Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
