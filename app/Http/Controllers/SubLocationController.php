<?php

namespace App\Http\Controllers;

use App\Models\SubLocation;
use App\Models\AssetLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class SubLocationController extends Controller
{
    public function index()
    {
        $subLocations = SubLocation::with('location')->get();
        $locations = AssetLocation::all();
        
        return Inertia::render('Assets/SubLocation/Index', [
            'subLocations' => $subLocations,
            'locations' => $locations
        ]);
    }

    public function create()
    {
        $locations = AssetLocation::select('id', 'name')->get();
        return Inertia::render('Assets/SubLocation/Create', [
            'locations' => $locations
        ]);
    }

    public function edit($id)
    {
        $subLocation = SubLocation::where("id", $id)->with('location:id,name')->first();
        $locations = AssetLocation::select('id','name')->get();
        
        return Inertia::render('Assets/SubLocation/Edit', [
            'subLocation' => $subLocation,
            'locations' => $locations
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'id' => 'nullable',
                'name' => 'required|string|max:255',
                'asset_location_id' => 'required|exists:asset_locations,id'
            ]);
            
            $subLocation = SubLocation::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'asset_location_id' => $request->asset_location_id
                ]
            );

            $message = $request->id ? 'Sub-location updated successfully' : 'Sub-location created successfully';

            return response()->json( $message, 200);
        } catch (Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $subLocation = SubLocation::findOrFail($id);
            $subLocation->delete();
            
            return response()->json(['message' => 'Sub-location deleted successfully']);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
