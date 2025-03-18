<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'General Storage', 'description' => 'Standard warehouses for general merchandise storage'],
            ['name' => 'Cold Storage', 'description' => 'Temperature-controlled facilities for perishable goods'],
            ['name' => 'Hazardous Materials', 'description' => 'Specialized facilities for storing hazardous materials'],
            ['name' => 'Pharmaceutical', 'description' => 'Facilities designed for pharmaceutical products storage'],
            ['name' => 'E-commerce', 'description' => 'Warehouses optimized for e-commerce fulfillment'],
            ['name' => 'Distribution Center', 'description' => 'Large facilities for regional distribution'],
            ['name' => 'Cross-Dock', 'description' => 'Facilities designed for quick transfer of goods'],
            ['name' => 'Bonded Warehouse', 'description' => 'Facilities for storing imported goods before duties are paid'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
