<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApprovalResource;
use App\Models\Approval;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    /**
     * Display a listing of approvals.
     */
    public function index(Request $request)
    {
        $approvals = Approval::query();

        if($request->filled('search')) {
            $approvals->whereHas('role', function($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%');
            });
        }
        
        $approvals = $approvals->with('role')->get();
        $roles = Role::all();
        
        return Inertia::render('Approval/Index', [
            'approvals' => ApprovalResource::collection($approvals),
            'roles' => $roles,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Store a newly created or update an existing approval.
     */
    public function store(Request $request)
    {
        try {       
            $validated = $request->validate([
                'id' => 'nullable|exists:approvals,id',
                'role_id' => 'required|exists:roles,id',
                'activity_type' => 'required|in:transfer,order,all',
                'approval_level' => 'required|integer|min:1',
                'is_active' => 'boolean',
                'description' => 'nullable|string',
            ]);
            
            // Check if another role already has this approval level for this activity type
            $existingApproval = Approval::where('activity_type', $validated['activity_type'])
                ->where('approval_level', $validated['approval_level'])
                ->where('role_id', '!=', $validated['role_id'])
                ->when($request->id, function($query) use ($request) {
                    return $query->where('id', '!=', $request->id);
                })
                ->first();
                
            if ($existingApproval) {
                return response()->json("Level $request->approval_level for activity type $request->activity_type already assigned to another role, please choose a different level.", 500);
            }
            
            // Check if this role already has an approval for this activity type
            $duplicateRoleApproval = Approval::where('activity_type', $validated['activity_type'])
                ->where('role_id', $validated['role_id'])
                ->when($request->id, function($query) use ($request) {
                    return $query->where('id', '!=', $request->id);
                })
                ->first();
                
            if ($duplicateRoleApproval) {
                return response()->json("This role already has an approval rule for $request->activity_type activity type. Each role can only have one approval rule per activity type.", 500);
            }
            
            Approval::updateOrCreate(['id' => $validated['id']], $validated);

            return response()->json( $request->id ? 'Approval updated successfully.' : 'Approval created successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Show the form for editing the specified approval.
     */
    public function edit(Approval $approval)
    {
        $roles = Role::all();
        
        return Inertia::render('Approval/Edit', [
            'approval' => new ApprovalResource($approval),
            'roles' => $roles,
        ]);
    }

    /**
     * Remove the specified approval from storage.
     */
    public function destroy(Approval $approval)
    {
       try {
            $approval->delete();
            
            if (request()->wantsJson()) {
                return response()->json('Approval deleted successfully.', 200);
            }

            return response()->json('Approval deleted successfully.', 200);
       } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
       }
    }
}
