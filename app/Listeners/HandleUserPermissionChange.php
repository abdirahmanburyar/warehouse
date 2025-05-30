<?php

namespace App\Listeners;

use App\Events\UserPermissionChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HandleUserPermissionChange
{

    /**
     * Handle the event.
     *
     * @param  \App\Events\UserPermissionChanged  $event
     * @return void
     */
    public function handle(UserPermissionChanged $event)
    {
        // Log the permission change
        Log::info('User permission changed', [
            'user_id' => $event->user->id,
            'user_name' => $event->user->name,
            'permission' => $event->permission,
            'action' => $event->action,
            'changed_by' => $event->changedBy ? $event->changedBy->name : 'System',
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Clear the user's permission cache
        $this->clearUserPermissionCache($event->user);

        // If the user is currently logged in, force them to re-authenticate
        // This will be handled by the middleware checking permissions
        $this->invalidateUserSessions($event->user);
    }

    /**
     * Clear the user's permission cache.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function clearUserPermissionCache($user)
    {
        // Clear any cached permissions for this user
        Cache::forget('permissions_' . $user->id);
    }

    /**
     * Invalidate all sessions for the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function invalidateUserSessions($user)
    {
        // Set a flag in the database that the user's permissions have changed
        $user->permission_updated_at = now();
        $user->save();
    }
}
