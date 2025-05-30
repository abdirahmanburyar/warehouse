<?php

namespace App\Services;

use App\Events\UserPermissionChanged;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    /**
     * Give a permission to a user and dispatch an event.
     *
     * @param User $user
     * @param string|Permission $permission
     * @return void
     */
    public function givePermissionTo(User $user, $permission)
    {
        $permissionName = $permission instanceof Permission ? $permission->name : $permission;
        
        // Only proceed if the user doesn't already have this permission
        if (!$user->hasPermissionTo($permissionName)) {
            $user->givePermissionTo($permissionName);
            
            // Dispatch event
            $event = new UserPermissionChanged(
                $user,
                $permissionName,
                'added',
                Auth::user() // The user who made the change (current authenticated user)
            );
            
            // Log the event dispatch
            \Illuminate\Support\Facades\Log::info('Dispatching permission added event', [
                'user_id' => $user->id,
                'permission' => $permissionName,
                'action' => 'added',
                'changed_by' => Auth::user() ? Auth::user()->name : 'System'
            ]);
            
            // Update the permission_updated_at timestamp
            $user->permission_updated_at = now();
            $user->save();
            
            event($event);
        }
    }
    
    /**
     * Remove a permission from a user and dispatch an event.
     *
     * @param User $user
     * @param string|Permission $permission
     * @return void
     */
    public function revokePermissionTo(User $user, $permission)
    {
        $permissionName = $permission instanceof Permission ? $permission->name : $permission;
        
        // Only proceed if the user has this permission
        if ($user->hasPermissionTo($permissionName)) {
            $user->revokePermissionTo($permissionName);
            
            // Dispatch event
            $event = new UserPermissionChanged(
                $user,
                $permissionName,
                'removed',
                Auth::user() // The user who made the change (current authenticated user)
            );
            
            // Log the event dispatch
            \Illuminate\Support\Facades\Log::info('Dispatching permission removed event', [
                'user_id' => $user->id,
                'permission' => $permissionName,
                'action' => 'removed',
                'changed_by' => Auth::user() ? Auth::user()->name : 'System'
            ]);
            
            // Update the permission_updated_at timestamp
            $user->permission_updated_at = now();
            $user->save();
            
            event($event);
        }
    }
    
    /**
     * Sync permissions for a user and dispatch events for changes.
     *
     * @param User $user
     * @param array $permissions
     * @return void
     */
    public function syncPermissions(User $user, array $permissions)
    {
        // Get current permissions
        $currentPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        
        // Identify permissions to add and remove
        $permissionsToAdd = array_diff($permissions, $currentPermissions);
        $permissionsToRemove = array_diff($currentPermissions, $permissions);
        
        // If there are changes, sync and dispatch events
        if (!empty($permissionsToAdd) || !empty($permissionsToRemove)) {
            // Sync the permissions
            $user->syncPermissions($permissions);
            
            // Dispatch events for added permissions
            foreach ($permissionsToAdd as $permission) {
                event(new UserPermissionChanged(
                    $user,
                    $permission,
                    'added',
                    Auth::user()
                ));
            }
            
            // Dispatch events for removed permissions
            foreach ($permissionsToRemove as $permission) {
                event(new UserPermissionChanged(
                    $user,
                    $permission,
                    'removed',
                    Auth::user()
                ));
            }
        }
    }
}
