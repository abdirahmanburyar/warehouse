<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facility;
use App\Models\EligibleItem;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateQuarterlyOrders extends Command
{
    protected $signature = 'orders:generate-quarterly';
    protected $description = 'Generate quarterly orders for facilities based on AMC and SOH';

    private const QUARTER_START_DATES = [
        1 => '01-01',
        2 => '01-04',
        3 => '01-07',
        4 => '01-10',
    ];

    private const MONTHS_IN_QUARTER = 4; // Always use 4 months now

    public function handle()
    {
        // $today = Carbon::today();
        $today = Carbon::parse('31-03-2024');

        $month = $today->month;
        $day = $today->day;
        $year = $today->year;

        // Ensure today is the last day of a quarter
        if (!($month == 3 && $day == 31) && !($month == 6 && $day == 30) &&
            !($month == 9 && $day == 30) && !($month == 12 && $day == 31)) {
            $this->error('Today is not the last day of a quarter.');
            return;
        }

        // Set next quarter
        if ($month == 3) {
            $targetQuarter = 2;
        } elseif ($month == 6) {
            $targetQuarter = 3;
        } elseif ($month == 9) {
            $targetQuarter = 4;
        } else { // December
            $targetQuarter = 1;
            $year += 1;
        }

        $orderStartDate = Carbon::createFromFormat('Y-m-d', $year . '-' . self::QUARTER_START_DATES[$targetQuarter]);
        $prevQuarterStart = $today->copy()->startOfQuarter();
        $prevQuarterEnd = $today->copy()->endOfQuarter();

        $this->info("Generating orders for Quarter {$targetQuarter} starting {$orderStartDate->format('d-m-Y')}");
        $this->info("Using data from {$prevQuarterStart->format('d-m-Y')} to {$prevQuarterEnd->format('d-m-Y')}");

        try {
            DB::beginTransaction();

            $facilities = Facility::where('is_active', true)->get();
            $totalOrders = 0;
            $totalOrderItems = 0;

            foreach ($facilities as $facility) {
                $this->info("\nProcessing facility: {$facility->name}");

                $eligibleItems = EligibleItem::where('facility_type', $facility->facility_type)
                    ->with('product')
                    ->get();

                if ($eligibleItems->isEmpty()) {
                    $this->warn("No eligible items for {$facility->facility_type}");
                    continue;
                }

                $order = Order::create([
                    'facility_id' => $facility->id,
                    'user_id' => $facility->user_id,
                    'order_number' => 'QO-' . $targetQuarter . '-' . $orderStartDate->year . '-' . Str::padLeft($facility->id, 4, '0'),
                    'order_type' => 'quarterly',
                    'status' => 'pending',
                    'order_date' => $orderStartDate,
                    'expected_date' => $orderStartDate->copy()->addDays(14),
                ]);

                $orderItemCount = 0;

                foreach ($eligibleItems as $eligibleItem) {
                    $productId = $eligibleItem->product_id;

                    // Total consumption during previous quarter
                    $totalConsumption = DB::table('pos')
                        ->where('product_id', $productId)
                        ->where('facility_id', $facility->id)
                        ->whereBetween('pos_date', [$prevQuarterStart, $prevQuarterEnd])
                        ->sum('total_quantity');

                    // AMC (Average Monthly Consumption) calculation
                    $amc = $totalConsumption > 0 ? ceil($totalConsumption / 3) : 120; // Default 120 if no consumption

                    if ($totalConsumption === 0) {
                        $this->warn("No consumption for {$eligibleItem->product->name}, using default AMC: 120");
                    }

                    // Current SOH (Stock on Hand)
                    $currentSOH = DB::table('facility_inventories')
                        ->where('product_id', $productId)
                        ->where('facility_id', $facility->id)
                        ->sum('quantity');

                    // Quantity To Order (QTO) = (AMC × 4) – current SOH
                    $qto = ($amc * self::MONTHS_IN_QUARTER) - $currentSOH;

                    if ($qto > 0) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $productId,
                            'quantity' => $qto,
                            'quantity_on_order' => 0,
                            'status' => 'pending',
                        ]);

                        $orderItemCount++;
                        $totalOrderItems++;
                        $this->info("Added item: {$eligibleItem->product->name} - AMC: {$amc}, QTO: {$qto}");
                    }
                }

                if ($orderItemCount === 0) {
                    $order->delete();
                    $this->warn("No items needed, deleted order for facility: {$facility->name}");
                } else {
                    $totalOrders++;
                    $this->info("Created order with {$orderItemCount} items for facility: {$facility->name}");
                }
            }

            DB::commit();
            $this->info("\n✅ Completed Successfully!");
            $this->info("Total: {$totalOrders} orders with {$totalOrderItems} items.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
