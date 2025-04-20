<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\User;
use App\Models\District;
use Illuminate\Http\Request;
use App\Http\Resources\FacilityResource;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $facilities = Facility::query();

        if($request->has('search')) {
            $facilities->where('name', 'like', "%{$request->search}%")
                ->orWhereHas('district', function($q) use ($request) {
                    $q->where('district_name', 'like', "%{$request->search}%")
                        ->orWhere('region_name', 'like', "%{$request->search}%");
                });
        }
        
        $facilities = $facilities->with(['user','district'])->paginate($request->per_page ?? 10, ['*'], 'page', $request->get('page', 1));

        $facilities->setPath(url()->current());

        return inertia('Facility/Index', [
            'facilities' => FacilityResource::collection($facilities),
            'users' => User::get(),
            'filters' => $request->only('page', 'per_page', 'search'),
            'districts' => District::get(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:facilities,email,' . $request->id,
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'nullable|string|max:100',
                'district_id' => 'required',
                'facility_type' => 'required|string|max:50',
                'has_cold_storage' => 'boolean',
                'special_handling_capabilities' => 'nullable|string',
                'is_24_hour' => 'boolean',
                'is_active' => 'boolean',
                'user_id' => 'required'
            ]);

            Facility::updateOrCreate(['id' => $request->id], $validated);

            return response()->json($request->id ? 'Facility updated successfully.' : 'Facility created successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function destroy(Facility $facility)
    {
        try {
            $facility->delete();    
            return response()->json('Facility deleted successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
