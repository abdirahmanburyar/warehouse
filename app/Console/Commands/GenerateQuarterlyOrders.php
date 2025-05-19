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
            $this->info("Generating quarterly orders...");
            
            // Start transaction
            DB::beginTransaction();
            $this->info("Starting quarterly order generation...");

            // Get today's date
            $today = Carbon::parse('31-03-2024'); // For testing
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

            // Initialize tracking arrays
            $productOrders = [];
            $facilityOrders = [];
            $facilities = Facility::where('is_active', true)->get();
            $totalOrders = 0;
            $totalOrderItems = 0;
            
            $this->info("Found {$facilities->count()} active facilities");
            
            // First pass: Calculate total needs for each product
            foreach ($facilities as $facility) {
                $this->info("\nCalculating needs for facility: {$facility->name}");
                
                // Create the order
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
                
                $eligibleItems = EligibleItem::where('facility_type', $facility->facility_type)
                    ->with('product')
                    ->get();
                
                foreach ($eligibleItems as $eligibleItem) {
                    $productId = $eligibleItem->product_id;
                    $no_of_months = $this->getMonthsInQuarter($eligibleItem->product->movement ?? 'Normal');
                    $amc = 120; // Default AMC
                    $quantityNeeded = $amc * $no_of_months;
                    
                    if (!isset($productOrders[$productId])) {
                        $productOrders[$productId] = [
                            'total_needed' => 0,
                            'product_name' => $eligibleItem->product->name,
                            'facilities' => []
                        ];
                    }
                    
                    $productOrders[$productId]['total_needed'] += $quantityNeeded;
                    $productOrders[$productId]['facilities'][] = [
                        'facility_id' => $facility->id,
                        'facility_name' => $facility->name,
                        'order_id' => $order->id,
                        'needed' => $quantityNeeded,
                        'amc' => $amc
                    ];
                }
            }
            
            $this->info("\nCalculated total needs for all products:");
            foreach ($productOrders as $productId => $data) {
                $this->info("Product {$data['product_name']}: {$data['total_needed']} units needed total");
            }
            
            // Second pass: Process each product and allocate inventory
            foreach ($productOrders as $productId => $productData) {
                $this->info("\n=== Processing {$productData['product_name']} ===");
                
                // Get fresh inventory data
                $inventory = DB::table('inventories')
                    ->where('product_id', $productId)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->get();
                
                $totalInventory = $inventory->sum('quantity');
                $this->info("Total inventory available: {$totalInventory}");
                $this->info("Total needed: {$productData['total_needed']}");
                
                if ($totalInventory > 0) {
                    // Track inventory batches
                    $inventoryTracker = collect($inventory)
                        ->mapWithKeys(function ($batch) {
                            return [$batch->id => [
                                'batch_number' => $batch->batch_number,
                                'quantity' => $batch->quantity,
                                'expiry_date' => $batch->expiry_date,
                                'warehouse_id' => $batch->warehouse_id,
                                'location_id' => $batch->location_id
                            ]];
                        })->toArray();
                    
                    foreach ($productData['facilities'] as $facilityData) {
                        $facilityId = $facilityData['facility_id'];
                        $facility = $facilities->firstWhere('id', $facilityId);
                        $quantityNeeded = $facilityData['needed'];
                        $amc = $facilityData['amc'];
                        
                        // Calculate facility's share using the formula: (facility needed / total needed) * available inventory
                        $facilityShare = ($quantityNeeded / $productData['total_needed']) * $totalInventory;
                        $totalToAllocate = floor($facilityShare);
                        
                        $this->info("\nFacility {$facilityData['facility_name']}:");
                        $this->info("  * Needs: {$quantityNeeded}");
                        $this->info("  * Share calculation: ({$quantityNeeded} / {$productData['total_needed']}) * {$totalInventory}");
                        $this->info("  * Share = " . number_format($facilityShare, 2) . " units");
                        $this->info("  * Final allocation = {$totalToAllocate} units");
                        
                        if ($totalToAllocate > 0) {
                            // Create order item
                            $orderItem = OrderItem::create([
                                'order_id' => $facilityData['order_id'],
                                'product_id' => $productId,
                                'quantity' => $quantityNeeded,
                                'amc' => $amc,
                                'qer' => 0,
                                'quantity_to_release' => $totalToAllocate,
                                'no_of_days' => 120
                            ]);
                            
                            $totalOrderItems++;
                            
                            // Allocate from batches
                            $remainingToAllocate = $totalToAllocate;
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
                                
                                $batchData['quantity'] -= $batchAllocation;
                                $remainingToAllocate -= $batchAllocation;
                                
                                $this->info("    Allocated {$batchAllocation} units from batch {$batchData['batch_number']}");
                                $this->info("    Remaining in batch: {$batchData['quantity']} units");
                            }
                            
                            $this->info("✓ Total allocated {$totalToAllocate} units to order {$facilityOrders[$facilityId]['order']->order_number}");
                            
                            // Show remaining inventory after this allocation
                            $remainingTotal = array_sum(array_column($inventoryTracker, 'quantity'));
                            $this->info("Remaining total inventory: {$remainingTotal} units");
                        
                    }

                    if ($inventoryBatches->isEmpty()) {
                        $this->warn("Skipping {$eligibleItem->product->name} - No inventory available");
                        continue;
                    }

                    if ($inventoryBatches->isNotEmpty()) {
                        $this->info("Product Name: {$inventoryBatches->first()->product_name}");
                        $this->info("\n=== Available Inventory Batches ===");
                        $totalAvailable = 0;
                        foreach ($inventory as $batch) {
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
                    $this->info("   - Monthly need: {$amc} × {$monthsInQuarter} months = {$monthlyNeed} units");
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
                                    'available_batches' => $inventory
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

                $this->info("Total inventory available: {$totalAvailable}");
                $this->info("Total needed: {$productData['total_needed']}");

                if ($totalAvailable <= 0) {
                    $this->warn("Skipping allocation - no inventory available");
                    continue;
                }

                foreach ($productData['facilities'] as $facilityData) {
                    $facilityId = $facilityData['facility_id'];
                    $quantityNeeded = $facilityData['needed'];
                    
                    // Calculate facility's share based on their proportion of total needed
                    $facilityShare = $quantityNeeded / $productData['total_needed'];
                    $totalToAllocate = floor($facilityShare * $totalAvailable);
                    $remainingToAllocate = $totalToAllocate;
                    
                    $this->info("\nProcessing facility ID {$facilityId}:");
                    $this->info("  * Needs: {$quantityNeeded} units");
                    $this->info("  * Share calculation: ({$quantityNeeded} / {$productData['total_needed']}) = " . 
                        number_format($facilityShare * 100, 2) . "%");
                    $this->info("  * Allocated units: {$totalToAllocate}");

                    $totalNeededForProduct = $productData['total_needed'];
                    
                    if ($totalNeededForProduct > 0 && $totalInventory > 0) {
                        // Determine allocation based on available inventory
                        if ($totalInventory >= $totalNeededForProduct) {
                            // If we have enough inventory, give each facility what they need
                            $totalToAllocate = $quantityNeeded;
                            $this->info("Sufficient inventory available. Allocating requested amount.");
                        } else {
                            // If we don't have enough, use proportional allocation
                            // (facility needed / total needed) * available inventory
                            $facilityShare = ($quantityNeeded / $totalNeededForProduct) * $totalInventory;
                            $totalToAllocate = floor($facilityShare); // Round down to nearest whole number
                            $this->info("Insufficient inventory. Using proportional allocation.");
                        }
                        
                        $this->info("\nFacility {$facilityData['facility_name']}:");
                        $this->info("  * Needs: {$quantityNeeded}");
                        if ($totalInventory >= $totalNeededForProduct) {
                            $this->info("  * Full allocation: {$totalToAllocate} units (100% of requested)");
                        } else {
                            $this->info("  * Share calculation: ({$quantityNeeded} / {$totalNeededForProduct}) * {$totalInventory}");
                            $this->info("  * Share = " . number_format($facilityShare, 2) . " units");
                        }
                        
                        if ($totalToAllocate > 0) {
                            // Create order item
                            $orderItem = OrderItem::create([
                                'order_id' => $facilityData['order_id'],
                                'product_id' => $productId,
                                'quantity' => $quantityNeeded,
                                'amc' => $facilityData['amc'],
                                'qer' => 0,
                                'quantity_to_release' => $totalToAllocate,
                                'no_of_days' => 120
                            ]);
                            
                            $totalOrderItems++;
                            
                            // Allocate from batches
                            $remainingToAllocate = $totalToAllocate;
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
                                
                                $batchData['quantity'] -= $batchAllocation;
                                $remainingToAllocate -= $batchAllocation;
                                
                                $this->info("    Allocated {$batchAllocation} units from batch {$batchData['batch_number']}");
                                $this->info("    Remaining in batch: {$batchData['quantity']} units");
                            }
                            
                            $this->info("✓ Total allocated {$totalToAllocate} units to order {$facilityOrders[$facilityId]['order']->order_number}");
                            
                            // Show remaining inventory after this allocation
                            $remainingTotal = array_sum(array_column($inventoryTracker, 'quantity'));
                            $this->info("Remaining total inventory: {$remainingTotal} units");
                        }
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
        }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
