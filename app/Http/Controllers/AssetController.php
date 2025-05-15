<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\SubLocation;
use App\Models\AssetLocation;
use App\Models\AssetCategory;
use App\Models\CustodyHistory;
use App\Http\Resources\AssetResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $locations = AssetLocation::with('subLocations')->get();
        return Inertia::render('Assets/Index', [
            'locations' => $locations,
        ]);
    }

    public function getAssets(Request $request)
    {
        $query = Asset::query()
            ->with('category:id,name', 'location:id,name', 'subLocation:id,name', 'history');

        // Apply location filter if provided
        if ($request->has('locations')) {
            $locations = $request->locations;
            if (!empty($locations)) {
                $query->whereIn('asset_location_id', $locations);
            }
        }

        // Apply sub-location filter if provided
        if ($request->has('sub_locations')) {
            $subLocations = $request->sub_locations;
            if (!empty($subLocations)) {
                $query->whereIn('sub_location_id', $subLocations);
            }
        }

        $assets = $query->latest()->get();
        return response()->json($assets);
        $locations = AssetLocation::with('subLocations')->get();
        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            "locations" => $locations
        ]);
    }

    public function create()
    {
        $locations = AssetLocation::all();
        $categories = AssetCategory::all();
        return Inertia::render('Assets/Create', [
            'locations' => $locations,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'asset_tag' => 'required|string|max:255',
                'asset_category_id' => 'required|exists:asset_categories,id',
                'serial_number' => 'required|string|max:255|unique:assets,serial_number,' . $request->id,
                'item_description' => 'required|string',
                'person_assigned' => 'required',
                'asset_location_id' => 'required|',
                'sub_location_id' => 'nullable',
                'acquisition_date' => 'required|date',
                'status' => 'required|string|in:active,in_use,maintenance,retired,disposed',
                'original_value' => 'required|numeric|min:0',
                'source_agency' => 'required|string|max:255'
            ]);
    
            Asset::updateOrCreate(
                ['id' => $request->id],
                $validated
            );
    
            return response()->json($request->id ? 'Updated' : 'Success', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function edit(Asset $asset)
    {
        $locations = AssetLocation::all();
        $categories = AssetCategory::all();
        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'locations' => $locations,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        try {
            $validated = $request->validate([
                'asset_tag' => 'required|string|max:255',
                'asset_category_id' => 'required|exists:asset_categories,id',
                'serial_number' => 'required|string|max:255|unique:assets,serial_number,' . $asset->id,
                'item_description' => 'required|string',
                'person_assigned' => 'required',
                'asset_location_id' => 'required',
                'sub_location_id' => 'nullable',
                'acquisition_date' => 'required|date',
                'status' => 'required|string|in:active,in_use,maintenance,retired,disposed',
                'original_value' => 'required|numeric|min:0',
                'source_agency' => 'required|string|max:255'
            ]);
    
            $asset->update($validated);
    
            return response()->json('Updated', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index');
    }

    public function getSubLocations($locationId)
    {
        $subLocations = SubLocation::where('asset_location_id', $locationId)->get();
        return response()->json($subLocations);
    }
   
}
