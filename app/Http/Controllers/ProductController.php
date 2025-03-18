<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::query()->with(['warehouse', 'category']);
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('active_ingredient', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }
        
        // Filter by warehouse
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' ? true : false);
        }
        
        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $products = $query->paginate(10)->withQueryString();
        
        // Get all warehouses for the warehouse filter
        $warehouses = Warehouse::where('is_active', true)->get();
        
        // Get all categories for the category filter
        $categories = Category::all();
        
        return Inertia::render('Product/Index', [
            'products' => $products,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'filters' => $request->only(['search', 'warehouse_id', 'category_id', 'is_active', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products',
            'barcode' => 'nullable|string|max:100|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id',
            'active_ingredient' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:100',
            'strength' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'storage_conditions' => 'nullable|string|max:255',
            'prescription_required' => 'nullable|string|in:yes,no',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id',
            'active_ingredient' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:100',
            'strength' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'storage_conditions' => 'nullable|string|max:255',
            'prescription_required' => 'nullable|string|in:yes,no',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
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
}
