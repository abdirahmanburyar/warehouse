<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Facility;
use App\Notifications\UserRegistered;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Http\Resources\UserResource;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Events\GlobalPermissionChanged;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        logger()->info($request->all());
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.name', $request->role);
            });
        }

        // is active
        if ($request->has('status') && $request->status != 'All') {
            $query->where('is_active', $request->status);
        }
        
        // Warehouse filter
        if ($request->filled('warehouse')) {
            $query->whereHas('warehouse', function ($q) use ($request) {
                $q->where('warehouses.name', $request->warehouse);
            });
        }
        
        // Facility filter
        if ($request->filled('facility')) {
            $query->whereHas('facility', function ($q) use ($request) {
                $q->where('facilities.name', $request->facility);
            });
        }

        $query->with(['roles', 'warehouse', 'facility'])->latest();

        
        $users = $query->paginate($request->per_page, ['*'], 'page', $request->page)->withQueryString();

        $users->setPath(url()->current());
        
        // Get all roles for filtering and the roles modal
        $roles = \Spatie\Permission\Models\Role::pluck('name')->toArray();
        
        // Get all warehouses for filtering and selection
        $warehouses = Warehouse::pluck('name')->toArray();

        // Get all facilities for filtering and selection
        $facilities = Facility::pluck('name')->toArray();
        
        return Inertia::render('User/Index', [
            'users' => UserResource::collection($users),
            'roles' => $roles,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'role', 'status', 'warehouse', 'facility', 'per_page']),
            'facilities' => $facilities
        ]);
    }

    /**
     * Store a newly created user or update an existing one.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'id' => 'nullable|exists:users,id',
                'name' => 'required|string|max:255',
                'username' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'unique:users,email,' . $request->id,
                ],
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'password' => $request->id ? 'nullable|string|min:8' : 'required|string|min:8',
                'facility_id' => 'nullable|exists:facilities,id',
                'role_ids' => 'nullable|array',
                'role_ids.*' => 'exists:roles,id',
                'direct_permissions' => 'nullable|array',
                'direct_permissions.*' => 'exists:permissions,id',
                'title' => 'required|string|max:255',
            ]);

            $userData = [
                'name' => $request->name,
                'username' => $request->username,
                'title' => $request->title,
                'email' => $request->email,
                'warehouse_id' => $request->warehouse_id,
                'facility_id' => $request->facility_id,
            ];

            // Store the original password for email notification
            $originalPassword = null;
            if ($request->filled('password')) {
                $originalPassword = $request->password;
                $userData['password'] = Hash::make($request->password);
            }
            
            // Check if this is a new user or an update
            $isNewUser = !$request->id;
            
            // Create or update the user
            $user = User::updateOrCreate(
                ['id' => $request->id],
                $userData
            );
            
            // Store original permissions and roles for comparison
            $originalDirectPermissions = $isNewUser ? [] : $user->getDirectPermissions()->pluck('id')->toArray();
            $originalRoleIds = $isNewUser ? [] : $user->roles()->pluck('id')->toArray();
            
            // Assign roles if provided
            if ($request->has('role_ids') && is_array($request->role_ids) && count($request->role_ids) > 0) {
                // Sync roles (this will detach any existing roles not in the array)
                $user->syncRoles($request->role_ids);
                
                // Track role changes for events
                $addedRoleIds = array_diff($request->role_ids, $originalRoleIds);
                $removedRoleIds = array_diff($originalRoleIds, $request->role_ids);
                
            }
            
            // Assign direct permissions if provided
            $newDirectPermissions = $request->has('direct_permissions') && is_array($request->direct_permissions) 
                ? $request->direct_permissions 
                : [];
                
            // Sync permissions
            $user->syncPermissions($newDirectPermissions);

            event(new GlobalPermissionChanged($user));
                        
            // Reload the user with relationships for the email
            $user->load(['roles', 'warehouse', 'facility']);
            
            // Send email notification for new users asynchronously
            if ($isNewUser) {
                // Queue the notification to prevent timeout issues
                $user->notifyNow(new UserRegistered($user, $originalPassword));
            }
            
            DB::commit();
            return response()->json($request->id ? 'User updated successfully' : 'User created successfully', 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }             
    }

    /**
     * Display the roles management page for a specific user.
     */
    public function showRoles(User $user)
    {
        $user->load('roles');
        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
        
        return Inertia::render('User/Roles', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    public function create(Request $request)
    {
        $warehouses = Warehouse::all();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $facilities = Facility::all();
        
        return Inertia::render('User/Create', [
            'warehouses' => $warehouses,
            'roles' => $roles,
            'permissions' => $permissions,
            'facilities' => $facilities
        ]);
    }
    
    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['roles.permissions', 'permissions', 'warehouse', 'facility']);
        $warehouses = Warehouse::all();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $facilities = Facility::all();
        
        return Inertia::render('User/Edit', [
            'user' => $user,
            'warehouses' => $warehouses,
            'roles' => $roles,
            'permissions' => $permissions,
            'facilities' => $facilities
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting your own account
            if ($user->id === auth()->id()) {
                $isFromSettings = request()->header('X-From-Settings') || 
                                 (request()->has('_headers') && request()->_headers && isset(request()->_headers['X-From-Settings']));
                
                if ($isFromSettings) {
                    return redirect()->back()->withErrors(['error' => 'You cannot delete your own account.']);
                }
                
                return response()->json('You cannot delete your own account.', 500);
            }
            
            $user->delete();

            $isFromSettings = request()->header('X-From-Settings') || 
                             (request()->has('_headers') && request()->_headers && isset(request()->_headers['X-From-Settings']));
            
            if ($isFromSettings) {
                return redirect()->route('settings.index', ['tab' => 'users'])->with('success', 'User deleted successfully.');
            }

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
        } catch (Throwable $e) {
            $isFromSettings = request()->header('X-From-Settings') || 
                             (request()->has('_headers') && request()->_headers && isset(request()->_headers['X-From-Settings']));
            
            if ($isFromSettings) {
                return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
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

        $isFromSettings = $request->header('X-From-Settings') || 
                         ($request->has('_headers') && $request->_headers && isset($request->_headers['X-From-Settings']));
        
        if ($isFromSettings) {
            return redirect()->route('settings.index', ['tab' => 'users'])->with('success', 'Roles assigned successfully');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Roles assigned successfully',
                'user' => $user->load('roles')
            ]);
        }

        return back()->with('success', 'Roles assigned successfully');
    }
    
    /**
     * Toggle a user's active status.
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_active' => 'required|boolean',
        ]);
        
        try {
            $user = User::findOrFail($request->user_id);
            $user->is_active = $request->is_active;
            $user->save();
            
            $statusText = $request->is_active ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "User {$statusText} successfully",
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle status for multiple users.
     */
    public function bulkToggleStatus(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'is_active' => 'required|boolean',
        ]);
        
        try {
            DB::beginTransaction();
            
            $count = User::whereIn('id', $request->user_ids)
                ->update(['is_active' => $request->is_active]);
            
            DB::commit();
            
            $statusText = $request->is_active ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "{$count} users {$statusText} successfully"
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
