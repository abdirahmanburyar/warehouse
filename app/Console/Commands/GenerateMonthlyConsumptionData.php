<?php

namespace App\Console\Commands;

use App\Models\AvarageMonthlyconsumption;
use App\Models\Facility;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class GenerateMonthlyConsumptionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumption:generate {--months=5 : Number of past months to generate data for} {--facility= : Specific facility ID to generate data for} {--product= : Specific product ID to generate data for} {--min=5 : Minimum random quantity} {--max=100 : Maximum random quantity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random monthly consumption data for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = $this->option('months');
        $facilityId = $this->option('facility');
        $productId = $this->option('product');
        $minQuantity = $this->option('min');
        $maxQuantity = $this->option('max');

        $this->info("Generating random monthly consumption data for the past {$months} months...");
        $this->info("Quantity range: {$minQuantity} - {$maxQuantity}");

        // Get the current month and year
        $currentDate = Carbon::now();
        
        // Get facilities to process
        $facilities = $this->getFacilities($facilityId);
        $this->info("Processing " . $facilities->count() . " facilities");

        // Get products to process
        $products = $this->getProducts($productId);
        $this->info("Processing " . $products->count() . " products");

        // Preload eligibility data to avoid repeated database queries
        $this->info("Preloading eligibility data...");
        $eligibilityMap = $this->preloadEligibilityData();
        
        // Generate month-year values for all months we need to process
        $monthYears = [];
        for ($i = 0; $i < $months; $i++) {
            $date = clone $currentDate;
            $date->subMonths($i);
            $monthYears[] = $date->format('Y-m');
        }
        
        $this->info("Generating consumption records...");
        
        // Calculate total operations for progress bar
        $totalOperations = count($monthYears) * $facilities->count();
        $progressBar = $this->output->createProgressBar($totalOperations);
        $progressBar->start();
        
        $totalRecords = 0;
        $batchSize = 100; // Smaller batch size for better reliability
        $records = [];

        // Process each facility
        foreach ($facilities as $facility) {
            // For each month
            foreach ($monthYears as $monthYear) {
                // Get eligible products for this facility type
                $eligibleProductIds = $eligibilityMap[$facility->facility_type] ?? [];
                
                // Generate records for eligible products
                foreach ($products as $product) {
                    // Skip if product is not eligible for this facility type
                    if (!in_array($product->id, $eligibleProductIds)) {
                        continue;
                    }
                    
                    // Skip if we're filtering by product ID and this isn't the one
                    if ($productId && $product->id != $productId) {
                        continue;
                    }
                    
                    // Generate random consumption quantity
                    $consumption = $this->generateRandomConsumption($minQuantity, $maxQuantity);
                    
                    // Simplified AMC calculation - just use the consumption value
                    $amc = $consumption;
                    
                    // Add to batch
                    $records[] = [
                        'facility_id' => $facility->id,
                        'product_id' => $product->id,
                        'month_year' => $monthYear,
                        'quantity' => $consumption,
                        'amc' => $amc,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $totalRecords++;
                    
                    // Insert in batches to improve performance
                    if (count($records) >= $batchSize) {
                        // Use simple insert instead of the custom method
                        DB::table('monthly_consumptions')->insertOrIgnore($records);
                        $records = [];
                        $this->info("Inserted batch, total records so far: {$totalRecords}");
                    }
                }
                
                $progressBar->advance();
            }
        }
        
        // Insert any remaining records
        if (!empty($records)) {
            DB::table('monthly_consumptions')->insertOrIgnore($records);
            $this->info("Inserted final batch, total records: {$totalRecords}");
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Generated {$totalRecords} monthly consumption records.");
    }

    /**
     * Get facilities to process.
     *
     * @param int|null $facilityId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getFacilities($facilityId = null)
    {
        $query = Facility::query()->where('is_active', true);
        
        if ($facilityId) {
            $query->where('id', $facilityId);
        }
        
        return $query->get();
    }

    /**
     * Get products to process.
     *
     * @param int|null $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getProducts($productId = null)
    {
        $query = Product::query()->where('is_active', true);
        
        if ($productId) {
            $query->where('id', $productId);
        }
        
        return $query->get();
    }

    /**
     * Preload all eligibility data to avoid repeated database queries.
     * Returns a map of facility_type => [product_ids]
     *
     * @return array
     */
    private function preloadEligibilityData()
    {
        $eligibilityMap = [];
        
        // Get all eligibility records
        $eligibilityRecords = DB::table('eligible_items')->get(['product_id', 'facility_type']);
        
        // Group by facility_type
        foreach ($eligibilityRecords as $record) {
            if (!isset($eligibilityMap[$record->facility_type])) {
                $eligibilityMap[$record->facility_type] = [];
            }
            $eligibilityMap[$record->facility_type][] = $record->product_id;
        }
        
        return $eligibilityMap;
    }
    
    /**
     * Insert a batch of records using a single query for better performance.
     *
     * @param array $records
     * @return void
     */
    private function insertBatch(array $records)
    {
        if (empty($records)) {
            return;
        }
        
        // Use insertOrIgnore to handle potential duplicates
        DB::table('monthly_consumptions')->insertOrIgnore($records);
    }

    /**
     * Generate a random consumption quantity.
     *
     * @param int $min Minimum quantity
     * @param int $max Maximum quantity
     * @return int
     */
    private function generateRandomConsumption($min, $max)
    {
        return rand($min, $max);
    }
    
    /**
     * Calculate the Average Monthly Consumption (AMC) based on the current consumption.
     * For simplicity, we're just using the current month's consumption as the AMC.
     * In a real-world scenario, you would calculate this based on historical data.
     *
     * @param int $facilityId The facility ID
     * @param int $productId The product ID
     * @param string $currentMonthYear The current month-year (YYYY-MM format)
     * @param int $currentConsumption The consumption for the current month
     * @return int The calculated AMC value
     */
    private function calculateAMC($facilityId, $productId, $currentMonthYear, $currentConsumption)
    {
        // For simplicity, just return the current consumption as the AMC
        // This prevents the command from getting stuck while still providing a value
        return $currentConsumption;
    }
}
