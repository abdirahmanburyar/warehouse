<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SubCategory::query();

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 6);
        $subcategories = $query->paginate($perPage);

        return response()->json([
            'data' => $subcategories,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sub_categories,name,' . ($request->id ?? 'NULL') . ',id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subcategory = $request->id
                ? SubCategory::findOrFail($request->id)
                : new SubCategory;

            $subcategory->fill($request->all());
            $subcategory->save();

            return response()->json([
                'success' => true,
                'message' => $request->id ? 'SubCategory updated successfully' : 'SubCategory created successfully',
                'data' => $subcategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the subcategory'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategory $subcategory)
    {
        return response()->json($subcategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subcategory)
    {
        try {
            $subcategory->delete();
            return response()->json([
                'success' => true,
                'message' => 'SubCategory deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the subcategory'
            ], 500);
        }
    }
}
