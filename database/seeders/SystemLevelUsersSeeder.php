<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;

class SystemLevelUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the system-level permissions
        $manageSystemPermission = Permission::where('name', 'manage-system')->first();
        $viewSystemPermission = Permission::where('name', 'view-system')->first();

        if (!$manageSystemPermission || !$viewSystemPermission) {
            $this->command->error('System-level permissions not found. Please run PermissionSeeder first.');
            return;
        }

        // Assign manage-system permission to admin users
        $adminUsers = User::where('email', 'admin@warehouse.com')->orWhere('email', 'admin@example.com')->get();
        
        foreach ($adminUsers as $user) {
            if (!$user->hasPermissionTo('manage-system')) {
                $user->permissions()->attach($manageSystemPermission->id);
                $this->command->info("Assigned 'manage-system' permission to {$user->email}");
            }
        }

        // Assign view-system permission to view-only users
        $viewOnlyUsers = User::where('email', 'viewonly@warehouse.com')->get();
        
        foreach ($viewOnlyUsers as $user) {
            if (!$user->hasPermissionTo('view-system')) {
                $user->permissions()->attach($viewSystemPermission->id);
                $this->command->info("Assigned 'view-system' permission to {$user->email}");
            }
        }

        // You can also assign these permissions to other users here
        // Example: Assign manage-system to a specific user
        $systemManager = User::where('email', 'manager@warehouse.com')->first();
        if ($systemManager && !$systemManager->hasPermissionTo('manage-system')) {
            $systemManager->permissions()->attach($manageSystemPermission->id);
            $this->command->info("Assigned 'manage-system' permission to {$systemManager->email}");
        }

        $this->command->info('System-level permissions assigned successfully!');
    }
}
