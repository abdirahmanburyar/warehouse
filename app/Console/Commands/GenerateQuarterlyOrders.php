<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facility;
use App\Models\EligibleItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateQuarterlyOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:generate-quarterly 
                          {--simulate-quarter= : Simulate being in a specific quarter (1-4)}
                          {--amc=120 : Default Average Monthly Consumption}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate quarterly orders for facilities based on AMC and SOH';

    /**
     * Default AMC value
     */
    private int $defaultAmc = 120;

    /**
     * Number of months for quarterly order calculation (static)
     */
    private const MONTHS_IN_QUARTER = 4;
    // Well known quarters
    private const QUARTERS = [
        ["from" => '01-01', "to" => '31-03'],
        ["from" => '01-04', "to" => '30-06'],
        ["from" => '01-07', "to" => '31-09'],
        ["from" => '01-10', "to" => '31-12'],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get current quarter or simulated quarter
        $simulatedQuarter = $this->option('simulate-quarter');
        $now = Carbon::now();
        
        // Determine current quarter (1-4)
        $currentQuarter = ceil($now->month / 3);
        
        // Use simulated quarter if provided
        if ($simulatedQuarter) {
            $currentQuarter = $simulatedQuarter;
        }
        
        // Get previous quarter for AMC calculation
        $prevQuarter = $currentQuarter - 1;
        $year = $now->year;
        if ($prevQuarter < 1) {
            $prevQuarter = 4;
            $year--;
        }
        
        // Get quarter date ranges
        $currentQuarterDates = self::QUARTERS[$currentQuarter - 1];
        $prevQuarterDates = self::QUARTERS[$prevQuarter - 1];
        
        // Create date objects for previous quarter
        $prevQuarterStart = Carbon::createFromFormat('Y-m-d', $year . '-' . $prevQuarterDates['from']);
        $prevQuarterEnd = Carbon::createFromFormat('Y-m-d', $year . '-' . $prevQuarterDates['to']);

        $this->info("Generating orders for Q{$currentQuarter} ({$currentQuarterDates['from']} - {$currentQuarterDates['to']})");
        $this->info("Using AMC from Q{$prevQuarter} ({$prevQuarterDates['from']} - {$prevQuarterDates['to']})");

        try {
            DB::beginTransaction();

            // Get all active facilities
            $facilities = Facility::where('is_active', true)->get();
            
            $totalOrders = 0;
            $totalOrderItems = 0;

            foreach ($facilities as $facility) {
                $this->info("\nProcessing facility: {$facility->name}");

                // Get eligible items for this facility type
                $eligibleItems = EligibleItem::where('facility_type', $facility->facility_type)
                    ->with('product')
                    ->get();

                if ($eligibleItems->isEmpty()) {
                    $this->warn("No eligible items found for facility type: {$facility->facility_type}");
                    continue;
                }

                // Create order
                $order = Order::create([
                    'facility_id' => $facility->id,
                    'user_id' => $facility->user_id,
                    'order_number' => 'QO-' . $currentQuarter . '-' . $now->year . '-' . Str::padLeft($facility->id, 4, '0'),
                    'order_type' => 'quarterly',
                    'status' => 'pending',
                    'order_date' => $now,
                    'expected_date' => $now->addDays(14),
                ]);

                $orderItemCount = 0;

                foreach ($eligibleItems as $eligibleItem) {
                    // Calculate AMC from previous quarter's POS data
                    $totalConsumption = DB::table('pos')
                        ->where('product_id', $eligibleItem->product_id)
                        ->where('facility_id', $facility->id)
                        ->whereBetween('pos_date', [$prevQuarterStart, $prevQuarterEnd])
                        ->sum('total_quantity');
                    
                    // Calculate AMC (total consumption divided by 3 months)
                    $amc = ceil($totalConsumption / 3);
                    
                    // If no consumption data found, use default AMC
                    if ($amc === 0) {
                        $amc = $this->defaultAmc;
                        $this->warn("No consumption data found for {$eligibleItem->product->name}, using default AMC: {$amc}");
                    }

                    // Get SOH from facility inventory
                    $soh = DB::table('facility_inventories')
                        ->where('product_id', $eligibleItem->product_id)
                        ->where('facility_id', $facility->id)
                        ->sum('quantity');

                    // Calculate QTO using configurable months period (default 4)
                    // Formula: QTO = (AMC × months) – SOH
                    $months = self::MONTHS_IN_QUARTER;
                    $qto = ($amc * $months) - $soh;

                    if ($qto > 0) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $eligibleItem->product_id,
                            'quantity' => $qto,
                            'status' => 'pending',
                            'amc' => $amc // Store the calculated AMC for reference
                        ]);
                        $orderItemCount++;
                        $totalOrderItems++;
                        $this->info("Added order item: {$eligibleItem->product->name} - AMC: {$amc}, QTO: {$qto} (SOH: {$soh})");
                    }
                }

                if ($orderItemCount === 0) {
                    $order->delete();
                    $this->warn("No items needed for facility: {$facility->name}");
                } else {
                    $totalOrders++;
                    $this->info("Created order with {$orderItemCount} items for facility: {$facility->name}");
                }
            }

            DB::commit();
            $this->info("\nCompleted successfully!");
            $this->info("Created {$totalOrders} orders with {$totalOrderItems} total items.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed: " . $e->getMessage());
            throw $e;
        }
    }
}
