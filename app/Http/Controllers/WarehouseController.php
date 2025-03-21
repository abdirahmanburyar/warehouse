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
        $sort = $request->input('sort', 'name');
        $order = $request->input('order', 'asc');
        $perPage = $request->input('per_page', 10);

        $query = Warehouse::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->orderBy($sort, $order)->paginate($perPage);
        $categories = Category::where('is_active', true)->get();

        return Inertia::render('Warehouse/Index', [
            'warehouses' => WarehouseResource::collection($warehouses),
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'order' => $order,
                'per_page' => $perPage,
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
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'capacity' => 'nullable|integer|min:0',
            'temperature_min' => 'nullable|integer',
            'temperature_max' => 'nullable|integer',
            'humidity_min' => 'nullable|numeric',
            'humidity_max' => 'nullable|numeric',
            'status' => 'nullable|string|max:50',
            'has_cold_storage' => 'boolean',
            'has_hazardous_storage' => 'boolean',
            'is_active' => 'boolean',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

            $warehouse = Warehouse::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'code' => $request->code,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'postal_code' => $request->postal_code,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'capacity' => $request->capacity,
                    'temperature_min' => $request->temperature_min,
                    'temperature_max' => $request->temperature_max,
                    'humidity_min' => $request->humidity_min,
                    'humidity_max' => $request->humidity_max,
                    'status' => $request->status,
                    'has_cold_storage' => $request->has_cold_storage,
                    'has_hazardous_storage' => $request->has_hazardous_storage,
                    'is_active' => $request->is_active,
                    'manager_name' => $request->manager_name,
                    'manager_email' => $request->manager_email,
                    'manager_phone' => $request->manager_phone,
                    'notes' => $request->notes,
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
}
