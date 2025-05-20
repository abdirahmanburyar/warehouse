<?php

namespace App\Http\Controllers;

use App\Http\Resources\EligibleItemResource;
use App\Models\EligibleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EligibleItemController extends Controller
{
    public function index(Request $request)
    {
        $query = EligibleItem::query()->with('product');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('facility_type')) {
            $type = $request->input('facility_type');
            $query->where('facility_type', 'like', "%{$type}%");
        }

        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);
        
        $perPage = $request->input('per_page', 10);
        $eligibleItems = $query->paginate($perPage)->withQueryString();

        $paginatedItems = EligibleItemResource::collection($eligibleItems)->response()->getData(true);

        return Inertia::render('Product/Eligible/Index', [
            'eligibleItems' => [
                'data' => $paginatedItems['data'],
                'meta' => [
                    'total' => $eligibleItems->total(),
                    'per_page' => $eligibleItems->perPage(),
                    'current_page' => $eligibleItems->currentPage(),
                    'last_page' => $eligibleItems->lastPage(),
                    'links' => [
                        'prev' => $eligibleItems->previousPageUrl(),
                        'next' => $eligibleItems->nextPageUrl(),
                    ],
                ],
            ],
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'per_page','facility_type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Product/Eligible/Create', [
            'products' => Product::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'facility_types' => 'required|array|min:1'
            ]);

            foreach ($request->facility_types as $type) {
                foreach ($request->products as $product) {
                    $eligibleItem = EligibleItem::firstOrCreate([
                        'product_id' => $product['product_id'],
                        'facility_type' => $type,
                    ]);
    
                }
            }

            return response()->json("Created eligible items successfully", 200);

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function edit(EligibleItem $eligible)
    {
        return Inertia::render('Product/Eligible/Edit', [
            'eligible' => $eligible,
            'products' => Product::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function destroy(EligibleItem $eligible)
    {
        try {
            $eligible->delete();

            return response()->json('Eligible item deleted successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
