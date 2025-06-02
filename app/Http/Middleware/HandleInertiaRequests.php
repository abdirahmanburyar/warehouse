<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $request->user() ? $request->user()->getAllPermissions()->pluck('name') : [],
                'roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
                'roleIds' => $request->user() ? $request->user()->roles->pluck('id') : [],
                'can' => $this->getUserPermissions($request),
            ],
            // show warehouse for the current user
            'warehouse' => $request->user() ? $request->user()->warehouse : null,            
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
    
    /**
     * Get the user permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getUserPermissions(Request $request): array
    {
        if (!$request->user()) {
            return [];
        }

        // Get all permissions for the user
        $permissions = $request->user()->getAllPermissions()->pluck('name');


        // Convert to a flattened can object for easier checking in Vue
        // e.g. 'order.view' becomes 'order_view' => true
        $flattenedPermissions = [];
        foreach ($permissions as $permission) {
            // Convert dot notation to underscore for Vue compatibility
            $key = str_replace(['.', '-'], '_', $permission);
            $flattenedPermissions[$key] = true;
        }

        return $flattenedPermissions;
    }
}
