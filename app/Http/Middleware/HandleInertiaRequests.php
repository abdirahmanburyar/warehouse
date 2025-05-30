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
                'roles' => $request->user() ? $request->user()->roles->pluck('id') : [],
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
     * Get user permissions as a flattened object for easier checking in Vue components
     * 
     * @param Request $request
     * @return array
     */
    protected function getUserPermissions(Request $request): array
    {
        if (!$request->user()) {
            return [];
        }
        
        $permissions = $request->user()->getAllPermissions()->pluck('name')->toArray();
        $formattedPermissions = [];
        
        // Convert permissions to a flattened object for easier checking in Vue
        // e.g., 'order.view' becomes 'order_view' => true
        foreach ($permissions as $permission) {
            $key = str_replace('.', '_', $permission);
            $formattedPermissions[$key] = true;
        }
        
        return $formattedPermissions;
    }
}
