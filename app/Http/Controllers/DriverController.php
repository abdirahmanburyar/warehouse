<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\LogisticCompany;
use App\Http\Resources\DriverResource;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::query()
            ->when(request()->filled('search'), function ($query) {
                $query->where('name', 'like', '%' . request()->search . '%')
                    ->orWhere('phone', 'like', '%' . request()->search . '%')
                    ->orWhere('driver_id', 'like', '%' . request()->search . '%');
            })
            ->with('company')
            ->paginate(request()->input('per_page', 10), ['*'], 'page', request()->input('page', 1))
            ->withQueryString();
        
        $drivers->setPath(url()->current());

        return inertia('Settings/Driver/Index', [
            'drivers' => DriverResource::collection($drivers),
            'companies' => LogisticCompany::where('is_active', true)->get(),
            'filters' => request()->only('search', 'per_page', 'page', 'company')
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:drivers,id',
                'name' => 'required|string|max:255',
                'driver_id' => 'required|string|max:50|unique:drivers,driver_id,' . $request->id,
                'phone' => 'required|string|max:20',
                'logistic_company_id' => 'required|exists:logistic_companies,id',
                'is_active' => 'boolean'
            ]);

            $driver = Driver::updateOrCreate(
                ['id' => $request->id],
                $validated
            );

            return response()->json($request->id ? 'Driver updated successfully' : 'Driver created successfully', 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function toggleStatus(Driver $driver)
    {
        try {
            $driver->is_active = !$driver->is_active;
            $driver->save();
            return response()->json('Status updated successfully', 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(Driver $driver)
    {
        try {
            $driver->delete();
            return response()->json('Driver deleted successfully', 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
