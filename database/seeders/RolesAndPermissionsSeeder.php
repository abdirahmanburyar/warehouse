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

        // 1. User Management
        $userPermissions = [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
        ];

        // 2. Role Management
        $rolePermissions = [
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
        ];

        // 3. Category Management
        $categoryPermissions = [
            'category.view',
            'category.create',
            'category.edit',
            'category.delete',
        ];

        // 4. Product Management
        $productPermissions = [
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',
            'product.import',
        ];

        // 5. Warehouse Management
        $warehousePermissions = [
            'warehouse.view',
            'warehouse.create',
            'warehouse.edit',
            'warehouse.delete',
        ];

        // 6. Location Management
        $locationPermissions = [
            'location.view',
            'location.create',
            'location.edit',
            'location.delete',
        ];

        // 7. Dosage Management
        $dosagePermissions = [
            'dosage.view',
            'dosage.create',
            'dosage.edit',
            'dosage.delete',
        ];

        // 8. Supplier Management
        $supplierPermissions = [
            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',
        ];

        // 9. Facility Management
        $facilityPermissions = [
            'facility.view',
            'facility.create',
            'facility.edit',
            'facility.delete',
            'facility.inventory',
            'facility.dispense',
        ];

        // 10. District Management
        $districtPermissions = [
            'district.view',
            'district.create',
            'district.edit',
            'district.delete',
        ];

        // 11. Inventory Management
        $inventoryPermissions = [
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'inventory.adjust',
            'inventory.audit',
        ];

        // 12. Supply Chain Management
        $supplyPermissions = [
            'supply.view',
            'supply.create',
            'supply.edit',
            'supply.delete',
            'supply.approve',
            'supply.reject',
            'supply.review',
        ];

        // 13. Purchase Order Management
        $purchaseOrderPermissions = [
            'purchase-order.view',
            'purchase-order.create',
            'purchase-order.edit',
            'purchase-order.delete',
            'purchase-order.approve',
            'purchase-order.reject',
            'purchase-order.review',
            'purchase-order.audit',
        ];

        // 14. Packing List Management
        $packingListPermissions = [
            'packing-list.view',
            'packing-list.create',
            'packing-list.edit',
            'packing-list.approve',
            'packing-list.reject',
            'packing-list.review',
            'packing-list.audit',
        ];

        // 15. Transfer Management
        $transferPermissions = [
            'transfer.view',
            'transfer.create',
            'transfer.edit',
            'transfer.delete',
            'transfer.in_process',
            'transfer.dispatch',
            'transfer.approve',
            'transfer.reject',
            'transfer.receive',
            'transfer.audit',
        ];

        // 16. Order Management
        $orderPermissions = [
            'order.view',
            'order.create',
            'order.edit',
            'order.delete',
            'order.in_process',
            'order.dispatch',
            'order.approve',
            'order.reject',
            'order.receive',
            'order.change-status',
            'order.bulk-change-status',
            'order.audit',
        ];

        // 17. Dispatch Management
        $dispatchPermissions = [
            'dispatch.view',
            'dispatch.create',
            'dispatch.edit',
            'dispatch.delete',
            'dispatch.in_process',
            'dispatch.dispatch',
            'dispatch.approve',
            'dispatch.reject',
            'dispatch.receive',
            'dispatch.audit',
        ];

        // 18. Report Management
        $reportPermissions = [
            'report.view',
            'report.monthly-consumption',
            'report.stock-level',
            'report.upload-consumption',
            'report.audit',
            'report.physical-count-review',
            'report.physical-count-approve',
            'report.physical-count-generate',
            'report.physical-count-view',
            'report.physical-count-submit',
            'report.physical-count-rollback',
        ];

        // 19. Approval Management
        $approvalPermissions = [
            'approval.view',
            'approval.approve',
            'approval.reject',
            'approval.audit',
        ];

        // 20. Liquidation and Disposal
        $liquidateDisposalPermissions = [
            'liquidate.view',
            'liquidate.create',
            'liquidate.approve',
            'liquidate.reject',
            'liquidate.review',
            'liquidate.audit',
            'disposal.view',
            'disposal.create',
            'disposal.approve',
            'disposal.reject',
            'disposal.review',
            'disposal.audit',
        ];

        // 21. Settings Management
        $settingsPermissions = [
            'settings.view',
            'settings.create',
            'settings.edit',
            'settings.delete',
            'settings.audit',
        ];

        $assetPermissions = [
            'asset.view',
            'asset.create',
            'asset.edit',
            'asset.delete',
            'asset.import',
            'asset.export',
            'asset.approve',
            'asset.reject',
            'asset.review',
            'asset.audit',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $userPermissions,
            $rolePermissions,
            $categoryPermissions,
            $productPermissions,
            $warehousePermissions,
            $locationPermissions,
            $dosagePermissions,
            $supplierPermissions,
            $facilityPermissions,
            $districtPermissions,
            $inventoryPermissions,
            $supplyPermissions,
            $purchaseOrderPermissions,
            $packingListPermissions,
            $transferPermissions,
            $orderPermissions,
            $dispatchPermissions,
            $reportPermissions,
            $approvalPermissions,
            $liquidateDisposalPermissions,
            $settingsPermissions,
            $assetPermissions
        );

        // Create permissions if they don't exist
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Administrator role - has all permissions
        $administratorRole = Role::firstOrCreate(['name' => 'administrator']);
        $administratorRole->givePermissionTo(Permission::all());

        // Manager role - can manage operations but not system settings
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerPermissions = array_merge(
            $userPermissions,
            $warehousePermissions,
            $facilityPermissions,
            $inventoryPermissions,
            $productPermissions,
            $categoryPermissions,
            $supplierPermissions,
            $orderPermissions,
            $transferPermissions,
            $reportPermissions,
            [
                // Limited approval permissions
                'approval.view',
                'approval.approve',
                
                // View-only for roles
                'role.view',
            ]
        );
        $managerRole->givePermissionTo($managerPermissions);

        // Supervisor role - can manage day-to-day operations
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorPermissions = array_merge(
            // View permissions for most modules
            ['user.view', 'role.view'],
            $warehousePermissions,
            $facilityPermissions,
            $inventoryPermissions,
            $productPermissions,
            $categoryPermissions,
            $orderPermissions,
            $transferPermissions,
            
            // Review permissions
            ['supply.review', 'purchase-order.review', 'packing-list.review'],
            
            // Report permissions
            ['report.view', 'report.monthly-consumption', 'report.stock-level']
        );
        $supervisorRole->givePermissionTo($supervisorPermissions);

        // Operator role - can perform basic operations
        $operatorRole = Role::firstOrCreate(['name' => 'operator']);
        $operatorPermissions = [
            // View permissions
            'warehouse.view',
            'facility.view',
            'inventory.view',
            'inventory.adjust',
            'product.view',
            'category.view',
            'order.view',
            'order.create',
            'transfer.view',
            'transfer.create',
            'report.view',
        ];
        $operatorRole->givePermissionTo($operatorPermissions);

        // Viewer role - can only view data
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerPermissions = [
            'warehouse.view',
            'facility.view',
            'inventory.view',
            'product.view',
            'category.view',
            'order.view',
            'transfer.view',
            'report.view',
        ];
        $viewerRole->givePermissionTo($viewerPermissions);

        // Assign administrator role to the first user (assuming it's the admin)
        $admin = User::first();
        if ($admin) {
            $admin->assignRole('administrator');
        }
    }
}
