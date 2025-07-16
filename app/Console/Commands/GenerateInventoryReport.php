<?php

namespace App\Console\Commands;

use App\Models\InventoryReport;
use App\Models\InventoryReportItem;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\IssueQuantityReport;
use App\Models\IssueQuantityItem;
use App\Models\MonthlyQuantityReceived;
use App\Models\ReceivedQuantityItem;
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
    protected $signature = 'inventory:generate-report {month? : The month to generate report for (YYYY-MM format)}';

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

            // Get target month from argument or use current month
            $monthInput = $this->argument('month');
            $targetMonth = $monthInput 
                ? Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth()
                : now()->startOfMonth();

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
                // Process each product at product_id level (no batches)
                $this->processProductInventory($product, $targetMonth, $report, $bar);
            }

            $bar->finish();
            $this->newLine();

            // Show summary of all quantities for each product
            $this->showQuantitySummary($report->id, $monthYear);

            DB::commit();
            $this->info("\nInventory report for {$monthYear} generated successfully!");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error generating report: " . $e->getMessage());
            return 1;
        }
    }

    private function calculateBeginningBalance($product, $targetMonth)
    {
        // Get the previous month's report
        $previousMonth = (clone $targetMonth)->subMonth();
        $previousMonthYear = $previousMonth->format('Y-m');

        // Debug info
        $this->info("\nDEBUG - Beginning Balance Calculation:");
        $this->info("Looking for previous month report: {$previousMonthYear}");
        
        // Find the previous month's report and get the closing balance
        $previousReport = InventoryReport::where('month_year', $previousMonthYear)
            ->first();

        if ($previousReport) {
            $previousBalance = InventoryReportItem::where('inventory_report_id', $previousReport->id)
                ->where('product_id', $product->id)
                ->value('closing_balance');

            if ($previousBalance !== null) {
                $this->info("Found previous month closing balance: {$previousBalance}");
                return $previousBalance;
            }
        }

        // If no previous report found, return 0
        $this->info("No previous report found, using 0 as beginning balance");
        return 0;
    }

    private function calculateReceivedQuantity($product, $targetMonth)
    {
        // Get the monthly quantity received report for the target month
        $monthYear = $targetMonth->format('Y-m');
        $receivedReport = MonthlyQuantityReceived::where('month_year', $monthYear)->first();

        if (!$receivedReport) {
            $this->info("\nDEBUG - Received Quantity Calculation:");
            $this->info("No monthly quantity received report found for month: {$monthYear}");
            return 0;
        }

        // Get the received quantity for this product from the report
        $receivedItem = ReceivedQuantityItem::where('parent_id', $receivedReport->id)
            ->where('product_id', $product->id)
            ->first();

        $receivedQuantity = $receivedItem ? $receivedItem->quantity : 0;

        // Debug information
        $this->info("\nDEBUG - Received Quantity Calculation:");
        $this->info("Target Month: {$monthYear}");
        $this->info("Product ID: {$product->id}");
        $this->info("Received Report ID: {$receivedReport->id}");
        $this->info("Received Quantity: {$receivedQuantity}");

        return $receivedQuantity;
    }

    private function calculateIssuedQuantity($product, $targetMonth)
    {
        // Get the issue quantity report for the target month
        $monthYear = $targetMonth->format('Y-m');
        $issueReport = IssueQuantityReport::where('month_year', $monthYear)->first();

        if (!$issueReport) {
            $this->info("\nDEBUG - Issued Quantity Calculation:");
            $this->info("No issue quantity report found for month: {$monthYear}");
            return 0;
        }

        // Get the issued quantity for this product from the report
        $issuedItem = IssueQuantityItem::where('parent_id', $issueReport->id)
            ->where('product_id', $product->id)
            ->first();

        $issuedQuantity = $issuedItem ? $issuedItem->quantity : 0;

        // Debug information
        $this->info("\nDEBUG - Issued Quantity Calculation:");
        $this->info("Target Month: {$monthYear}");
        $this->info("Product ID: {$product->id}");
        $this->info("Issue Report ID: {$issueReport->id}");
        $this->info("Issued Quantity: {$issuedQuantity}");

        return $issuedQuantity;
    }
    private function calculateOtherQuantityOut($product, $targetMonth)
    {
        // TODO: Implement other quantity out calculation
        // Sum all other quantity outs (disposals, etc) for the month
        return 0;
    }

    private function calculateAdjustments($product, $targetMonth)
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

    /**
     * Show summary of all quantities for each product
     */
    private function showQuantitySummary($reportId, $monthYear)
    {
        $this->info("\n" . str_repeat("=", 80));
        $this->info("QUANTITY SUMMARY FOR {$monthYear}");
        $this->info(str_repeat("=", 80));

        $reportItems = InventoryReportItem::where('inventory_report_id', $reportId)
            ->with('product')
            ->orderBy('product_id')
            ->get();

        foreach ($reportItems as $item) {
            $this->info("\nProduct: {$item->product->name} (ID: {$item->product_id})");
            $this->info("  Beginning Balance: {$item->beginning_balance}");
            $this->info("  Received Quantity: {$item->received_quantity}");
            $this->info("  Issued Quantity: {$item->issued_quantity}");
            $this->info("  Other Quantity Out: {$item->other_quantity_out}");
            $this->info("  Positive Adjustment: {$item->positive_adjustment}");
            $this->info("  Negative Adjustment: {$item->negative_adjustment}");
            $this->info("  Closing Balance: {$item->closing_balance}");
            $this->info("  Average Monthly Consumption: {$item->average_monthly_consumption}");
            $this->info("  Months of Stock: {$item->months_of_stock}");
        }

        $this->info("\n" . str_repeat("=", 80));
        $this->info("Total Products Processed: {$reportItems->count()}");
        $this->info("Total Closing Balance: " . $reportItems->sum('closing_balance'));
        $this->info(str_repeat("=", 80));
    }

    /**
     * Process individual product inventory at product_id level
     */
    private function processProductInventory($product, $targetMonth, $report, $bar)
    {
        // Calculate quantities for the month (no batch number)
        $beginningBalance = $this->calculateBeginningBalance($product, $targetMonth);
        $receivedQuantity = $this->calculateReceivedQuantity($product, $targetMonth);
        $issuedQuantity = $this->calculateIssuedQuantity($product, $targetMonth);
        $otherQuantityOut = $this->calculateOtherQuantityOut($product, $targetMonth);
        $adjustments = $this->calculateAdjustments($product, $targetMonth);
        
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
        $data = [
            'inventory_report_id' => $report->id,
            'product_id' => $product->id,
            'uom' => 'N/A', // Default UOM since we're not using inventory records
            'beginning_balance' => $beginningBalance,
            'received_quantity' => $receivedQuantity,
            'issued_quantity' => $issuedQuantity,
            'other_quantity_out' => $otherQuantityOut,
            'positive_adjustment' => $adjustments['positive'],
            'negative_adjustment' => $adjustments['negative'],
            'closing_balance' => $closingBalance,
            'total_closing_balance' => $closingBalance, // Same as closing balance since no cost
            'average_monthly_consumption' => $avgConsumption,
            'months_of_stock' => $monthsOfStock,
            'quantity_in_pipeline' => 0 // TODO: Implement pipeline calculation
        ];

        // Save the record and show summary
        $reportItem = InventoryReportItem::create($data);
        
        $this->info("\nSaved: {$product->name} - Closing Balance: {$closingBalance}");
        $bar->advance();
    }


}
