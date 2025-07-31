<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\Facility;
use Inertia\Inertia;
use App\Models\Approval;
use App\Http\Resources\ApprovalResource;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'General');
        
        // Get users with filtering if tab is 'users'
        $users = User::with('warehouse','facility');
        
        if ($tab === 'users') {
            // Apply search filter if provided
            if ($request->filled('search')) {
                $search = $request->input('search');
                $users->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }
            
            // Apply sorting if provided
            if ($request->filled('sort_field')) {
                $sortField = $request->input('sort_field', 'created_at');
                $sortDirection = $request->input('sort_direction', 'desc');
                $users->orderBy($sortField, $sortDirection);
            } else {
                $users->orderBy('created_at', 'desc');
            }
        }
        


        // Get approvals with filtering if tab is 'approvals'
        $approvals = Approval::query();

        if ($tab === 'approvals') {
            // Apply search filter if provided
            if ($request->filled('search')) {
                $search = $request->input('search');
                $approvals->where(function($q) use ($search) {
                    $q->where('activity_type', 'like', "%{$search}%")
                      ->orWhere('approval_level', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            

        }
        
        // Extract filters but only include non-empty ones
        $filters = [];
        if ($request->filled('search')) $filters['search'] = $request->input('search');
        if ($request->filled('sort_field')) $filters['sort_field'] = $request->input('sort_field');
        if ($request->filled('sort_direction')) $filters['sort_direction'] = $request->input('sort_direction');
        
        return Inertia::render('Settings/Index', [
            'approvals' => ApprovalResource::collection($approvals->paginate(10)),
            'users' => UserResource::collection($users->paginate(10)->withQueryString()),
            'permissions' => Permission::all(),
            'warehouses' => Warehouse::get(),
            'activeTab' => $tab,
            'filters' => $request->only('search', 'sort_field', 'sort_direction', 'tab'),
            'facilities' => Facility::get(),
        ]);
    }
}
