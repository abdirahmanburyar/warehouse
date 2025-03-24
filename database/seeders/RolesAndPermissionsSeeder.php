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

        // Create permissions for settings
        $settingsPermissions = [
            'settings.view',
            'settings.create',
            'settings.edit',
            'settings.delete',
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

        // Create permissions for approvals
        $approvalPermissions = [
            'approval.view',
            'approval.create',
            'approval.edit',
            'approval.delete',
        ];

        // Create permissions for dosages
        $dosagePermissions = [
            'dosage.view',
            'dosage.create',
            'dosage.edit',
            'dosage.delete',
        ];

        // Create permissions for products
        $productPermissions = [
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
        ];

        // Create permissions for inventories
        $inventoryPermissions = [
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $warehousePermissions,
            $categoryPermissions,
            $userPermissions,
            $reportPermissions,
            $approvalPermissions,
            $dosagePermissions,
            $productPermissions,
            $inventoryPermissions,
            $settingsPermissions
        );

        // Create permissions if they don't exist
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin role - has all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Manager role - can manage warehouses and categories but not users
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->givePermissionTo([
            ...$warehousePermissions,
            ...$categoryPermissions,
            ...$productPermissions,
            ...$dosagePermissions,
            ...$approvalPermissions,
            ...$reportPermissions,
            'user.view',
        ]);

        // Warehouse operator role - can view and edit warehouses but not delete
        $operatorRole = Role::firstOrCreate(['name' => 'operator']);
        $operatorRole->givePermissionTo([
            'warehouse.view',
            'warehouse.edit',
            'category.view',
            'product.view',
            'dosage.view',
            'report.view',
        ]);

        // Viewer role - can only view data
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'warehouse.view',
            'category.view',
            'product.view',
            'dosage.view',
            'report.view',
        ]);

        // Assign admin role to the first user (assuming it's the admin)
        $admin = User::first();
        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}
