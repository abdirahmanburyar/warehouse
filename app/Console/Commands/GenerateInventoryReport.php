<?php

namespace App\Console\Commands;

use App\Models\InventoryReport;
use App\Models\InventoryReportItem;
use App\Models\Product;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateInventoryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:generate-report {--month= : The month to generate report for (YYYY-MM format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly inventory report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            // Get the target month or use current month
            $targetMonth = $this->option('month') 
                ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
                : Carbon::now()->startOfMonth();
            
            $monthYear = $targetMonth->format('Y-m');

            // Check if report already exists
            if (InventoryReport::where('month_year', $monthYear)->exists()) {
                $this->error("Report for {$monthYear} already exists!");
                return 1;
            }

            // Create the report
            $report = InventoryReport::create([
                'month_year' => $monthYear,
                'generated_by' => 'system',
                'generated_at' => now(),
            ]);

            // Get all products with their current inventory
            $products = Product::with(['inventories'])->get();

            $bar = $this->output->createProgressBar(count($products));
            $bar->start();

            foreach ($products as $product) {
                // Get batch number from inventory
                // Get the first inventory record for this product
                $inventory = $product->inventories()->first();
                $batchNumber = $inventory->batch_number ?? null;

                // Calculate quantities for the month
                $beginningBalance = $this->calculateBeginningBalance($product, $targetMonth, $batchNumber);
                $receivedQuantity = $this->calculateReceivedQuantity($product, $targetMonth, $batchNumber);
                $issuedQuantity = $this->calculateIssuedQuantity($product, $targetMonth, $batchNumber);
                $otherQuantityOut = $this->calculateOtherQuantityOut($product, $targetMonth, $batchNumber);
                $adjustments = $this->calculateAdjustments($product, $targetMonth, $batchNumber);
                
                // Calculate closing balance
                $closingBalance = $beginningBalance 
                    + $receivedQuantity 
                    - $issuedQuantity 
                    - $otherQuantityOut 
                    + $adjustments['positive'] 
                    - $adjustments['negative'];

                // Calculate average monthly consumption (last 3 months)
                $avgConsumption = $this->calculateAverageMonthlyConsumption($product, $targetMonth);
                
                // Calculate months of stock
                $monthsOfStock = $avgConsumption > 0 ? $closingBalance / $avgConsumption : 0;

                // Create report item
                // InventoryReportItem::create([
                $data = [
                    'inventory_report_id' => $report->id,
                    'product_id' => $product->id,
                    'uom' => $inventory->uom ?? 'N/A',
                    'batch_number' => $inventory->batch_number ?? null,
                    'expiry_date' => $inventory->expiry_date ?? null,
                    'beginning_balance' => $beginningBalance,
                    'received_quantity' => $receivedQuantity,
                    'issued_quantity' => $issuedQuantity,
                    'other_quantity_out' => $otherQuantityOut,
                    'positive_adjustment' => $adjustments['positive'],
                    'negative_adjustment' => $adjustments['negative'],
                    'closing_balance' => $closingBalance,
                    'total_closing_balance' => $closingBalance * ($inventory->unit_cost ?? 0),
                    'average_monthly_consumption' => $avgConsumption,
                    'months_of_stock' => $monthsOfStock,
                    'quantity_in_pipeline' => 0, // TODO: Implement pipeline calculation
                    'unit_cost' => $inventory->unit_cost ?? 0,
                    'total_cost' => $closingBalance * ($inventory->unit_cost ?? 0)
                ];

                $this->info($data, true);

                // $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            DB::commit();
            $this->info("\nInventory report for {$monthYear} generated successfully!");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error generating report: " . $e->getMessage());
            return 1;
        }
    }

    private function calculateBeginningBalance($product, $targetMonth, $batchNumber = null)
    {
        // TODO: Implement actual beginning balance calculation
        // This should be the closing balance of the previous month
        return $product->inventories()
            ->when($batchNumber, function($query) use ($batchNumber) {
                return $query->where('batch_number', $batchNumber);
            })
            ->value('quantity') ?? 0;
    }

    private function calculateReceivedQuantity($product, $targetMonth, $batchNumber = null)
    {
        // TODO: Implement received quantity calculation
        // Sum all received quantities for the month from purchase orders or transfers
        return 0;
    }

    private function calculateIssuedQuantity($product, $targetMonth, $batchNumber = null)
    {
        // Sum all issued quantities for the target month from issue_quantity_items table
        return DB::table('issue_quantity_items')
            ->where('product_id', $product->id)
            ->when($batchNumber, function($query) use ($batchNumber) {
                return $query->where('batch_number', $batchNumber);
            })
            ->whereYear('issued_date', $targetMonth->year)
            ->whereMonth('issued_date', $targetMonth->month)
            ->sum('quantity');
    }
    private function calculateOtherQuantityOut($product, $targetMonth, $batchNumber = null)
    {
        // TODO: Implement other quantity out calculation
        // Sum all other quantity outs (disposals, etc) for the month
        return 0;
    }

    private function calculateAdjustments($product, $targetMonth, $batchNumber = null)
    {
        // TODO: Implement adjustments calculation
        // Calculate both positive and negative adjustments for the month
        return [
            'positive' => 0,
            'negative' => 0
        ];
    }

    private function calculateAverageMonthlyConsumption($product, $targetMonth)
    {
        // TODO: Implement average monthly consumption calculation
        // Calculate average consumption over last 3 months
        return 0;
    }
}
