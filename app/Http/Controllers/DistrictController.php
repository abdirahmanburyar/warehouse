<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\District;
use App\Http\Resources\DistrictResource;
use Inertia\Inertia;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $districts = District::query();

        if($request->filled('search')) {
            $districts->where('district_name', 'LIKE', "%{$request->search}%")
                ->orWhere('region_name', 'LIKE', "%{$request->search}%");
        }
        
        $districts = $districts->paginate(500);

        $regions = District::distinct()->get(['region_name']);
        return Inertia::render('District/Index', [
            'regions' => $regions,
            'districts' => DistrictResource::collection($districts),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:districts,id',
                'district_name' => 'required|string|max:255',
                'region_name' => 'required|string|max:255',
            ]);

            if(District::where('district_name', $validated['district_name'])->where('region_name', $validated['region_name'])->exists() && !$request->id) {
                return response()->json('District name already exists', 400);
            }
    
            District::updateOrCreate(['id' => $request->id], $validated);
            return response()->json('District saved successfully', 200);
        } catch (\Throwable $th) {
            return response()->json('Failed to save district', 500);
        }
    }

    public function destroy($id)
    {
        District::destroy($id);
        return response()->json('District deleted successfully', 200);
    }

    // API methods
    public function apiGetDistricts()
    {
        $districts = District::all();
        return response()->json(['districts' => $districts]);
    }

    public function apiGetRegions()
    {
        $regions = District::distinct()->get(['region_name']);
        return response()->json(['regions' => $regions]);
    }
}
