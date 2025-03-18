<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for warehouses
        $warehousePermissions = [
            'warehouse.view',
            'warehouse.create',
            'warehouse.edit',
            'warehouse.delete',
        ];

        // Create permissions for categories
        $categoryPermissions = [
            'category.view',
            'category.create',
            'category.edit',
            'category.delete',
        ];

        // Create permissions for users
        $userPermissions = [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
        ];

        // Create permissions for reports
        $reportPermissions = [
            'report.view',
            'report.create',
            'report.export',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $warehousePermissions,
            $categoryPermissions,
            $userPermissions,
            $reportPermissions
        );

        // Create each permission
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin role - has all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Manager role - can manage warehouses and categories but not users
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            ...$warehousePermissions,
            ...$categoryPermissions,
            ...$reportPermissions,
            'user.view',
        ]);

        // Warehouse operator role - can view and edit warehouses but not delete
        $operatorRole = Role::create(['name' => 'operator']);
        $operatorRole->givePermissionTo([
            'warehouse.view',
            'warehouse.edit',
            'category.view',
            'report.view',
        ]);

        // Viewer role - can only view data
        $viewerRole = Role::create(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'warehouse.view',
            'category.view',
            'report.view',
        ]);

        // Assign admin role to the first user (assuming it's the admin)
        $admin = User::first();
        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}
