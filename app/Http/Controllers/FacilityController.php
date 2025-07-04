<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\User;
use App\Models\District;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Resources\FacilityResource;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Jobs\ImportFacilitiesJob;

class FacilityController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            
            // Store file in storage/app/temp
            $path = $file->store('temp');
            $fullPath = storage_path('app/' . $path);
            
            // Dispatch job to process the file
            ImportFacilitiesJob::dispatch($fullPath);

            return response()->json([
                'message' => 'File uploaded successfully. Facilities will be imported in the background.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function index(Request $request)
    {
        $facilities = Facility::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->when($request->filled('district'), function ($query) use ($request) {
                $query->where('district', 'like', "%{$request->district}%");
            })
            ->with(['user','handledby'])
            ->paginate($request->per_page ?? 10, ['*'], 'page', $request->page ?? 1);

        $facilities = $facilities->setPath(url()->current());



        return inertia('Facility/Index', [
            'facilities' => FacilityResource::collection($facilities),
            'users' => User::get(),
            'filters' => $request->only('page', 'per_page', 'search','district'),
            'districts' => District::pluck('name')->toArray(),
            'regions' => Region::pluck('name')->toArray(),
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
            'regions' => Region::pluck('name')->toArray()
        ]);
    }
    
    public function edit(Facility $facility)
    {
        return inertia('Facility/Edit', [
            'facility' => $facility,
            'users' => User::get(),
            'regions' => Region::pluck('name')->toArray()
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
                'region' => 'required|string',
                'handled_by' => 'required',
                'facility_type' => 'required|string|max:50',
                'has_cold_storage' => 'boolean',
                'is_active' => 'boolean',
                'user_id' => 'required'
            ]);

            $validated['handled_by'] = $request->handled_by['id'];
            $validated['user_id'] = $request->user_id['id'];
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

    public function getFacilities(Request $request){
        try {
            $facilities = Facility::where('district', 'like', "%{$request->district}%")->get();
        return response()->json($facilities);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
