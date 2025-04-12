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
                'model' => 'required',
                'action' => 'required|in:confirm,verify,approve',
                'sequence' => 'required|integer|min:1',
                'notes' => 'nullable|string',
            ]);
            
            Approval::updateOrCreate(['id' => $validated['id']], $validated);

            return response()->json( $request->id ? 'Approval updated successfully.' : 'Approval created successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
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
