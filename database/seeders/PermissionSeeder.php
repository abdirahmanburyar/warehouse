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
            // ========================================
            // SYSTEM-LEVEL PERMISSIONS
            // ========================================
            
            // System Management (Full Access)
            'manager-system',
            
            // ========================================
            // VIEW-ONLY PERMISSION (READ-ONLY ACCESS)
            // ========================================
            // This permission allows viewing all modules but prevents any actions
            'view-only-access',
            
            // ========================================
            // USER MANAGEMENT PERMISSIONS
            // ========================================
            'user-manage',      // Full user management access
            'user-view',        // View user information
            'user-create',      // Create new users
            'user-edit',        // Edit existing users
            'user-toggle',      // Activate/deactivate users
            'user-permission',  // Manage user permissions
            
            // ========================================
            // ORDER MANAGEMENT PERMISSIONS
            // ========================================
            'order-manage',     // Full order management access
            'order-view',       // View orders
            'order-create',     // Create new orders
            'order-edit',       // Edit existing orders
            'order-toggle',     // Activate/deactivate orders
            'order-review',     // Review orders
            'order-approve',    // Approve orders
            'order-reject',     // Reject orders
            'order-delivery',   // Manage order delivery
            'order-receive',    // Receive orders
            'order-dispatch',   // Dispatch orders
            
            // ========================================
            // PRODUCT MANAGEMENT PERMISSIONS
            // ========================================
            'product-manage',   // Full product management access
            'product-view',     // View products
            'product-create',   // Create new products
            'product-edit',     // Edit existing products
            'product-toggle',   // Activate/deactivate products
            
            // ========================================
            // INVENTORY MANAGEMENT PERMISSIONS
            // ========================================
            'inventory-manage', // Full inventory management access
            'inventory-view',   // View inventory
            'inventory-create', // Create inventory records
            'inventory-edit',   // Edit inventory records
            'inventory-toggle', // Activate/deactivate inventory
            
            // ========================================
            // TRANSFER MANAGEMENT PERMISSIONS
            // ========================================
            'transfer-manage',  // Full transfer management access
            'transfer-view',    // View transfers
            'transfer-create',  // Create new transfers
            'transfer-edit',    // Edit existing transfers
            'transfer-toggle',  // Activate/deactivate transfers
            'transfer-review',  // Review transfers
            'transfer-approve', // Approve transfers
            'transfer-reject',  // Reject transfers
            'transfer-delivery', // Manage transfer delivery
            'transfer-receive', // Receive transfers
            'transfer-dispatch', // Dispatch transfers
            
            // ========================================
            // ASSET MANAGEMENT PERMISSIONS
            // ========================================
            'asset-manage',     // Full asset management access
            'asset-view',       // View assets
            'asset-create',     // Create new assets
            'asset-edit',       // Edit existing assets
            'asset-delete',     // Delete assets
            'asset-approve',    // Approve assets
            'asset-bulk-import', // Bulk import assets
            'asset-export',     // Export assets
            
            // ========================================
            // PURCHASE ORDER MANAGEMENT PERMISSIONS
            // ========================================
            'purchase-order-manage', // Full purchase order management access
            'purchase-order-view',   // View purchase orders
            'purchase-order-create', // Create new purchase orders
            'purchase-order-edit',   // Edit existing purchase orders
            'purchase-order-toggle', // Activate/deactivate purchase orders
            'purchase-order-review', // Review purchase orders
            'purchase-order-approve', // Approve purchase orders
            'purchase-order-reject',  // Reject purchase orders
            
            // ========================================
            // FACILITY MANAGEMENT PERMISSIONS
            // ========================================
            'facility-manage',  // Full facility management access
            'facility-view',    // View facilities
            'facility-create',  // Create new facilities
            'facility-edit',    // Edit existing facilities
            'facility-toggle',  // Activate/deactivate facilities
            
            // ========================================
            // WAREHOUSE MANAGEMENT PERMISSIONS
            // ========================================
            'warehouse-manage', // Full warehouse management access
            'warehouse-view',   // View warehouses
            'warehouse-create', // Create new warehouses
            'warehouse-edit',   // Edit existing warehouses
            'warehouse-toggle', // Activate/deactivate warehouses
            
            // ========================================
            // SUPPLIER MANAGEMENT PERMISSIONS
            // ========================================
            'supplier-manage',  // Full supplier management access
            'supplier-view',    // View suppliers
            'supplier-create',  // Create new suppliers
            'supplier-edit',    // Edit existing suppliers
            'supplier-toggle',  // Activate/deactivate suppliers
            
            // ========================================
            // REPORT PERMISSIONS
            // ========================================
            'report-view',      // View reports
            'report-export',    // Export reports
            
            // ========================================
            // SETTINGS PERMISSIONS
            // ========================================
            'setting-manage',   // Full settings management access
            'setting-view',     // View settings
            
            // ========================================
            // DASHBOARD ACCESS PERMISSIONS
            // ========================================
            'dashboard-view',   // Access to dashboard
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
    }
}
