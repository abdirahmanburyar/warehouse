<?php

namespace App\Jobs;

use App\Models\InventoryReport;
use App\Models\InventoryReportItem;
use App\Models\Product;
use App\Models\ReceivedQuantityItem;
use App\Models\IssueQuantityItem;
use App\Models\InventoryAdjustmentItem;
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
            
            DB::transaction(function () {
                // Parse the month year
                $date = Carbon::createFromFormat('Y-m', $this->monthYear);
                $previousDate = $date->copy()->subMonth();
                
                // Check if report already exists
                $existingReport = InventoryReport::where('month_year', $this->monthYear)->first();
                
                if ($existingReport) {
                    Log::info("Report for {$this->monthYear} already exists. Updating...");
                    // Delete existing items
                    $existingReport->items()->delete();
                    $report = $existingReport;
                } else {
                    // Create new report
                    $report = new InventoryReport();
                    $report->month_year = $this->monthYear;
                    $report->status = 'generated';
                }
                
                $report->generated_by = 1; // System user ID
                $report->generated_at = now();
                $report->save();
                
                // Generate report items for each product
                $itemsGenerated = $this->generateReportItems($report, $date, $previousDate);
                
                Log::info("Successfully generated {$itemsGenerated} items for inventory report {$this->monthYear}");
                
                // Send email notification with report details
                Mail::to('buryar313@gmail.com')->send(new MonthlyInventoryReportGenerated($report, $itemsGenerated));
                
                Log::info("Email notification sent for monthly inventory report {$this->monthYear}");
            });
            
        } catch (\Exception $e) {
            Log::error("Error generating monthly inventory report for {$this->monthYear}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Send error email notification
            Mail::to('buryar313@gmail.com')->send(new MonthlyInventoryReportGenerated(null, 0, $e->getMessage()));
            
            throw $e; // Re-throw to mark job as failed
        }
    }
    
    /**
     * Generate report items for all products
     */
    private function generateReportItems(InventoryReport $report, Carbon $date, Carbon $previousDate)
    {
        // Get all active products
        $products = Product::where('is_active', true)->get();
        
        Log::info("Processing {$products->count()} products for report {$this->monthYear}");
        
        $itemsGenerated = 0;
        
        foreach ($products as $product) {
            if ($this->generateProductReportItem($report, $product, $date, $previousDate)) {
                $itemsGenerated++;
            }
        }
        
        return $itemsGenerated;
    }
    
    /**
     * Generate report item for a specific product
     */
    private function generateProductReportItem(InventoryReport $report, Product $product, Carbon $date, Carbon $previousDate)
    {
        // Get beginning balance from previous month's report
        $beginningBalance = $this->getPreviousMonthClosingBalance($product->id, $previousDate->format('Y-m'));
        
        // Get received quantity for the month from ReceivedQuantityItem
        $receivedQuantity = ReceivedQuantityItem::where('product_id', $product->id)
            ->whereYear('received_at', $date->year)
            ->whereMonth('received_at', $date->month)
            ->sum('quantity');
        
        // Get issued quantity for the month from IssueQuantityItem
        $issuedQuantity = IssueQuantityItem::where('product_id', $product->id)
            ->whereYear('issued_date', $date->year)
            ->whereMonth('issued_date', $date->month)
            ->sum('quantity');
        
        // Get adjustments for the month
        $adjustments = InventoryAdjustmentItem::where('product_id', $product->id)
            ->whereHas('inventoryAdjustment', function($q) use ($date) {
                $q->where('status', 'approved')
                  ->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month);
            })
            ->get();
        
        $positiveAdjustment = $adjustments->where('difference', '>', 0)->sum('difference');
        $negativeAdjustment = $adjustments->where('difference', '<', 0)->sum(function($item) {
            return abs($item->difference);
        });
        
        // Calculate closing balance
        $closingBalance = $beginningBalance + $receivedQuantity - $issuedQuantity - $negativeAdjustment + $positiveAdjustment;
        
        // Only create report item if there's any movement or balance
        if ($beginningBalance > 0 || $receivedQuantity > 0 || $issuedQuantity > 0 || $positiveAdjustment > 0 || $negativeAdjustment > 0 || $closingBalance > 0) {
            
            // Try to calculate average unit cost from recent received items
            // Handle case where unit_cost column might not exist
            $unitCost = null;
            $totalCost = null;
            
            try {
                $recentReceivedItems = ReceivedQuantityItem::where('product_id', $product->id)
                    ->where('unit_cost', '>', 0)
                    ->orderBy('received_at', 'desc')
                    ->limit(5)
                    ->get();
                
                $unitCost = $recentReceivedItems->isNotEmpty() ? $recentReceivedItems->avg('unit_cost') : null;
                $totalCost = $unitCost ? ($closingBalance * $unitCost) : null;
            } catch (\Exception $e) {
                // If unit_cost column doesn't exist, leave as null
                Log::info("Unit cost calculation skipped for product {$product->id}: " . $e->getMessage());
            }
            
            // Calculate months of stock based on issued quantity
            $monthsOfStock = $issuedQuantity > 0 ? ($closingBalance / $issuedQuantity) : 0;
            
            InventoryReportItem::create([
                'inventory_report_id' => $report->id,
                'product_id' => $product->id,
                'uom' => $product->uom ?? null,
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
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
            ]);
            
            return true; // Item was created
        }
        
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
