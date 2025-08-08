<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // System Management (Full Access)
            'manager-system',
            
            // User Management
            'user-manage',
            'user-view',
            'user-create',
            'user-edit',
            'user-toggle',
            'user-permission',
            
            // Order Management (with approval workflow)
            'order-manage',
            'order-view',
            'order-create',
            'order-edit',
            'order-toggle',
            'order-review',
            'order-approve',
            'order-reject',
            'order-delivery',
            'order-receive',
            'order-dispatch',
            
            // Product Management
            'product-manage',
            'product-view',
            'product-create',
            'product-edit',
            'product-toggle',
            
            // Inventory Management
            'inventory-manage',
            'inventory-view',
            'inventory-create',
            'inventory-edit',
            'inventory-toggle',
            
            // Transfer Management (with approval workflow)
            'transfer-manage',
            'transfer-view',
            'transfer-create',
            'transfer-edit',
            'transfer-toggle',
            'transfer-review',
            'transfer-approve',
            'transfer-reject',
            'transfer-delivery',
            'transfer-receive',
            'transfer-dispatch',
            
            // Asset Management (with approval workflow)
            'asset-manage',
            'asset-view',
            'asset-create',
            'asset-edit',
            'asset-delete',
            'asset-approve',
            'asset-bulk-import',
            'asset-export',
            
            // Purchase Order Management (with approval workflow)
            'purchase-order-manage',
            'purchase-order-view',
            'purchase-order-create',
            'purchase-order-edit',
            'purchase-order-toggle',
            'purchase-order-review',
            'purchase-order-approve',
            'purchase-order-reject',
            
            // Facility Management
            'facility-manage',
            'facility-view',
            'facility-create',
            'facility-edit',
            'facility-toggle',
            
            // Warehouse Management
            'warehouse-manage',
            'warehouse-view',
            'warehouse-create',
            'warehouse-edit',
            'warehouse-toggle',
            
            // Reports
            'report-view',
            'report-export',
            
            // Settings
            'setting-manage',
            'setting-view',
            
            // Dashboard Access
            'dashboard-view',

            // Supplier Management
            'supplier-manage',
            'supplier-view',
            'supplier-create',
            'supplier-edit',
            'supplier-toggle',
            
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
    }
}
