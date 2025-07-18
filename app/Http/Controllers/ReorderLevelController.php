<?php

namespace App\Http\Controllers;

use App\Models\ReorderLevel;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReorderLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $reorderLevels = ReorderLevel::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $reorderLevels->where(function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('productID', 'like', "%{$search}%");
                });
            });
        }

        $reorderLevels->with('product')
            ->latest();

        $reorderLevels = $reorderLevels->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $reorderLevels->setPath(url()->current()); // Force Laravel to use full URLs

        return Inertia::render('ReorderLevel/Index', [
            'reorderLevels' => $reorderLevels,
            'filters' => $request->only(['search', 'per_page', 'page'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::select('id', 'name', 'productID')
            ->orderBy('name')
            ->get();

        return Inertia::render('ReorderLevel/Create', [
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handle multiple items
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.amc' => 'required|numeric|min:0',
                'items.*.lead_time' => 'required|integer|min:1'
            ]);

            $createdCount = 0;
            foreach ($request->items as $item) {
                ReorderLevel::create([
                    'product_id' => $item['product_id'],
                    'amc' => $item['amc'],
                    'lead_time' => $item['lead_time']
                ]);
                $createdCount++;
            }

            $message = $createdCount === 1 
                ? 'Reorder level created successfully.' 
                : "{$createdCount} reorder levels created successfully.";

            return redirect()->route('reorder-levels.index')
                ->with('success', $message);
        }

        // Handle single item (backward compatibility)
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'amc' => 'required|numeric|min:0',
            'lead_time' => 'required|integer|min:1'
        ]);

        $reorderLevel = ReorderLevel::create([
            'product_id' => $request->product_id,
            'amc' => $request->amc,
            'lead_time' => $request->lead_time
        ]);

        return redirect()->route('reorder-levels.index')
            ->with('success', 'Reorder level created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReorderLevel $reorderLevel)
    {
        $reorderLevel->load('product');
        
        $products = Product::select('id', 'name', 'productID')
            ->orderBy('name')
            ->get();

        return Inertia::render('ReorderLevel/Edit', [
            'reorderLevel' => $reorderLevel,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReorderLevel $reorderLevel)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'amc' => 'required|numeric|min:0',
            'lead_time' => 'required|integer|min:1'
        ]);

        $reorderLevel->update([
            'product_id' => $request->product_id,
            'amc' => $request->amc,
            'lead_time' => $request->lead_time
        ]);

        return redirect()->route('reorder-levels.index')
            ->with('success', 'Reorder level updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReorderLevel $reorderLevel)
    {
        $reorderLevel->delete();

        return redirect()->route('reorder-levels.index')
            ->with('success', 'Reorder level deleted successfully.');
    }
}
