<?php

namespace App\Jobs;

use App\Models\InventoryReport;
use App\Models\InventoryReportItem;
use App\Models\Product;
use App\Models\MonthlyQuantityReceived;
use App\Models\IssueQuantityReport;
use App\Mail\MonthlyInventoryReportGenerated;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateMonthlyInventoryReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $monthYear;
    
    /**
     * Create a new job instance.
     */
    public function __construct($monthYear = null)
    {
        $this->monthYear = $monthYear ?? Carbon::now()->format('Y-m');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info("Starting monthly inventory report generation for {$this->monthYear}");
            echo "Starting monthly inventory report generation for {$this->monthYear}\n";
            
            // Parse the month year
            $date = Carbon::createFromFormat('Y-m', $this->monthYear);
            $previousDate = $date->copy()->subMonth();
            
            echo "Processing date: {$date->format('Y-m')}\n";
            
            // Check if report already exists
            echo "Checking for existing report...\n";
            $existingReport = InventoryReport::where('month_year', $this->monthYear)->first();
            
            if ($existingReport) {
                Log::info("Report for {$this->monthYear} already exists. Updating...");
                echo "Report already exists. Updating...\n";
                // Delete existing items
                $existingReport->items()->delete();
                $report = $existingReport;
            } else {
                echo "Creating new report...\n";
                // Create new report
                $report = new InventoryReport();
                $report->month_year = $this->monthYear;
                $report->status = 'generated';
            }
            
            $report->generated_by = 1; // System user ID
            $report->generated_at = now();
            $report->save();
            
            echo "Report saved with ID: {$report->id}\n";
            
            // Generate report items for each product
            $itemsGenerated = $this->generateReportItems($report, $date, $previousDate);
            
            echo "Generated {$itemsGenerated} items\n";
            Log::info("Successfully generated {$itemsGenerated} items for inventory report {$this->monthYear}");
            
            // Send email notification with report details - Skip for now to test
            // Mail::to('buryar313@gmail.com')->send(new MonthlyInventoryReportGenerated($report, $itemsGenerated));
            
            echo "Email notification skipped for testing\n";
            Log::info("Monthly inventory report generation completed for {$this->monthYear}");
            
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            Log::error("Error generating monthly inventory report for {$this->monthYear}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Send error email notification - Skip for now
            // Mail::to('buryar313@gmail.com')->send(new MonthlyInventoryReportGenerated(null, 0, $e->getMessage()));
            
            throw $e; // Re-throw to mark job as failed
        }
    }
    
    /**
     * Generate report items for all products
     */
    private function generateReportItems(InventoryReport $report, Carbon $date, Carbon $previousDate)
    {
        // First check if required reports exist for this month
        echo "Checking for required reports for {$date->format('Y-m')}...\n";
        
        // Check for received quantity report
        $receivedReport = MonthlyQuantityReceived::where('month_year', $date->format('Y-m'))->first();
        if (!$receivedReport) {
            throw new \Exception("Received quantity report for {$date->format('Y-m')} not found. Please generate it first.");
        }
        
        // Check for issued quantity report  
        $issuedReport = IssueQuantityReport::where('month_year', $date->format('Y-m'))->first();
        if (!$issuedReport) {
            throw new \Exception("Issued quantity report for {$date->format('Y-m')} not found. Please generate it first.");
        }
        
        echo "Found required reports. Proceeding with inventory report generation...\n";
        
        // Get received items from the generated report
        $receivedItems = $receivedReport->items()->with('product')->get();
        echo "Found {$receivedItems->count()} received items\n";
        
        // Get issued items from the generated report
        $issuedItems = $issuedReport->items()->with('product')->get();
        echo "Found {$issuedItems->count()} issued items\n";
        
        // Get all unique products from both reports
        $allProductIds = $receivedItems->pluck('product_id')->merge($issuedItems->pluck('product_id'))->unique();
        echo "Processing {$allProductIds->count()} unique products\n";
        
        $itemsGenerated = 0;
        $processedCount = 0;
        
        foreach ($allProductIds as $productId) {
            $processedCount++;
            
            // Show progress every 10 products
            if ($processedCount % 10 == 0) {
                echo "Processed {$processedCount}/{$allProductIds->count()} products\n";
            }
            
            // Get the product
            $product = Product::find($productId);
            if (!$product) {
                continue;
            }
            
            if ($this->generateProductReportItemFromReports($report, $product, $receivedItems, $issuedItems, $previousDate)) {
                $itemsGenerated++;
            }
        }
        
        echo "Completed processing all products. Items generated: {$itemsGenerated}\n";
        
        return $itemsGenerated;
    }
    
    /**
     * Generate report item for a specific product using data from generated reports
     */
    private function generateProductReportItemFromReports(InventoryReport $report, $product, $receivedItems, $issuedItems, Carbon $previousDate)
    {
        echo "Processing product: {$product->productID} - {$product->name}\n";
        
        // Get beginning balance from previous month's report
        $beginningBalance = $this->getPreviousMonthClosingBalance($product->id, $previousDate->format('Y-m'));
        echo "  Beginning balance: {$beginningBalance}\n";
        
        // Get received quantity from the received report items
        $receivedQuantity = $receivedItems->where('product_id', $product->id)->sum('quantity');
        echo "  Received quantity: {$receivedQuantity}\n";
        
        // Get issued quantity from the issued report items
        $issuedQuantity = $issuedItems->where('product_id', $product->id)->sum('quantity');
        echo "  Issued quantity: {$issuedQuantity}\n";
        
        // Set adjustments to 0 (will be entered manually)
        $positiveAdjustment = 0;
        $negativeAdjustment = 0;
        
        // Calculate closing balance
        $closingBalance = $beginningBalance + $receivedQuantity - $issuedQuantity - $negativeAdjustment + $positiveAdjustment;
        echo "  Closing balance: {$closingBalance}\n";
        
        // Only create report item if there's any movement or balance
        if ($beginningBalance > 0 || $receivedQuantity > 0 || $issuedQuantity > 0 || $closingBalance > 0) {
            
            // Calculate unit cost based on received items
            $productReceivedItems = $receivedItems->where('product_id', $product->id);
            echo "  Found " . $productReceivedItems->count() . " received items for this product\n";
            
            $totalReceivedCost = $productReceivedItems->sum('total_cost');
            $totalReceivedQuantity = $productReceivedItems->sum('quantity');
            
            echo "  Total received cost: {$totalReceivedCost}\n";
            echo "  Total received quantity: {$totalReceivedQuantity}\n";
            
            // Debug: Show individual received items
            foreach ($productReceivedItems as $item) {
                echo "    Item - Qty: {$item->quantity}, Unit Cost: {$item->unit_cost}, Total Cost: {$item->total_cost}\n";
            }
            
            // Calculate weighted average unit cost for this month's received items
            $unitCost = 0;
            if ($totalReceivedQuantity > 0 && $totalReceivedCost > 0) {
                $unitCost = $totalReceivedCost / $totalReceivedQuantity;
                echo "  Calculated unit cost from received items: {$unitCost}\n";
            } else {
                echo "  No cost data from received items, checking previous month...\n";
                // If no cost data from received items, try to get from previous month's report
                $previousReportItem = InventoryReportItem::whereHas('report', function($q) use ($previousDate) {
                    $q->where('month_year', $previousDate->format('Y-m'));
                })->where('product_id', $product->id)->first();
                
                if ($previousReportItem && $previousReportItem->unit_cost > 0) {
                    $unitCost = $previousReportItem->unit_cost;
                    echo "  Using previous month's unit cost: {$unitCost}\n";
                } else {
                    echo "  No previous month's cost data found, using 0\n";
                }
            }
            
            // Calculate total cost based on closing balance and unit cost
            $totalCost = $closingBalance * $unitCost;
            echo "  Calculated total cost: {$totalCost} (closing balance: {$closingBalance} × unit cost: {$unitCost})\n";
            
            // Calculate months of stock based on issued quantity
            $monthsOfStock = $issuedQuantity > 0 ? ($closingBalance / $issuedQuantity) : 0;
            
            echo "  Final values - Unit Cost: " . round($unitCost, 2) . ", Total Cost: " . round($totalCost, 2) . "\n";
            
            InventoryReportItem::create([
                'inventory_report_id' => $report->id,
                'product_id' => $product->id,
                'uom' => $product->uom ?? 'pcs',
                'batch_number' => null, // Not tracking batches in monthly reports
                'expiry_date' => null, // Not tracking expiry in monthly reports
                'beginning_balance' => $beginningBalance,
                'received_quantity' => $receivedQuantity,
                'issued_quantity' => $issuedQuantity,
                'other_quantity_out' => 0, // Not used in this implementation
                'positive_adjustment' => $positiveAdjustment,
                'negative_adjustment' => $negativeAdjustment,
                'closing_balance' => $closingBalance,
                'total_closing_balance' => $closingBalance,
                'average_monthly_consumption' => $issuedQuantity, // Current month consumption
                'months_of_stock' => $monthsOfStock,
                'quantity_in_pipeline' => 0, // Not tracked in this implementation
                'unit_cost' => round($unitCost, 2),
                'total_cost' => round($totalCost, 2),
            ]);
            
            echo "  ✓ Report item created successfully\n\n";
            return true; // Item was created
        }
        
        echo "  ✗ No report item created (no movement or balance)\n\n";
        return false; // No item created
    }
    
    /**
     * Get closing balance from previous month's report
     */
    private function getPreviousMonthClosingBalance($productId, $monthYear)
    {
        $previousReportItem = InventoryReportItem::whereHas('report', function($q) use ($monthYear) {
            $q->where('month_year', $monthYear);
        })->where('product_id', $productId)->first();
        
        return $previousReportItem ? $previousReportItem->closing_balance : 0;
    }
}
