<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            // User Management Module
            [
                'name' => 'user-view',
                'display_name' => 'View Users',
                'description' => 'Can view user list and details',
                'module' => 'User Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'user-create',
                'display_name' => 'Create Users',
                'description' => 'Can create new users',
                'module' => 'User Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'user-edit',
                'display_name' => 'Edit Users',
                'description' => 'Can edit existing users',
                'module' => 'User Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'user-delete',
                'display_name' => 'Delete Users',
                'description' => 'Can delete users',
                'module' => 'User Management',
                'guard_name' => 'web',
            ],

            // Facility Management Module
            [
                'name' => 'facility-view',
                'display_name' => 'View Facilities',
                'description' => 'Can view facility list and details',
                'module' => 'Facility Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'facility-create',
                'display_name' => 'Create Facilities',
                'description' => 'Can create new facilities',
                'module' => 'Facility Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'facility-edit',
                'display_name' => 'Edit Facilities',
                'description' => 'Can edit existing facilities',
                'module' => 'Facility Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'facility-delete',
                'display_name' => 'Delete Facilities',
                'description' => 'Can delete facilities',
                'module' => 'Facility Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'facility-import',
                'display_name' => 'Import Facilities',
                'description' => 'Can import facilities from Excel',
                'module' => 'Facility Management',
                'guard_name' => 'web',
            ],

            // Product Management Module
            [
                'name' => 'product-view',
                'display_name' => 'View Products',
                'description' => 'Can view product list and details',
                'module' => 'Product Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product-create',
                'display_name' => 'Create Products',
                'description' => 'Can create new products',
                'module' => 'Product Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product-edit',
                'display_name' => 'Edit Products',
                'description' => 'Can edit existing products',
                'module' => 'Product Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product-delete',
                'display_name' => 'Delete Products',
                'description' => 'Can delete products',
                'module' => 'Product Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'product-import',
                'display_name' => 'Import Products',
                'description' => 'Can import products from Excel',
                'module' => 'Product Management',
                'guard_name' => 'web',
            ],

            // Inventory Management Module
            [
                'name' => 'inventory-view',
                'display_name' => 'View Inventory',
                'description' => 'Can view inventory levels and movements',
                'module' => 'Inventory Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'inventory-adjust',
                'display_name' => 'Adjust Inventory',
                'description' => 'Can adjust inventory quantities',
                'module' => 'Inventory Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'inventory-transfer',
                'display_name' => 'Transfer Inventory',
                'description' => 'Can transfer inventory between locations',
                'module' => 'Inventory Management',
                'guard_name' => 'web',
            ],

            // Warehouse Management Module
            [
                'name' => 'warehouse-view',
                'display_name' => 'View Warehouses',
                'description' => 'Can view warehouse information',
                'module' => 'Warehouse Management',
                'guard_name' => 'web',
            ],
            [
                'name' => 'warehouse-manage',
                'display_name' => 'Manage Warehouses',
                'description' => 'Can create, edit, and delete warehouses',
                'module' => 'Warehouse Management',
                'guard_name' => 'web',
            ],

            // Reports Module
            [
                'name' => 'reports-view',
                'display_name' => 'View Reports',
                'description' => 'Can view system reports',
                'module' => 'Reports',
                'guard_name' => 'web',
            ],
            [
                'name' => 'reports-export',
                'display_name' => 'Export Reports',
                'description' => 'Can export reports to various formats',
                'module' => 'Reports',
                'guard_name' => 'web',
            ],

            // System Administration Module
            [
                'name' => 'system-settings',
                'display_name' => 'System Settings',
                'description' => 'Can modify system settings',
                'module' => 'System Administration',
                'guard_name' => 'web',
            ],
            [
                'name' => 'permission-manage',
                'display_name' => 'Manage Permissions',
                'description' => 'Can assign and manage user permissions',
                'module' => 'System Administration',
                'guard_name' => 'web',
            ],

            // View-Only Access (Special Permission)
            [
                'name' => 'view-only-access',
                'display_name' => 'View Only Access',
                'description' => 'Can view all modules but cannot perform any actions',
                'module' => 'System Administration',
                'guard_name' => 'web',
            ],

            // Manager System (Full Access)
            [
                'name' => 'manager-system',
                'display_name' => 'System Manager',
                'description' => 'Full system access and management capabilities',
                'module' => 'System Administration',
                'guard_name' => 'web',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'description' => $permission['description'],
                'module' => $permission['module'],
                'guard_name' => $permission['guard_name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Permissions seeded successfully!');
        $this->command->info('Created ' . count($permissions) . ' permissions across ' . count(array_unique(array_column($permissions, 'module'))) . ' modules.');
    }
}
