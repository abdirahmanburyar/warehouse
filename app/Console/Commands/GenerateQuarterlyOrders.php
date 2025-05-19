<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facility;
use App\Models\EligibleItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

    private function getMonthsInQuarter($movement): int
    {
        return match($movement) {
            'Fast Moving' => 3,
            'Slow Moving' => 4,
            default => 4
        };
    }

    public function handle()
    {
        try {
            DB::beginTransaction();
            
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

            // First, calculate required quantities for all facilities
            $productOrders = [];
            $facilityOrders = [];
            $facilities = Facility::where('is_active', true)->get();
            $totalOrders = 0;
            $totalOrderItems = 0;

            // Step 1: Calculate required quantities for all facilities
            foreach ($facilities as $facility) {
                $this->info("\nCalculating requirements for facility: {$facility->name}");

                $eligibleItems = EligibleItem::where('facility_type', $facility->facility_type)
                    ->with('product')
                    ->get();

                if ($eligibleItems->isEmpty()) {
                    $this->warn("No eligible items for {$facility->facility_type}");
                    continue;
                }

                $timestamp = now()->format('His');
                $order = Order::create([
                    'facility_id' => $facility->id,
                    'user_id' => $facility->user_id,
                    'order_number' => "OR-{$targetQuarter}-{$year}-{$timestamp}-" . str_pad($facility->id, 4, '0', STR_PAD_LEFT),
                    'order_type' => 'quarterly',
                    'status' => 'pending',
                    'order_date' => $orderStartDate,
                    'expected_date' => $orderStartDate->copy()->addDays(7),
                ]);

                $facilityOrders[$facility->id] = [
                    'order' => $order,
                    'items' => []
                ];

                foreach ($eligibleItems as $eligibleItem) {
                    $productId = $eligibleItem->product_id;

                    // Check inventory with detailed batch information
                    $inventoryBatches = DB::table('inventories as i')
                    ->join('products as p', 'p.id', '=', 'i.product_id')
                    ->where('i.product_id', $productId)
                    ->where('i.quantity', '>', 0)
                    ->orderBy('i.expiry_date', 'asc')
                    ->select('i.*', 'p.name as product_name')
                    ->get();

                    $this->info("\n================================================");
                    $this->info("Checking inventory for product ID: {$productId}");

                    if ($inventoryBatches->isEmpty()) {
                        $this->warn("Skipping {$eligibleItem->product->name} - No inventory available");
                        continue;
                    }

                    if ($inventoryBatches->isNotEmpty()) {
                        $this->info("Product Name: {$inventoryBatches->first()->product_name}");
                        $this->info("\n=== Available Inventory Batches ===");
                        $totalAvailable = 0;
                        foreach ($inventoryBatches as $batch) {
                            $this->info("Batch {$batch->batch_number}:");
                            $this->info("  - Quantity: {$batch->quantity} units");
                            $this->info("  - Location: Warehouse {$batch->warehouse_id}, Location {$batch->location_id}");
                            $this->info("  - Expires: {$batch->expiry_date}");
                            $totalAvailable += $batch->quantity;
                        }
                        $this->info("\nTotal available quantity across all batches: {$totalAvailable} units");
                    }

                    if ($totalAvailable === 0) {
                        $this->warn("Skipping {$eligibleItem->product->name} - All batches are empty");
                        continue;
                    }

                    $this->info("\n=== Processing {$eligibleItem->product->name} for {$facility->name} ===");

                    // Total consumption during previous quarter
                    $totalConsumption = DB::table('pos')
                        ->where('product_id', $productId)
                        ->where('facility_id', $facility->id)
                        ->whereBetween('pos_date', [$prevQuarterStart, $prevQuarterEnd])
                        ->sum('total_quantity');

                    $this->info("1. Total consumption in previous quarter: {$totalConsumption} units");
                    $this->info("   Period: {$prevQuarterStart->format('d-m-Y')} to {$prevQuarterEnd->format('d-m-Y')}");

                    // AMC (Average Monthly Consumption) calculation
                    $amc = $totalConsumption > 0 ? floor($totalConsumption / 3) : 120; // Default 120 if no consumption

                    if ($totalConsumption === 0) {
                        $this->warn("2. No consumption history found, using default AMC: 120");
                    } else {
                        $this->info("2. AMC Calculation: {$totalConsumption} ÷ 3 months = {$amc} units per month");
                    }

                    // Current SOH (Stock on Hand)
                    $currentSOH = DB::table('facility_inventories')
                        ->where('product_id', $productId)
                        ->where('facility_id', $facility->id)
                        ->sum('quantity');

                    $this->info("3. Current Stock on Hand (SOH): {$currentSOH} units");

                    // Quantity To Order (QTO) = (AMC × 4) – current SOH
                    // Get product movement and calculate months
                    $monthsInQuarter = $this->getMonthsInQuarter($eligibleItem->product->movement);
                    $monthlyNeed = $amc * $monthsInQuarter;
                    $qto = $monthlyNeed - $currentSOH;

                    $this->info("4. Order Quantity Calculation:");
                    $this->info("   - Monthly need: {$amc} × {self::MONTHS_IN_QUARTER} months = {$monthlyNeed} units");
                    $this->info("   - QTO = {$monthlyNeed} - {$currentSOH} = {$qto} units");

                    $this->info("5. Total Available in Central Inventory: {$totalAvailable} units");

                    if ($qto > 0) {
                        $this->info("6. Checking order feasibility:");
                        $this->info("   - Requested: {$qto} units");
                        $this->info("   - Available: {$totalAvailable} units");

                        $allocatable = min($qto, $totalAvailable);
                        
                        if ($allocatable > 0) {
                            // Store the required quantity
                            if (!isset($productOrders[$productId])) {
                                $productOrders[$productId] = [
                                    'product_name' => $eligibleItem->product->name,
                                    'total_demand' => 0,
                                    'facilities' => [],
                                    'available_batches' => $inventoryBatches
                                ];
                            }

                            $productOrders[$productId]['total_demand'] += $allocatable;
                            $productOrders[$productId]['facilities'][] = [
                                'facility_id' => $facility->id,
                                'quantity_needed' => $allocatable,
                                'amc' => $amc,
                                'qto' => $qto
                            ];

                            $facilityOrders[$facility->id]['items'][] = [
                                'product_id' => $productId,
                                'quantity_needed' => $allocatable
                            ];

                            $this->info("   ✓ Can allocate: {$allocatable} units");
                        } else {
                            $this->warn("   ✗ Cannot allocate any units - insufficient inventory");
                        }
                    } else {
                        $this->info("6. No order needed - sufficient stock at facility");
                    }
                }
            }

            // Step 2: Check inventory and allocate quantities
            $globalInventoryTracker = [];

            foreach ($productOrders as $productId => $productData) {
                $this->info("\n=== Processing {$productData['product_name']} ===");

                // Get fresh inventory data for this product
                $freshInventory = DB::table('inventories')
                    ->where('product_id', $productId)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                // Track available inventory in memory
                if (!isset($globalInventoryTracker[$productId])) {
                    $globalInventoryTracker[$productId] = collect($freshInventory)
                        ->mapWithKeys(function ($batch) {
                            return [$batch->id => [
                                'batch_number' => $batch->batch_number,
                                'quantity' => $batch->quantity,
                                'expiry_date' => $batch->expiry_date,
                                'warehouse_id' => $batch->warehouse_id,
                                'location_id' => $batch->location_id
                            ]];
                        })->toArray();
                }

                $inventoryTracker = &$globalInventoryTracker[$productId];

                $totalAvailable = array_sum(array_column($inventoryTracker, 'quantity'));
                $this->info("\nCurrent inventory status:");
                foreach ($inventoryTracker as $batchId => $batchData) {
                    if ($batchData['quantity'] > 0) {
                        $this->info("Batch {$batchData['batch_number']}: {$batchData['quantity']} units (Expires: {$batchData['expiry_date']}");
                    }
                }

                $this->info("\nTotal available: {$totalAvailable} units");
                $this->info("Total demand: {$productData['total_demand']} units");

                foreach ($productData['facilities'] as $facilityData) {
                    $facilityId = $facilityData['facility_id'];
                    $quantityNeeded = $facilityData['quantity_needed'];
                    
                    // Calculate facility's share based on their proportion of total demand
                    $facilityShare = $quantityNeeded / $productData['total_demand'];
                    $totalToAllocate = floor($facilityShare * $totalAvailable);
                    $remainingToAllocate = $totalToAllocate;
                    
                    $this->info("\nFacility {$facilityId}:");
                    $this->info("- Needs: {$quantityNeeded} units");
                    $this->info("- Allocation calculation:");
                    $this->info("  * Share = ({$quantityNeeded} / {$productData['total_demand']}) = " . 
                        number_format($facilityShare * 100, 2) . "%");
                    $this->info("  * Units = " . number_format($facilityShare * $totalAvailable, 2) . " units");
                    $this->info("  * Final allocation = {$totalToAllocate} units");

                    if ($totalToAllocate > 0) {
                        // Calculate and store AMC, QER, needed quantity, and quantity to release
                        $amc = $facilityData['amc'] ?? 120; // Use default 120 if not set
                        $qer = $facilityData['qto'] ?? 0; // Use QTO as QER
                        $neededQuantity = $quantityNeeded; // Use already calculated quantity needed
                        
                        // Get product movement and calculate months
                        $this->info("\n########## START DEBUG ##########");
                        $this->info("Calculating for product ID: {$productId}");
                        $product = Product::find($productId);
                        if (!$product) {
                            $this->error("Product not found: {$productId}");
                            $this->info("########## END DEBUG ##########\n");
                            continue;
                        }
                        
                        // Debug product details
                        $this->info("Product name: {$product->name}");
                        $this->info("Raw movement value: '{$product->movement}'");
                        $this->info("Movement type: " . gettype($product->movement));
                        
                        // Try to normalize the movement value
                        $movement = trim($product->movement);
                        $this->info("Trimmed movement: '{$movement}'");
                        
                        $monthsInQuarter = $this->getMonthsInQuarter($movement);
                        $this->info("Months in quarter: {$monthsInQuarter}");
                        
                        $no_of_days = $monthsInQuarter * 30; // Approximate days in a month
                        $this->info("Final no_of_days: {$no_of_days}");
                        $this->info("########## END DEBUG ##########\n");
                        
                        // Create single order item for total allocation with calculations
                        $orderItem = OrderItem::create([
                            'order_id' => $facilityOrders[$facilityId]['order']->id,
                            'product_id' => $productId,
                            'quantity' => $neededQuantity, // This is the needed quantity based on AMC calculation
                            'amc' => $amc,
                            'qer' => $qer,
                            'quantity_to_release' => $totalToAllocate, // This is the calculated quantity after inventory checks
                            'no_of_days' => $no_of_days
                        ]);

                        // Allocate from batches and track in inventory_allocations
                        foreach ($inventoryTracker as $batchId => &$batchData) {
                            if ($remainingToAllocate <= 0 || $batchData['quantity'] <= 0) continue;

                            $batchAllocation = min($batchData['quantity'], $remainingToAllocate);
                            
                            // Record allocation
                            DB::table('inventory_allocations')->insert([
                                'order_item_id' => $orderItem->id,
                                'product_id' => $productId,
                                'warehouse_id' => $batchData['warehouse_id'],
                                'location_id' => $batchData['location_id'],
                                'batch_number' => $batchData['batch_number'],
                                'expiry_date' => $batchData['expiry_date'],
                                'allocated_quantity' => $batchAllocation,
                                'allocation_type' => 'quarterly',
                                'notes' => "Allocated from batch {$batchData['batch_number']} (expires {$batchData['expiry_date']})",
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            // Update inventory
                            DB::table('inventories')
                                ->where('id', $batchId)
                                ->update([
                                    'quantity' => DB::raw("quantity - {$batchAllocation}")
                                ]);

                            // Update in-memory tracker
                            $batchData['quantity'] -= $batchAllocation;
                            $remainingToAllocate -= $batchAllocation;

                            $this->info("  Allocated {$batchAllocation} units from batch {$batchData['batch_number']}");
                            $this->info("  Remaining in batch: {$batchData['quantity']} units");
                        }

                        $totalOrderItems++;
                        $this->info("✓ Total allocated {$totalToAllocate} units to order {$facilityOrders[$facilityId]['order']->order_number}");

                        // Show remaining inventory after this allocation
                        $remainingTotal = array_sum(array_column($inventoryTracker, 'quantity'));
                        $this->info("Remaining total inventory: {$remainingTotal} units");
                    }
                }
            }

            // Clean up empty orders
            foreach ($facilityOrders as $facilityId => $orderData) {
                $orderItemCount = OrderItem::where('order_id', $orderData['order']->id)->count();
                if ($orderItemCount === 0) {
                    $orderData['order']->delete();
                    $this->warn("No items allocated, deleted order for facility ID: {$facilityId}");
                } else {
                    $totalOrders++;
                    $this->info("Finalized order with {$orderItemCount} items for facility ID: {$facilityId}");
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
