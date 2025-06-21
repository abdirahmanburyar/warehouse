<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Warehouse;
use App\Http\Resources\LocationResource;

class LocationController extends Controller
{
    public function index(Request $request){
        $locations = Location::query();

        if($request->filled('search')){
            $locations->where('location','like','%'.$request->search.'%');
        }
        
        if($request->filled('warehouse')){
            $locations->where('warehouse', $request->warehouse);
        }

        $locations = $locations->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $locations->setPath(url()->current());
        
        return inertia('Location/Index', [
            'locations' => LocationResource::collection($locations),
            'warehouses' => Warehouse::pluck('name')->toArray(),
            'filters' => $request->only('search', 'warehouse','per_page','page')
        ]);
    }

    public function store(Request $request){
        try {
            $request->validate([
                'id' => 'nullable',
                'location' => 'required|string|unique:locations,location,' . $request->id,
                'warehouse' => 'required'
            ]);
            Location::updateOrCreate([
                'id' => $request->id
            ],[
                'location' => $request->location,
                'warehouse' => $request->warehouse,
            ]);
            return response()->json($request->id ? "Updated" : "Created", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function create(Request $request){
        $warehouses = Warehouse::pluck('name')->toArray();
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
        $warehouses = Warehouse::pluck('name')->toArray();
        return inertia("Location/Edit", ['location' => $location, 'warehouses' => $warehouses]);
    }

    public function getLocations(Request $request, $id){
        try {
            $locations = Location::where('warehouse', $id)->select('id','location','warehouse')->get();
            return response()->json($locations, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
