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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->defaultAmc = $this->option('amc') ?? 120;
        
        // Get current quarter or simulated quarter
        $quarter = $this->option('simulate-quarter');
        if (!$quarter) {
            $quarter = ceil(Carbon::now()->month / 3);
        }

        $this->info("Generating orders for Q{$quarter}...");

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
                    'order_number' => 'QO-' . $quarter . '-' . date('Y') . '-' . Str::padLeft($facility->id, 4, '0'),
                    'order_type' => 'quarterly',
                    'status' => 'pending',
                    'order_date' => Carbon::now(),
                    'expected_date' => Carbon::now()->addDays(14),
                ]);

                $orderItemCount = 0;

                foreach ($eligibleItems as $eligibleItem) {
                    // Get SOH from facility inventory
                    $soh = DB::table('facility_inventories')
                        ->where('product_id', $eligibleItem->product_id)
                        ->where('facility_id', $facility->id)
                        ->sum('quantity');

                    // Calculate QTO using static 4 months period
                    // Formula: QTO = (AMC × 4 months) – SOH
                    $qto = ($this->defaultAmc * self::MONTHS_IN_QUARTER) - $soh;

                    if ($qto > 0) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $eligibleItem->product_id,
                            'quantity' => $qto,
                            'status' => 'pending'
                        ]);
                        $orderItemCount++;
                        $totalOrderItems++;
                        $this->info("Added order item: {$eligibleItem->product->name} - QTO: {$qto} (SOH: {$soh})");
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
