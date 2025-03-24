<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Http\Resources\UserResource;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query()->with(['roles', 'warehouse']);
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $users = $query->paginate(10)->withQueryString();
        
        // Get all roles for the roles modal
        $roles = \Spatie\Permission\Models\Role::all();
        
        // Get all warehouses for the warehouse selection
        $warehouses = Warehouse::where('is_active', true)->get();
        
        return Inertia::render('User/Index', [
            'users' => UserResource::collection($users),
            'roles' => $roles,
            'warehouses' => $warehouses,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Store a newly created user or update an existing one.
     */
    public function store(Request $request)
    {
        try {
            $userId = $request->id;
            
            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'username' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'warehouse_id' => 'nullable|exists:warehouses,id',
            ];
            
            // Add unique validation with ignore for updates
            if ($userId) {
                $rules['username'][] = Rule::unique('users')->ignore($userId);
                $rules['email'][] = Rule::unique('users')->ignore($userId);
                $rules['password'] = 'nullable|string|min:8';
            } else {
                $rules['username'][] = 'unique:users';
                $rules['email'][] = 'unique:users';
                $rules['password'] = 'required|string|min:8';
            }
            
            $validated = $request->validate($rules);
            
            // Prepare data for updateOrCreate
            $userData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'warehouse_id' => $validated['warehouse_id'] ?? null,
            ];
            
            // Only update password if provided (for updates) or always for new users
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($validated['password']);
            }
            
            // UpdateOrCreate based on ID
            if ($userId) {
                $user = User::findOrFail($userId);
                $user->update($userData);
                $message = 'User updated successfully.';
            } else {
                $user = User::create($userData);
                $message = 'User created successfully.';
            }

            // Check if request from settings page
            $isFromSettings = $request->header('X-From-Settings') || 
                             ($request->has('_headers') && $request->_headers && isset($request->_headers['X-From-Settings']));
            
            if ($isFromSettings) {
                return redirect()->route('settings.index', ['tab' => 'users'])->with('success', $message);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => $user
            ]);
        } catch (Throwable $e) {
            if ($request->header('X-From-Settings') || 
                ($request->has('_headers') && $request->_headers && isset($request->_headers['X-From-Settings']))) {
                return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
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
                
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
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
}
