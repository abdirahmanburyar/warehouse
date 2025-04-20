<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CategorySeeder;
use Database\Seeders\WarehouseSeeder;
use Database\Seeders\DistrictsTableSeeder;
use Database\Seeders\FacilitiesTableSeeder;
use Database\Seeders\EligibleItemsTableSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\InventoriesTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create user first so we can assign roles
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class, // Add roles and permissions
            CategorySeeder::class,
            WarehouseSeeder::class,
            InventoriesTableSeeder::class, // Add inventories for all products
        ]);
    }
}
