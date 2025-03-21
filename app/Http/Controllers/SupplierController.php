<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Supplier::query()->orderBy('name');

        // Apply search filter if provided
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply active filter if provided
        if ($request->has('active') && $request->input('active') !== null) {
            $query->where('is_active', $request->input('active'));
        }

        $suppliers = $query->paginate(10)->withQueryString();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'active']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully.',
            'supplier' => $supplier
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['supplies' => function($query) {
            $query->with('product')->latest();
        }]);
        
        $supplier->loadCount('supplies');
        
        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully.',
            'supplier' => $supplier
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        // Check if the supplier has any associated supplies
        if ($supplier->supplies()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete supplier with associated supplies.',
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully.'
        ]);
    }
}
