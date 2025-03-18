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
                'name' => 'required|string|max:255|unique:categories,name,' . $request->id,
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $category = Category::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'description' => $request->description,
                    'is_active' => $request->is_active ?? true,
                ]
            );
            
            $action = $request->id ? 'updated' : 'created';
            
            return response()->json([
                'success' => true,
                'message' => "Category {$action} successfully",
                'category' => new CategoryResource($category)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
