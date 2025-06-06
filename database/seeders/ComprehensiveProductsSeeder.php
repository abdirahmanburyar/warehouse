<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ComprehensiveProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get or create categories
        $drugCategory = Category::firstOrCreate(
            ['name' => 'Pharmaceuticals'],
            [
                'description' => 'Prescription and over-the-counter medications',
                'is_active' => true,
            ]
        );
        
        $consumableCategory = Category::firstOrCreate(
            ['name' => 'Medical Consumables'],
            [
                'description' => 'Medical supplies and consumables for healthcare facilities',
                'is_active' => true,
            ]
        );
        
        $labCategory = Category::firstOrCreate(
            ['name' => 'Laboratory Supplies'],
            [
                'description' => 'Laboratory equipment, reagents and testing supplies',
                'is_active' => true,
            ]
        );

        // Get the comprehensive products data
        $productsData = $this->getProductsData();
        $countCreated = 0;
        $countSkipped = 0;
        
        // Get existing product names for simple duplicate check
        $existingProducts = Product::pluck('name')->toArray();
        
        // Create all products
        foreach ($productsData as $item) {
            // Simple exact name match check
            if (Product::where('name', $item['name'])->exists()) {
                $countSkipped++;
                $this->command->info("Skipped: {$item['name']}");
                continue;
            }
            
            // Determine the category based on type
            $categoryId = null;
            switch ($item['type']) {
                case 'Drug':
                    $categoryId = $drugCategory->id;
                    $sku = 'DRUG-' . strtoupper(substr(md5($item['name']), 0, 8));
                    break;
                case 'Consumable':
                    $categoryId = $consumableCategory->id;
                    $sku = 'CONS-' . strtoupper(substr(md5($item['name']), 0, 8));
                    break;
                case 'Lab':
                    $categoryId = $labCategory->id;
                    $sku = 'LAB-' . strtoupper(substr(md5($item['name']), 0, 8));
                    break;
                default:
                    $categoryId = $consumableCategory->id;
                    $sku = 'ITEM-' . strtoupper(substr(md5($item['name']), 0, 8));
            }
            
            Product::create([
                'name' => $item['name'],
                'sku' => $sku,
                'barcode' => 'BAR' . rand(1000000000, 9999999999),
                'description' => 'Essential ' . strtolower($item['type']) . ' for healthcare facilities',
                'category_id' => $categoryId,
                'type' => $item['type'],
                'pack_size' => $item['pack_size'],
                'is_active' => true,
            ]);
            $countCreated++;
            $this->command->info("Created: {$item['name']}");
        }
        
        $this->command->info("Created {$countCreated} new products.");
        $this->command->info("Skipped {$countSkipped} products as they already exist.");
    }
    
    /**
     * Get comprehensive products data from the Excel
     * 
     * @return array
     */
    private function getProductsData()
    {
        return [];
          
    }
}
