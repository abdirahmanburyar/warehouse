<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function index(Request $request){
        $locations = Location::query()
            ->when($request->filled('location'), function($query){
                $query->where('location', $query);
            })
            ->get();
        return inertia('Location/Index', [
            'locations' => $locations
        ]);
    }

    public function store(Request $request){
        try {
            $request->validate([
                'id' => 'nullable',
                'location' => 'required|string|unique:locations,location,' . $request->id
            ]);
            Location::updateOrCreate([
                'id' => $request->id
            ],[
                'location' => $request->location
            ]);
            return response()->json($request->id ? "Updated" : "Created", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
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
        return inertia("Location/Edit", ['location' => $location]);
    }
}
