<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to Super Admin role
        $superAdminRole->syncPermissions($permissions);

        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'buryar313@gmail.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'buryar313@gmail.com',
                'password' => Hash::make('password'),
                'title' => 'Super Administrator',
                'is_active' => true,
            ]
        );

        // Assign Super Admin role to the user
        $superAdmin->assignRole($superAdminRole);

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Email: buryar313@gmail.com');
        $this->command->info('Password: password');
    }
}
