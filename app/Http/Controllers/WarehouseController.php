<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseResource;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Throwable;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        $query = Warehouse::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->paginate($perPage);
        $categories = Category::where('is_active', true)->get();

        return Inertia::render('Warehouse/Index', [
            'warehouses' => WarehouseResource::collection($warehouses),
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created warehouse or update an existing one.
     */
    public function store(Request $request)
    {
        
        try {
        $request->validate([
            'id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $request->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:0',
            'status' => 'string|max:50',
            'special_handling_capabilities' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

            $warehouse = Warehouse::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'code' => $request->code,
                    'address' => $request->address,
                    'city' => $request->city,
                    'postal_code' => $request->postal_code,
                    'manager_name' => $request->manager_name,
                    'manager_phone' => $request->manager_phone,
                    'manager_email' => $request->manager_email,
                    'capacity' => $request->capacity,
                    'status' => $request->status ?? 'active',
                    'special_handling_capabilities' => $request->special_handling_capabilities,
                    'notes' => $request->notes,
                    'user_id' => auth()->id()
                ]
            );

            $message = $request->id ? 'Warehouse updated successfully' : 'Warehouse created successfully';
            return response()->json($message, 200);
        } catch (Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse->delete();
            return response()->json('Warehouse deleted successfully', 200);
        } catch (Throwable $e) {
            return response()->json('Error deleting warehouse: ' . $e->getMessage(), 500);
        }
    }

    
    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse)
    {
        return Inertia::render('Warehouse/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Warehouse/Create');
    }

}
