<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@warehouse.psivista.com')->first();

        $warehouses = [
            [
                'name' => 'PSI Warehouse',
                'code' => 'PSI001',
                'address' => 'Port Road, Eyl',
                'city' => 'Eyl',
                'state' => 'Nugal',
                'country' => 'Somalia',
                'postal_code' => '00200',
                'manager_name' => 'Fatima Hassan',
                'manager_email' => 'fatima.hassan@warehouse.psivista.com',
                'manager_phone' => '+252 61 234 5678',
                'latitude' => 7.9803,
                'longitude' => 49.8164,
                'capacity' => 10000,
                'temperature_min' => -5,
                'temperature_max' => 5,
                'humidity_min' => 70,
                'humidity_max' => 85,
                'status' => 'active',
                'has_cold_storage' => true,
                'has_hazardous_storage' => false,
                'is_active' => true,
                'notes' => 'Specialized cold storage facility for seafood and perishable goods from the coast',
                'user_id' => $admin->id,
            ]
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
