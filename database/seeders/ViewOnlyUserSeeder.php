<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Permission;

class ViewOnlyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create View-Only User
        $viewOnlyUser = User::firstOrCreate(
            ['email' => 'viewonly@warehouse.com'],
            [
                'name' => 'View Only User',
                'username' => 'viewonly',
                'email' => 'viewonly@warehouse.com',
                'password' => Hash::make('password'),
                'title' => 'View Only User',
                'is_active' => true,
            ]
        );

        // Get view-only permission
        $viewOnlyPermission = Permission::where('name', 'view-only-access')->first();
        
        // Get all view permissions for each module
        $viewPermissions = Permission::whereIn('name', [
            'view-only-access',
            'dashboard-view',
            'user-view',
            'order-view',
            'product-view',
            'inventory-view',
            'transfer-view',
            'asset-view',
            'purchase-order-view',
            'facility-view',
            'warehouse-view',
            'supplier-view',
            'report-view',
            'setting-view',
        ])->get();

        // Assign view-only permissions to the user
        $viewOnlyUser->permissions()->sync($viewPermissions->pluck('id'));

        $this->command->info('View-Only User created successfully!');
        $this->command->info('Email: viewonly@warehouse.com');
        $this->command->info('Password: password');
        $this->command->info('Assigned ' . $viewPermissions->count() . ' view-only permissions');
        $this->command->info('This user can view all modules but cannot perform any actions (create, edit, delete, approve, etc.)');
    }
}
