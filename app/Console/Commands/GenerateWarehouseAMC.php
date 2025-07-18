<?php

namespace App\Console\Commands;

use App\Models\IssueQuantityReport;
use App\Models\IssueQuantityItem;
use App\Models\ReorderLevel;
use App\Models\Product;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateWarehouseAMC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:generate-amc {--month= : Specific month to process (YYYY-MM format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AMC (Average Monthly Consumption) and Reorder Levels based on last 3 months of issue quantity data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting AMC and Reorder Level generation...');

        try {
            // Get the target month (default to last month)
            $targetMonth = $this->option('month') ?: Carbon::now()->subMonth()->format('Y-m');
            
            $this->info("Processing AMC for month: {$targetMonth}");

            // Get the last 3 months of data
            $months = $this->getLastThreeMonths($targetMonth);
            
            if (empty($months)) {
                $this->error('No issue quantity reports found for the last 3 months.');
                return 1;
            }

            $this->info('Found months: ' . implode(', ', $months));

            // Calculate AMC for each product
            $amcData = $this->calculateAMC($months);
            
            if (empty($amcData)) {
                $this->error('No AMC data calculated. Check if there are issue quantity items.');
                return 1;
            }

            // Update or create reorder levels
            $this->updateReorderLevels($amcData);

            $this->info('AMC and Reorder Level generation completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error generating AMC: ' . $e->getMessage());
            Log::error('AMC generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Get the last 3 months of available data
     */
    private function getLastThreeMonths(string $targetMonth): array
    {
        $months = [];
        $currentMonth = Carbon::createFromFormat('Y-m', $targetMonth);
        
        // Start from the target month and go back 3 months
        for ($i = 0; $i < 3; $i++) {
            $monthToCheck = $currentMonth->copy()->subMonths($i)->format('Y-m');
            
            // Check if we have data for this month
            $report = IssueQuantityReport::where('month_year', $monthToCheck)->first();
            
            if ($report) {
                $months[] = $monthToCheck;
            } else {
                $this->warn("No report found for month: {$monthToCheck}");
            }
        }

        return $months;
    }

    /**
     * Calculate AMC for each product based on the last 3 months
     */
    private function calculateAMC(array $months): array
    {
        $amcData = [];

        // Get all products that have issue quantity data in the specified months
        $productsWithData = IssueQuantityItem::whereHas('report', function ($query) use ($months) {
            $query->whereIn('month_year', $months);
        })
        ->with(['product', 'report'])
        ->get()
        ->groupBy('product_id');

        foreach ($productsWithData as $productId => $items) {
            $product = $items->first()->product;
            $monthlyQuantities = [];

            // Group quantities by month
            foreach ($items as $item) {
                $month = $item->report->month_year;
                if (!isset($monthlyQuantities[$month])) {
                    $monthlyQuantities[$month] = 0;
                }
                $monthlyQuantities[$month] += $item->quantity;
            }

            // Calculate average monthly consumption
            $totalQuantity = array_sum($monthlyQuantities);
            $monthsWithData = count($monthlyQuantities);
            
            if ($monthsWithData > 0) {
                $amc = $totalQuantity / $monthsWithData;
                
                $amcData[$productId] = [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'amc' => round($amc, 2),
                    'months_used' => $monthsWithData,
                    'total_quantity' => $totalQuantity,
                    'monthly_breakdown' => $monthlyQuantities
                ];

                $this->line("Product: {$product->name} - AMC: {$amc} (based on {$monthsWithData} months)");
            }
        }

        return $amcData;
    }

    /**
     * Update or create reorder levels with the calculated AMC
     */
    private function updateReorderLevels(array $amcData): void
    {
        $updated = 0;
        $created = 0;

        foreach ($amcData as $productId => $data) {
            $reorderLevel = ReorderLevel::where('product_id', $productId)->first();

            if ($reorderLevel) {
                // Update existing reorder level
                $reorderLevel->update([
                    'amc' => $data['amc']
                ]);
                $updated++;
                $this->line("Updated reorder level for: {$data['product_name']} - AMC: {$data['amc']}");
            } else {
                // Create new reorder level with default lead time of 5
                ReorderLevel::create([
                    'product_id' => $productId,
                    'amc' => $data['amc'],
                    'lead_time' => 5, // Default lead time
                ]);
                $created++;
                $this->line("Created reorder level for: {$data['product_name']} - AMC: {$data['amc']}");
            }
        }

        $this->info("Reorder levels updated: {$updated}, created: {$created}");
    }
}
