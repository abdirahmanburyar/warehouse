<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Warehouse;

class LocationController extends Controller
{
    public function index(Request $request){
        $locations = Location::query()
            ->when($request->filled('search'), function($query) use ($request){
                $query->where('location', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('warehouse'), function($query) use ($request){
                $query->whereHas('warehouse', function($query) use($request){
                    $query->where('name', $request->warehouse);
                });
            })
            ->with('warehouse')
            ->get();
            
        $warehouses = Warehouse::select('id','name')->pluck('name')->toArray();
        
        return inertia('Location/Index', [
            'locations' => $locations,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'warehouse'])
        ]);
    }

    public function store(Request $request){
        try {
            $request->validate([
                'id' => 'nullable',
                'location' => 'required|string|unique:locations,location,' . $request->id,
                'warehouse_id' => 'required'
            ]);
            Location::updateOrCreate([
                'id' => $request->id
            ],[
                'location' => $request->location,
                'warehouse_id' => $request->warehouse_id,
            ]);
            return response()->json($request->id ? "Updated" : "Created", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function create(Request $request){
        $warehouses = Warehouse::get();
        return inertia('Location/Create',[
            'warehouses' => $warehouses
        ]);
    }

    public function destroy(Request $request, $id){
        try {
            $location = Location::find($id);
            $location->delete();
            return response()->json("Deleted Successfully", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function edit(Request $request, $id){
        $location = Location::find($id);
        $warehouses = Warehouse::get();
        return inertia("Location/Edit", ['location' => $location, 'warehouses' => $warehouses]);
    }

    public function getLocations(Request $request, $id){
        try {
            $locations = Location::where('warehouse_id', $id)->select('id','location','warehouse_id')->get();
            return response()->json($locations, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
