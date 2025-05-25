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
        $status = $request->input('status', '');
        $stateId = $request->input('state_id', '');
        $districtId = $request->input('district_id', '');
        $cityId = $request->input('city_id', '');

        $query = Warehouse::query();

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('manager_name', 'like', "%{$search}%")
                    ->orWhere('manager_phone', 'like', "%{$search}%")
                    ->orWhere('manager_email', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Apply location filters
        if (!empty($stateId)) {
            $query->where('state_id', $stateId);
        }

        if (!empty($districtId)) {
            $query->where('district_id', $districtId);
        }

        if (!empty($cityId)) {
            $query->where('city_id', $cityId);
        }

        // Load relationships with their parent relationships
        $query->with([
            'state',
            'district.state',
            'city.district.state'
        ]);

        $warehouses = $query->paginate($perPage)->withQueryString();

        // Get states for dropdown
        $states = \App\Models\State::orderBy('name')->get();
        
        // Get districts based on selected state
        $districts = [];
        if (!empty($stateId)) {
            $districts = \App\Models\District::where('state_id', $stateId)->orderBy('name')->get();
        }
        
        // Get cities based on selected district
        $cities = [];
        if (!empty($districtId)) {
            $cities = \App\Models\City::where('district_id', $districtId)->orderBy('name')->get();
        }

        // Add status badge to each warehouse
        $warehouses->through(function ($warehouse) {
            $statusClass = '';
            if ($warehouse->status === 'active') {
                $statusClass = 'bg-green-100 text-green-800';
            } elseif ($warehouse->status === 'inactive') {
                $statusClass = 'bg-red-100 text-red-800';
            } elseif ($warehouse->status === 'maintenance') {
                $statusClass = 'bg-yellow-100 text-yellow-800';
            }
            
            $warehouse->status_badge = $statusClass;
            return $warehouse;
        });
        
        return Inertia::render('Warehouse/Index', [
            'warehouses' => $warehouses,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
                'status' => $status,
                'state_id' => $stateId,
                'district_id' => $districtId,
                'city_id' => $cityId,
            ],
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'maintenance', 'label' => 'Maintenance'],
            ],
            'states' => $states,
            'districts' => $districts,
            'cities' => $cities,
        ]);
    }

    /**
     * Store a newly created warehouse or update an existing one.
     */
    public function store(Request $request)
    {
        try {
        // Log the incoming request data for debugging
        \Illuminate\Support\Facades\Log::info('Warehouse store request data:', $request->all());
            
        $request->validate([
            'id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $request->id,
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'postal_code' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:50',
            'status' => 'string|max:50',
        ]);

            // Generate a code if not provided
            if (!$request->code) {
                $prefix = 'WH';
                $timestamp = substr(time(), -6);
                $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                $code = "{$prefix}-{$timestamp}-{$random}";
            } else {
                $code = $request->code;
            }

            $warehouse = Warehouse::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'code' => $code,
                    'address' => $request->address,
                    'state' => $request->state,
                    'district' => $request->district,
                    'city' => $request->city,
                    'state_id' => $request->state_id,
                    'district_id' => $request->district_id,
                    'city_id' => $request->city_id,
                    'postal_code' => $request->postal_code,
                    'manager_name' => $request->manager_name,
                    'manager_phone' => $request->manager_phone,
                    'manager_email' => $request->manager_email,
                    'status' => $request->status ?? 'active',
                    'user_id' => auth()->id()
                ]
            );

            $message = $request->id ? 'Warehouse updated successfully' : 'Warehouse created successfully';
            return response()->json($message, 200);
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Warehouse store error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Error trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error saving warehouse', 'error' => $e->getMessage()], 500);
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
        
        // Log the warehouse data for debugging
        logger()->info([
            'warehouse_id' => $warehouse->id,
            'state_id' => $warehouse->state_id,
            'district_id' => $warehouse->district_id,
            'city_id' => $warehouse->city_id,
            'state' => $warehouse->state,
            'district' => $warehouse->district,
            'city' => $warehouse->city
        ]);
        
        return Inertia::render('Warehouse/Edit', [
            'warehouse' => $warehouse,
            'states' => $states,
            'districts' => $districts,
            'cities' => $cities
        ]);
    }

    public function create(Request $request)
    {
        // Get states for dropdown
        $states = \App\Models\State::orderBy('name')
            ->get()
            ->map(function($state) {
                return [
                    'id' => $state->id,
                    'name' => $state->name,
                    'code' => $state->code
                ];
            });
        
        // Get districts for dropdown
        $districts = \App\Models\District::orderBy('name')
            ->get()
            ->map(function($district) {
                return [
                    'id' => $district->id,
                    'name' => $district->name,
                    'state_id' => $district->state_id
                ];
            });
            
        // Get cities for dropdown
        $cities = \App\Models\City::orderBy('name')
            ->get()
            ->map(function($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'district_id' => $city->district_id
                ];
            });
        
        return Inertia::render('Warehouse/Create', [
            'states' => $states,
            'districts' => $districts,
            'cities' => $cities
        ]);
    }

}
