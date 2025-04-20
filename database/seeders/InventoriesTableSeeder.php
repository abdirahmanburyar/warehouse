<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoriesTableSeeder extends Seeder
{
    private $racks = ['A', 'B', 'C', 'D', 'E'];
    private $shelves = ['1', '2', '3', '4', '5'];
    private $blocks = ['X', 'Y', 'Z'];
    private $pallets = ['01', '02', '03', '04', '05'];

    /**
     * Generate a location string based on whether the item needs cold storage
     */
    private function generateLocation(bool $isColdStorage): string
    {
        if ($isColdStorage) {
            $rack = 'CS'; // Cold Storage
        } else {
            $rack = $this->racks[array_rand($this->racks)];
        }
        
        $shelf = $this->shelves[array_rand($this->shelves)];
        $block = $this->blocks[array_rand($this->blocks)];
        $pallet = $this->pallets[array_rand($this->pallets)];

        return "Rack {$rack}, Shelf {$shelf}, Block {$block}, Pallet {$pallet}";
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countCreated = 0;
        $countSkipped = 0;

        try {
            DB::beginTransaction();

            // Get all products and active facilities
            $products = Product::all();
            $facilities = Facility::where('is_active', true)->get();

            if ($products->isEmpty()) {
                throw new \Exception("No products found in the database.");
            }

            if ($facilities->isEmpty()) {
                throw new \Exception("No active facilities found in the database.");
            }

            foreach ($facilities as $facility) {
                $this->command->info("Creating inventory for facility: {$facility->name}");

                foreach ($products as $product) {
                    // Check if inventory already exists for this product in this facility
                    $exists = DB::table('facility_inventories')
                        ->where('product_id', $product->id)
                        ->where('facility_id', $facility->id)
                        ->exists();

                    if ($exists) {
                        $countSkipped++;
                        continue;
                    }

                    // Generate random decimal quantity between 1000 and 10000
                    $quantity = rand(1000, 10000);

                    // Generate batch number in format BATCH-YYYY-XXXX
                    $batchNumber = 'BATCH-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                    // Check if product needs cold storage (based on name containing keywords)
                    $needsColdStorage = str_contains(strtolower($product->name), 'vaccine') || 
                                     str_contains(strtolower($product->name), 'insulin') ||
                                     str_contains(strtolower($product->name), 'cold');

                    // Only assign cold storage products to facilities with cold storage
                    if ($needsColdStorage && !$facility->has_cold_storage) {
                        $this->command->warn("Skipping cold storage product {$product->name} for facility without cold storage: {$facility->name}");
                        continue;
                    }

                    // Generate location based on storage requirements
                    $location = $this->generateLocation($needsColdStorage);

                    // Create inventory record
                    DB::table('facility_inventories')->insert([
                        'product_id' => $product->id,
                        'facility_id' => $facility->id,
                        'quantity' => $quantity,
                        'batch_number' => $batchNumber,
                        'location' => $location,
                        'expiry_date' => now()->addYears(2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $countCreated++;
                    $this->command->info("Created inventory for {$product->name} at {$facility->name} with quantity {$quantity} at {$location} (Batch: {$batchNumber})");
                }
            }

            DB::commit();
            $this->command->info("Successfully created {$countCreated} inventory records (skipped {$countSkipped} existing records)");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error creating inventory records: " . $e->getMessage());
            throw $e;
        }
    }
}
