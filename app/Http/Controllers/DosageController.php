<?php

namespace App\Http\Controllers;

use App\Models\Dosage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\DosageResource;
use Inertia\Inertia;
use Throwable;

class DosageController extends Controller
{
    /**
     * Display a listing of the dosages.
     */
    public function index(Request $request)
    {
        $query = Dosage::query();
        
        // Handle search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Handle sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);
        
        // Get paginated results with per_page parameter
        $perPage = $request->input('per_page', 10);
        $dosages = $query->paginate($perPage)->withQueryString();
        
        $paginatedDosages = DosageResource::collection($dosages)->response()->getData(true);

        return Inertia::render('Product/Dosage/Index', [
            'dosages' => [
                'data' => $paginatedDosages['data'],
                'meta' => [
                    'total' => $dosages->total(),
                    'per_page' => $dosages->perPage(),
                    'current_page' => $dosages->currentPage(),
                    'last_page' => $dosages->lastPage(),
                ],
                'links' => [
                    'first' => $dosages->url(1),
                    'last' => $dosages->url($dosages->lastPage()),
                    'prev' => $dosages->previousPageUrl(),
                    'next' => $dosages->nextPageUrl(),
                ],
            ],
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new dosage.
     */
    public function create()
    {
        return Inertia::render('Product/Dosage/Create');
    }

    /**
     * Show the form for editing a dosage.
     */
    public function edit(Dosage $dosage)
    {
        return Inertia::render('Product/Dosage/Edit', [
            'dosage' => new DosageResource($dosage)
        ]);
    }

    /**
     * Store a newly created dosage in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('dosages', 'name')
                ],
                'description' => 'nullable|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $dosage = Dosage::create($request->all());
            
            return redirect()->route('products.dosages.index')->with('success', 'Dosage created successfully');
        } catch (Throwable $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified dosage in storage.
     */
    public function update(Request $request, Dosage $dosage)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('dosages', 'name')->ignore($dosage->id)
                ],
                'description' => 'nullable|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $dosage->update($request->all());
            
            return redirect()->route('products.dosages.index')->with('success', 'Dosage updated successfully');
        } catch (Throwable $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified dosage from storage.
     */
    public function destroy(Dosage $dosage)
    {
        try {
            // Check if the dosage is associated with any products
            if ($dosage->products()->exists()) {
                return response()->json('Cannot delete dosage. It is associated with one or more products.', 500);
            }
            
            $dosage->delete();
            return response()->json('Dosage deleted successfully', 200);
        } catch (Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
