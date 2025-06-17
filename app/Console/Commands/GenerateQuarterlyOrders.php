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
            
            // Don't start a transaction for the entire process to avoid timeouts
            // We'll use smaller transactions for critical operations
            $this->info("Starting quarterly order generation...");

            // Get today's date
            $today = Carbon::parse('31-03-2025'); // For testing
            $month = $today->month;
            $day = $today->day;
            $year = $today->year;

            // Commenting out this check for testing purposes
            // Ensure today is the last day of a quarter
            // if (!($month == 3 && $day == 31) && !($month == 6 && $day == 30) &&
            //     !($month == 9 && $day == 30) && !($month == 12 && $day == 31)) {
            //     $this->error('Today is not the last day of a quarter.');
            //     return;
            // }

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
            // Process only Dangorayo Health Center
            $facilities = Facility::where('is_active', true)
                ->where('name', 'Dangorayo Health Center')
                ->get();
            
            if ($facilities->isEmpty()) {
                $this->error("Dangorayo Health Center not found or not active");
                return;
            }
            
            $totalOrders = 0;
            $totalOrderItems = 0;
            
            $this->info("Processing only Dangorayo Health Center");
            
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
                    $product = $eligibleItem->product;
                    
                    if (!$product) {
                        $this->warn("Product ID {$productId} not found, skipping");
                        continue;
                    }
                    
                    $this->info("Processing product: {$product->name} [{$product->movement_type}]");
                    
                    // Calculate AMC (Average Monthly Consumption) from monthly_consumption table
                    $monthsInQuarter = $this->getMonthsInQuarter($product->movement_type);
                    
                    // Get the previous months for AMC calculation
                    $months = [];
                    $currentDate = $today->copy();
                    
                    // Get the last 4 months (or as many as needed based on movement type)
                    for ($i = 0; $i < 4; $i++) {
                        $monthYear = $currentDate->format('Y-m');
                        $months[] = $monthYear;
                        $currentDate->subMonth();
                    }
                    
                    // Limit to the number of months we need based on movement type
                    $months = array_slice($months, 0, $monthsInQuarter);
                    
                    $this->info("Calculating AMC using months: " . implode(', ', $months));
                    
                    // Get consumption data from monthly_consumptions table
                    $this->info("Querying monthly_consumptions for facility_id: {$facility->id}, product_id: {$productId}");
                    
                    try {
                        $consumptionData = DB::table('monthly_consumptions')
                            ->where('facility_id', $facility->id)
                            ->where('product_id', $productId)
                            ->whereIn('month_year', $months)
                            ->get();
                            
                        $this->info("Found " . count($consumptionData) . " consumption records");
                        
                        // Debug: Show each consumption record
                        foreach ($consumptionData as $record) {
                            $this->info("  Month: {$record->month_year}, Quantity: {$record->quantity}");
                        }
                        
                        $totalConsumption = $consumptionData->sum('quantity');
                        $monthsWithData = $consumptionData->count();
                        $this->info("Total consumption: {$totalConsumption}, Months with data: {$monthsWithData}");
                    } catch (\Exception $e) {
                        $this->error("Error querying consumption data: " . $e->getMessage());
                        $totalConsumption = 0;
                        $monthsWithData = 0;
                    }
                    
                    // Calculate AMC
                    if ($monthsWithData > 0) {
                        $amc = $totalConsumption / $monthsWithData;
                        $this->info("Calculated AMC from $monthsWithData months: $amc");
                    } else {
                        // For testing, set a minimum AMC if no data is available
                        $amc = 10; // Set a default AMC for testing
                        $this->info("No consumption data found. Setting default AMC of 10 for testing");
                    }
                    
                        // Calculate SOH (Stock on Hand)
                    $soh = DB::table('facility_inventories')
                        ->where('facility_id', $facility->id)
                        ->where('product_id', $productId)
                        ->sum('quantity');
                        
                    // For testing, ensure SOH is less than AMC*months to generate orders
                    $this->info("Original SOH: {$soh}");
                    
                    // Calculate needed quantity
                    $neededQuantity = ($amc * $monthsInQuarter) - $soh;
                    $neededQuantity = max(0, ceil($neededQuantity)); // Ensure non-negative and round up
                    
                    if ($neededQuantity <= 0) {
                        $this->info("No need for {$product->name}, AMC: {$amc}, SOH: {$soh}, Needed: 0");
                        continue;
                    }
                    
                    $this->info("AMC: {$amc}, SOH: {$soh}, Needed for {$monthsInQuarter} months: {$neededQuantity}");
                    
                    // Add to product orders
                    if (!isset($productOrders[$productId])) {
                        $productOrders[$productId] = [
                            'product' => $product,
                            'facilities' => [],
                            'total_needed' => 0,
                            'inventory' => []
                        ];
                    }
                    
                    $productOrders[$productId]['facilities'][$facility->id] = [
                        'facility' => $facility,
                        'needed' => $neededQuantity,
                        'allocated' => 0
                    ];
                    
                    $productOrders[$productId]['total_needed'] += $neededQuantity;
                    
                    // Add to facility orders
                    $facilityOrders[$facility->id]['items'][$productId] = [
                        'product' => $product,
                        'needed' => $neededQuantity,
                        'allocated' => 0
                    ];
                }
            }
            
            // Second pass: Get available inventory for each product
            foreach ($productOrders as $productId => &$productData) {
                $product = $productData['product'];
                $totalNeeded = $productData['total_needed'];
                
                $this->info("\nProcessing inventory for {$product->name} === {$product->description} ===");
                $this->info("Total needed: {$totalNeeded}");
                
                // Get available inventory
                $inventories = DB::table('inventories')
                    ->where('product_id', $productId)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date') // Prioritize items that expire sooner
                    ->get();
                
                $totalAvailable = $inventories->sum('quantity');
                $this->info("Total inventory available: {$totalAvailable}");
                
                // For testing purposes, create some inventory if none exists
                if ($totalAvailable <= 0) {
                    $this->warn("No inventory available for {$product->name}, creating test inventory");
                    
                    // Find a warehouse to assign inventory to
                    $warehouse = DB::table('warehouses')->first();
                    
                    if ($warehouse) {
                        // Create test inventory entry
                        $inventoryId = DB::table('inventories')->insertGetId([
                            'product_id' => $productId,
                            'warehouse_id' => $warehouse->id,
                            'location_id' => 1, // Default location
                            'batch_number' => 'TEST-' . Str::random(6),
                            'quantity' => $totalNeeded * 2, // Double what's needed
                            'expiry_date' => now()->addYear(),
                            'uom' => $product->uom ?? 'each', // Add the missing UOM field
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        // Refresh inventories
                        $inventories = DB::table('inventories')
                            ->where('product_id', $productId)
                            ->where('quantity', '>', 0)
                            ->orderBy('expiry_date')
                            ->get();
                        
                        $totalAvailable = $inventories->sum('quantity');
                        $this->info("Created test inventory. New total available: {$totalAvailable}");
                    } else {
                        $this->error("No warehouses found to create test inventory");
                        continue;
                    }
                }
                
                // Track inventory for allocation
                $inventoryTracker = [];
                foreach ($inventories as $inventory) {
                    $inventoryTracker[$inventory->id] = [
                        'warehouse_id' => $inventory->warehouse_id,
                        'location_id' => $inventory->location_id,
                        'batch_number' => $inventory->batch_number,
                        'expiry_date' => $inventory->expiry_date,
                        'quantity' => $inventory->quantity,
                        'uom' => $inventory->uom,
                    ];
                }
                
                $productData['inventory'] = $inventoryTracker;
                
                // Calculate allocation ratio if not enough inventory
                $allocationRatio = 1.0;
                if ($totalAvailable < $totalNeeded) {
                    $allocationRatio = $totalAvailable / $totalNeeded;
                    $this->warn("Insufficient inventory. Allocation ratio: " . number_format($allocationRatio, 2));
                }
                
                // Third pass: Allocate inventory to facilities
                foreach ($productData['facilities'] as $facilityId => &$facilityData) {
                    $facility = $facilityData['facility'];
                    $needed = $facilityData['needed'];
                    
                    // Calculate allocation based on ratio
                    $totalToAllocate = min($needed, floor($needed * $allocationRatio));
                    
                    if ($totalToAllocate <= 0) {
                        $this->info("No allocation for {$facility->name}, needed: {$needed}, allocated: 0");
                        continue;
                    }
                    
                    $facilityData['allocated'] = $totalToAllocate;
                    $facilityOrders[$facilityId]['items'][$productId]['allocated'] = $totalToAllocate;
                    
                    $this->info("Allocating {$totalToAllocate} units of {$product->name} to {$facility->name}");
                    
                    // Use a transaction for each order item creation and inventory allocation
                    DB::beginTransaction();
                    try {
                        // Create order item
                        $orderItem = OrderItem::create([
                            'order_id' => $facilityOrders[$facilityId]['order']->id,
                            'product_id' => $productId,
                            'quantity' => $totalToAllocate,
                            'unit_price' => $product->unit_price ?? 0,
                            'total_price' => ($product->unit_price ?? 0) * $totalToAllocate,
                            'amc' => $facilityOrders[$facilityId]['items'][$productId]['needed'] / $this->getMonthsInQuarter($product->movement_type),
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
                        
                        // Commit the transaction for this order item
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Error processing order item: " . $e->getMessage());
                        // Continue with the next item
                        continue;
                    }
                    
                    $this->info("✓ Total allocated {$totalToAllocate} units to order {$facilityOrders[$facilityId]['order']->order_number}");
                    
                    // Show remaining inventory after this allocation
                    $remainingTotal = array_sum(array_column($inventoryTracker, 'quantity'));
                    $this->info("Remaining total inventory: {$remainingTotal} units");
                }
            }
            
            // Clean up empty orders
            foreach ($facilityOrders as $facilityId => $orderData) {
                try {
                    DB::beginTransaction();
                    $orderItemCount = OrderItem::where('order_id', $orderData['order']->id)->count();
                    if ($orderItemCount === 0) {
                        $orderData['order']->delete();
                        $this->warn("No items allocated, deleted order for facility ID: {$facilityId}");
                    } else {
                        $totalOrders++;
                        $this->info("Finalized order with {$orderItemCount} items for facility ID: {$facilityId}");
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error cleaning up order: " . $e->getMessage());
                }
            }

            $this->info("\n✅ Completed Successfully!");
            $this->info("Total: {$totalOrders} orders with {$totalOrderItems} items.");
        } catch (\Exception $e) {
            $this->error('❌ Failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
