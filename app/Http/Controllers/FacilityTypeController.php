<?php

namespace App\Http\Controllers;

use App\Models\FacilityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\FacilityTypeResource;
use Inertia\Inertia;
use Throwable;

class FacilityTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = FacilityType::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Pagination
        $perPage = $request->input('per_page', 25);
        $facilityTypes = $query->paginate($perPage);

        return Inertia::render('Product/FacilityType/Index', [
            'facilityTypes' => FacilityTypeResource::collection($facilityTypes),
            'filters' => $request->only(['search', 'status', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Product/FacilityType/Create');
    }

    public function edit(Request $request, $facilityType)
    {
        $facilityType = FacilityType::find($facilityType);
        return inertia('Product/FacilityType/Edit', [
            'facilityType' => $facilityType,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validator = $request->validate([
                'id' => ['nullable', 'exists:facility_types,id'],
                'name' => ['required', 'string', 'max:255', 'unique:facility_types,name,' . $request->id],
            ]);

            $facilityType = FacilityType::updateOrCreate(
                [
                    'id' => $request->id,
                ],
                [
                    'name' => $request->name,
                    'is_active' => true,
                ]
            );

            return response()->json($request->id ? "Success" : $facilityType->name, 200);
        } catch (Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function toggleStatus(FacilityType $facilityType)
    {
        try {
            $facilityType->update([
                'is_active' => !$facilityType->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Facility Type status updated successfully',
                'data' => new FacilityTypeResource($facilityType),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the facility type status.',
            ], 500);
        }
    }

    public function destroy(FacilityType $facilityType)
    {
        try {
            $facilityType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Facility Type deleted successfully',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the facility type.',
            ], 500);
        }
    }
}
