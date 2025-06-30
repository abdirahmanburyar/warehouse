<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Facility;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\EligibleItem;
use App\Models\InventoryAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessFacilityQuarterlyOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $facility;
    protected $targetQuarter;
    protected $year;
    protected $orderStartDate;
    protected $today;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [60, 180, 300]; // 1 min, 3 mins, 5 mins

    /**
     * Create a new job instance.
     */
    public function __construct(Facility $facility, int $targetQuarter, int $year, Carbon $orderStartDate, Carbon $today)
    {
        $this->facility = $facility;
        $this->targetQuarter = $targetQuarter;
        $this->year = $year;
        $this->orderStartDate = $orderStartDate;
        $this->today = $today;
        $this->onQueue('quarterly-orders');
    }

    /**
     * Calculate the number of months needed for AMC based on movement type.
     */
    private function getMonthsInQuarter($movement): int
    {
        return match($movement) {
            'Fast Moving' => 3,
            'Slow Moving' => 4,
            default => 4
        };
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info("Processing quarterly order for facility", [
            'facility_id' => $this->facility->id,
            'facility_name' => $this->facility->name,
            'quarter' => $this->targetQuarter,
            'year' => $this->year
        ]);

        DB::beginTransaction();
        try {
            // Create the order
            $timestamp = now()->format('His');
            $order = Order::create([
                'facility_id' => $this->facility->id,
                'created_by' => $this->facility->user_id,
                'order_number' => "OR-{$this->targetQuarter}-{$this->year}-{$timestamp}-" . str_pad($this->facility->id, 4, '0', STR_PAD_LEFT),
                'order_type' => 'quarterly',
                'status' => 'pending',
                'order_date' => $this->orderStartDate,
                'expected_date' => $this->orderStartDate->copy()->addDays(7),
            ]);

            // Get eligible items for the facility
            $eligibleItems = EligibleItem::where('facility_type', $this->facility->facility_type)
                ->with('product')
                ->get();

            foreach ($eligibleItems as $eligibleItem) {
                $this->processEligibleItem($order, $eligibleItem);
            }

            DB::commit();
            Log::info("Successfully processed quarterly order", [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing quarterly order for facility", [
                'facility_id' => $this->facility->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process a single eligible item for the order.
     */
    protected function processEligibleItem(Order $order, EligibleItem $eligibleItem)
    {
        $product = $eligibleItem->product;
        if (!$product) {
            Log::warning("Product not found for eligible item", ['eligible_item_id' => $eligibleItem->id]);
            return;
        }

        // Calculate AMC with null check for movement
        $movement = $product->movement ?? 'Fast Moving'; // Default to 'Fast Moving' if movement is null
        $monthsNeeded = $this->getMonthsInQuarter($movement);
        $monthsInQuarter = 3; // Standard quarter length

        // Get the previous months for AMC calculation
        $months = [];
        $currentDate = $this->today->copy();
        for ($i = 0; $i < $monthsNeeded; $i++) {
            $months[] = $currentDate->format('Y-m');
            $currentDate->subMonth();
        }

        // Get consumption data
        $consumptionData = DB::table('monthly_consumption_reports')
            ->join('monthly_consumption_items', 'monthly_consumption_reports.id', '=', 'monthly_consumption_items.parent_id')
            ->where('monthly_consumption_reports.facility_id', $this->facility->id)
            ->where('monthly_consumption_items.product_id', $product->id)
            ->whereIn('monthly_consumption_reports.month_year', $months)
            ->select('monthly_consumption_reports.month_year', 'monthly_consumption_items.quantity')
            ->get();

        // Calculate AMC
        $totalConsumption = $consumptionData->sum('quantity');
        $monthsWithData = $consumptionData->count();
        $amc = $monthsWithData > 0 ? $totalConsumption / $monthsWithData : 10; // Default AMC if no data

        // Get current stock on hand
        $soh = DB::table('facility_inventories')
            ->join('facility_inventory_items', 'facility_inventories.id', '=', 'facility_inventory_items.facility_inventory_id')
            ->where('facility_inventories.facility_id', $this->facility->id)
            ->where('facility_inventories.product_id', $product->id)
            ->sum('facility_inventory_items.quantity');

        // Calculate needed quantity
        $neededQuantity = ($amc * $monthsInQuarter) - $soh;
        $neededQuantity = max(0, ceil($neededQuantity));

        if ($neededQuantity <= 0) {
            Log::info("No need for product", [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'amc' => $amc,
                'soh' => $soh
            ]);
            return;
        }

        // Create order item
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $neededQuantity,
            'quantity_on_order' => 0,
            'soh' => $soh,
            'amc' => $amc,
            'quantity_to_release' => 0,
            'no_of_days' => 120
        ]);

        // Process inventory allocation in a separate job
        ProcessInventoryAllocation::dispatch($orderItem, $neededQuantity)
            ->onQueue('inventory-allocation');
    }
} 