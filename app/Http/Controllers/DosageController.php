<?php

namespace App\Http\Controllers;

use App\Models\Dosage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\DosageResource;

class DosageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Dosage::query()->with('category');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Sorting
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        if (in_array($sortField, ['id', 'name', 'description', 'is_active', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $dosages = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => DosageResource::collection($dosages)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|exists:dosages,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dosages', 'name')->ignore($request->id)
            ],
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Dosage::updateOrCreate(
                ['id' => $request->id],
                $validator->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Dosage saved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $dosage = Dosage::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $dosage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dosage not found'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $dosage = Dosage::findOrFail($id);
            
            // Check if the dosage is associated with any products
            if ($dosage->products()->count() > 0) {
                return response()->json('Cannot delete dosage. It is associated with one or more products.', 500);
            }
            
            $dosage->delete();
            
            return response()->json('Dosage deleted successfully', 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Get dosages by category
     */
    public function getByCategory($category)
    {
        try {
            $dosages = Dosage::where('category_id', $category)
                ->where('is_active', true)
                ->with('category')
                ->get();

            return response()->json($dosages, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
