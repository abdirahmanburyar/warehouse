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
        
        return Inertia::render('Category/Index', [
            'categories' => CategoryResource::collection($categories),
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Store a newly created category in storage or update an existing one.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'nullable|exists:categories,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return back()->withErrors($validator)->withInput();
            }
            
            if ($request->id) {
                $category = Category::findOrFail($request->id);
                $category->update($request->all());
                $message = 'Category updated successfully';
            } else {
                $category = Category::create($request->all());
                $message = 'Category created successfully';
            }
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'category' => new CategoryResource($category)
                ]);
            }
            
            return redirect()->route('categories.index')->with('success', $message);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            
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
