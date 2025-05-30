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
                    $q->where('name', 'like', "%{$request->search}%");
                });
        }
        
        $facilities = $facilities->with(['user'])->paginate($request->per_page ?? 10, ['*'], 'page', $request->get('page', 1));

        $facilities->setPath(url()->current());

        return inertia('Facility/Index', [
            'facilities' => FacilityResource::collection($facilities),
            'users' => User::get(),
            'filters' => $request->only('page', 'per_page', 'search','district'),
            'districts' => District::pluck('name')->toArray(),
        ]);
    }

    public function show(Request $request, $id){
        $facility = Facility::find($id);
        return inertia('Facility/Show', [
            'facility' => $facility,
            'currentTab' => $request->get('tab', 'inventory')
        ]);
    }

    // tabs
    public function inventory(Request $request, $id){
        $facility = Facility::find($id);
        return inertia('Facility/Inventory', [
            'facility' => $facility,
            'currentTab' => $request->get('tab', 'inventory')
        ]);
    }

    public function dispence(Request $request, $id){
        $facility = Facility::find($id);
        return inertia('Facility/Dispence', [
            'facility' => $facility,
            'currentTab' => $request->get('tab', 'inventory')
        ]);
    }

    public function stock(Request $request, $id){
        $facility = Facility::find($id);
        return inertia('Facility/Stock', [
            'facility' => $facility,
            'currentTab' => $request->get('tab', 'inventory')
        ]);
    }

    public function expiry(Request $request, $id){
        $facility = Facility::find($id);
        return inertia('Facility/Expiry', [
            'facility' => $facility,
            'currentTab' => $request->get('tab', 'inventory')
        ]);
    }
    
    public function create()
    {
        return inertia('Facility/Create', [
            'users' => User::get(),
            'districts' => District::pluck('name')->toArray(),
        ]);
    }
    
    public function edit(Facility $facility)
    {
        return inertia('Facility/Edit', [
            'facility' => $facility,
            'users' => User::get(),
            'districts' => District::pluck('name')->toArray(),
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
                'district' => 'required|string',
                'facility_type' => 'required|string|max:50',
                'has_cold_storage' => 'boolean',
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

    public function toggleStatus(Facility $facility)
    {
        try {
            $facility->is_active = !$facility->is_active;
            $facility->save();
            return response()->json('Facility status toggled successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
