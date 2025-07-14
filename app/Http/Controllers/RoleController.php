<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Events\GlobalPermissionChanged;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        $query = Role::with(['permissions', 'users'])
            ->withCount('users');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Permission filter
        if ($request->filled('permission')) {
            $permissionId = $request->permission;
            $query->whereHas('permissions', function ($q) use ($permissionId) {
                $q->where('permissions.id', $permissionId);
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $roles = $query->paginate($perPage);

        $permissions = Permission::all();
        
        return Inertia::render('Role/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'permission', 'per_page', 'page'])
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all();
        
        return Inertia::render('Role/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all();
        
        return Inertia::render('Role/Edit', [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    /**
     * Get all roles and permissions for AJAX requests.
     */
    public function getAllRolesAndPermissions()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name]);
        
        // Sync permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        // Send a broadcast to every user who has this role
        User::role($role->name)->get()->each(function ($user) use ($role) {
            event(new GlobalPermissionChanged($user));
        });

        return redirect()->route('settings.roles.index')->with('success', 'Role created successfully');
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Don't allow editing the admin role name
        if ($role->name === 'admin' && $request->name !== 'admin') {
            return redirect()->back()->withErrors(['name' => 'Cannot modify the admin role name']);
        }

        $role->update(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        // Send a broadcast to every user who has this role
        User::role($role->name)->get()->each(function ($user) use ($role) {
            event(new GlobalPermissionChanged($user));
        });

        return redirect()->route('settings.roles.index')->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'Cannot delete the admin role');
        }
        
        $role->delete();

        return redirect()->route('settings.roles.index')->with('success', 'Role deleted successfully');
    }
    
    /**
     * Assign roles to a user.
     */
    public function assignRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->syncRoles($request->roles);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Roles assigned successfully',
                'user' => $user->load('roles')
            ]);
        }

        return redirect()->back()->with('success', 'Roles assigned successfully');
    }
}
