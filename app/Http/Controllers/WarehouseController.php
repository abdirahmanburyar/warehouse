<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseResource;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Models\District;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Throwable;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::query();

        if($request->filled('search')){
            $warehouses->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('address', 'like', '%' . $request->search . '%');
        }

        if($request->filled('status')){
            $warehouses->where('status', $request->status);
        }

        // region
        if($request->filled('region')){
            $warehouses->where('region', $request->region);
        }
        // district
        if($request->filled('district')){
            $warehouses->where('district', $request->district);
        }

        $warehouses = $warehouses->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $warehouses->setPath(url()->current());

        return Inertia::render('Warehouse/Index', [
            'warehouses' => WarehouseResource::collection($warehouses),
            'filters' => $request->only('search','per_page','page','status'),
            'regions' => Region::pluck('name')->toArray(),
            'districts' => District::pluck('name')->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        try {            
        $request->validate([
            'id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:50',
            'status' => 'string|max:50',
        ]);

            $warehouse = Warehouse::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'address' => $request->address,
                    'region' => $request->region,
                    'district' => $request->district,
                    'manager_name' => $request->manager_name,
                    'manager_phone' => $request->manager_phone,
                    'manager_email' => $request->manager_email,
                    'status' => $request->status ?? 'active',
                ]
            );

            $message = $request->id ? 'Warehouse updated successfully' : 'Warehouse created successfully';
            return response()->json($message, 200);
        } catch (Throwable $e) {
            return response()->json( $e->getMessage(), 500);
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
    public function edit($id)
    {
        // Make sure the warehouse exists
        $warehouse = \App\Models\Warehouse::find($id);
        if (!$warehouse) {
            return redirect()->route('inventories.warehouses.index')->with('error', 'Warehouse not found');
        }

        // Get all states
        $states = \App\Models\State::orderBy('name')->get();
        
        // Get all districts and cities (we'll filter them in the frontend)
        $districts = \App\Models\District::orderBy('name')->get();
        $cities = \App\Models\City::orderBy('name')->get();
        
        // Load state, district, and city data if they exist
        if ($warehouse->state_id && !$warehouse->state) {
            $warehouse->state = \App\Models\State::find($warehouse->state_id);
        }
        
        if ($warehouse->district_id && !$warehouse->district) {
            $warehouse->district = \App\Models\District::find($warehouse->district_id);
        }
        
        if ($warehouse->city_id && !$warehouse->city) {
            $warehouse->city = \App\Models\City::find($warehouse->city_id);
        }
        

        
        return Inertia::render('Warehouse/Edit', [
            'warehouse' => $warehouse,
            'states' => $states,
            'districts' => $districts,
            'cities' => $cities
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Warehouse/Create', [
            'regions' => Region::pluck('name')->toArray()
        ]);
    }
    
    /**
     * Get all warehouses for API use (dropdowns, etc.)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWarehouses()
    {
        try {
            $warehouses = Warehouse::where('status', 'active')
                ->orderBy('name')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'code' => $warehouse->code
                    ];
                });
                
            return response()->json($warehouses);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Failed to fetch warehouses'], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->status = $warehouse->status == 'active' ? 'inactive' : 'active';
            $warehouse->save();
            return response()->json('Warehouse status toggled successfully', 200);
        } catch (Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

}
