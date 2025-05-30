<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        return Inertia::render('Role/Index', [
            'roles' => $roles,
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
     * Store a newly created role or update an existing one.
     */
    public function store(Request $request)
    {
        // Check if this is an update operation (id is provided)
        $isUpdate = $request->has('id') && $request->id;
        $roleId = $isUpdate ? $request->id : null;
        
        // Validate the request with conditional unique rule
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name' . ($isUpdate ? ',' . $roleId : ''),
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Create or update the role
        if ($isUpdate) {
            $role = Role::findOrFail($roleId);
            
            // Don't allow editing the administrator role name
            if ($role->name === 'administrator' && $request->name !== 'administrator') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot modify the administrator role name'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Cannot modify the administrator role name');
            }
            
            $role->update(['name' => $request->name]);
            $successMessage = 'Role updated successfully';
        } else {
            $role = Role::create(['name' => $request->name]);
            $successMessage = 'Role created successfully';
        }
        
        // Sync permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        // Return JSON response if requested
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'role' => $role->load('permissions')
            ]);
        }

        // Check if request from settings page
        $isFromSettings = $request->header('X-From-Settings') || 
                         ($request->has('_headers') && $request->_headers && isset($request->_headers['X-From-Settings']));
        
        if ($isFromSettings) {
            return redirect()->route('settings.index', ['tab' => 'roles'])->with('success', $successMessage);
        }

        return redirect()->route('settings.roles.index')->with('success', $successMessage);
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

        $role->update(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => $role->load('permissions')
            ]);
        }

        // Check if request from settings page
        $isFromSettings = $request->header('X-From-Settings') || 
                         ($request->has('_headers') && $request->_headers && isset($request->_headers['X-From-Settings']));
        
        if ($isFromSettings) {
            return redirect()->route('settings.index', ['tab' => 'roles'])->with('success', 'Role updated successfully');
        }

        return redirect()->route('settings.roles.index')->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            // Check if request from settings page
            $isFromSettings = request()->header('X-From-Settings') || 
                             (request()->has('_headers') && request()->_headers && isset(request()->_headers['X-From-Settings']));
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the admin role'
                ], 403);
            }
            
            if ($isFromSettings) {
                return redirect()->route('settings.index', ['tab' => 'roles'])->with('error', 'Cannot delete the admin role');
            }
            
            return redirect()->route('settings.roles.index')->with('error', 'Cannot delete the admin role');
        }
        
        $role->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        }

        // Check if request from settings page
        $isFromSettings = request()->header('X-From-Settings') || 
                         (request()->has('_headers') && request()->_headers && isset(request()->_headers['X-From-Settings']));
        
        if ($isFromSettings) {
            return redirect()->route('settings.index', ['tab' => 'roles'])->with('success', 'Role deleted successfully');
        }

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

        return back()->with('success', 'Roles assigned successfully');
    }
}
