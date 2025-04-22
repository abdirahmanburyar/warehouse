<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseInventoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding warehouse inventories...');
        
        // Get all products
        $products = DB::table('products')->get();
        
        $countCreated = 0;

        // Define location ranges
        $racks = range('A', 'E');       // Racks A to E
        $blocks = range(1, 30);         // Blocks 1 to 30
        $pallets = range(1, 50);        // Pallets 1 to 50
        $shelves = range(100, 300, 2);  // Shelves 100 to 300 (even numbers)
        
        foreach ($products as $product) {
            // Check if inventory already exists
            $exists = DB::table('inventories')
                ->where('product_id', $product->id)
                ->where('warehouse_id', 1)
                ->exists();
                
            if ($exists) {
                $this->command->warn("Inventory already exists for product {$product->name}");
                continue;
            }
            
            // Generate random quantity between 1000 and 10000
            $quantity = rand(1000, 10000);
            
            // Generate batch number
            $batchNumber = 'BATCH-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Generate random location
            $rack = $racks[array_rand($racks)];
            $block = $blocks[array_rand($blocks)];
            $pallet = $pallets[array_rand($pallets)];
            $shelf = $shelves[array_rand($shelves)];
            
            // Format location string with descriptive indicators
            $location = "Rack {$rack}, Block {$block}, Pallet {$pallet}, Shelf {$shelf}";
            
            DB::table('inventories')->insert([
                'warehouse_id' => 1,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'batch_number' => $batchNumber,
                'location' => $location,
                'expiry_date' => \Carbon\Carbon::now()->addYears(2)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
                'is_active' => true,
            ]);
            
            $countCreated++;
        }
        
        $this->command->info("Created {$countCreated} inventory records");
    }
}
