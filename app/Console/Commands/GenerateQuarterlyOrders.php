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
                    $product = $eligibleItem->product;
                    
                    if (!$product) {
                        $this->warn("Product ID {$productId} not found, skipping");
                        continue;
                    }
                    
                    $this->info("Processing product: {$product->name} [{$product->movement_type}]");
                    
                    // Calculate AMC (Average Monthly Consumption)
                    $amc = DB::table('order_items')
                        ->join('orders', 'orders.id', '=', 'order_items.order_id')
                        ->where('orders.facility_id', $facility->id)
                        ->where('order_items.product_id', $productId)
                        ->where('orders.status', 'received')
                        ->where('orders.created_at', '>=', $prevQuarterStart)
                        ->where('orders.created_at', '<=', $prevQuarterEnd)
                        ->sum('order_items.quantity');
                    
                    // For testing, set a minimum AMC if it's zero
                    if ($amc == 0) {
                        $amc = 10; // Set a default AMC for testing
                        $this->info("Setting default AMC of 10 for testing");
                    }
                    
                    $monthsInQuarter = $this->getMonthsInQuarter($product->movement_type);
                    $amc = $amc / 3; // Divide by 3 months to get monthly average
                    
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
                        'quantity' => $inventory->quantity
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
                    
                    $this->info("✓ Total allocated {$totalToAllocate} units to order {$facilityOrders[$facilityId]['order']->order_number}");
                    
                    // Show remaining inventory after this allocation
                    $remainingTotal = array_sum(array_column($inventoryTracker, 'quantity'));
                    $this->info("Remaining total inventory: {$remainingTotal} units");
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
