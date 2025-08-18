<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates for all permissions
        $this->definePermissionGates();

        // Define special access gates
        $this->defineSpecialGates();
    }

    /**
     * Define permission-based gates.
     */
    protected function definePermissionGates(): void
    {
        // Get all permissions from database and create gates
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            Gate::define($permission->name, function (User $user) use ($permission) {
                return $user->hasPermission($permission->name);
            });
        }
    }

    /**
     * Define special access gates for admin and manager roles.
     */
    protected function defineSpecialGates(): void
    {
        // Admin has access to everything
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        // Manager system has access to everything
        Gate::define('manager-access', function (User $user) {
            return $user->hasPermission('manager-system');
        });

        // View-only access - can view but not modify
        Gate::define('view-only', function (User $user) {
            return $user->hasPermission('view-only-access');
        });

        // Module-specific access gates
        Gate::define('user-management', function (User $user) {
            return $user->hasAnyPermission(['user-view', 'user-create', 'user-edit', 'user-delete']);
        });

        Gate::define('facility-management', function (User $user) {
            return $user->hasAnyPermission(['facility-view', 'facility-create', 'facility-edit', 'facility-delete', 'facility-import']);
        });

        Gate::define('product-management', function (User $user) {
            return $user->hasAnyPermission(['product-view', 'product-create', 'product-edit', 'product-delete', 'product-import']);
        });

        Gate::define('inventory-management', function (User $user) {
            return $user->hasAnyPermission(['inventory-view', 'inventory-adjust', 'inventory-transfer']);
        });

        Gate::define('warehouse-management', function (User $user) {
            return $user->hasAnyPermission(['warehouse-view', 'warehouse-manage']);
        });

        Gate::define('reports-access', function (User $user) {
            return $user->hasAnyPermission(['reports-view', 'reports-export']);
        });

        Gate::define('system-administration', function (User $user) {
            return $user->hasAnyPermission(['system-settings', 'permission-manage']);
        });
    }
}
