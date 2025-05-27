<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\WarehouseAmc;
use App\Models\IssuedQuantity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WarehouseAmcMonthly extends Command
{
    protected $signature = 'warehouse:generate-amc';
    protected $description = 'Generate Average Monthly Consumption (AMC) records for all products';

    public function handle()
    {
        $this->info('Starting AMC generation...');

        // Get last month's date range
        $lastMonth = now()->subMonth();
        $startDate = $lastMonth->startOfMonth()->format('Y-m-d');
        $endDate = $lastMonth->endOfMonth()->format('Y-m-d');
        $monthYear = $lastMonth->format('Y-m');

        // Process each product
        Product::chunk(100, function ($products) use ($startDate, $endDate, $monthYear) {
            foreach ($products as $product) {
                // Calculate total issued quantity for last month
                $totalIssued = IssuedQuantity::where('product_id', $product->id)
                    ->whereBetween('issued_date', [$startDate, $endDate])
                    ->sum('quantity');

                // Create or update AMC record
                WarehouseAmc::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'month_year' => $monthYear
                    ],
                    [
                        'quantity' => $totalIssued
                    ]
                );

                $this->info("Processed AMC for product: {$product->name} - Quantity: {$totalIssued}");
            }
        });

        $this->info('AMC generation completed successfully!');
    }
}
