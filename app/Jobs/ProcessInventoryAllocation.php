<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OrderItem;
use App\Models\InventoryAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessInventoryAllocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderItem;
    protected $neededQuantity;

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
    public $backoff = [30, 60, 120]; // 30 secs, 1 min, 2 mins

    /**
     * Create a new job instance.
     */
    public function __construct(OrderItem $orderItem, int $neededQuantity)
    {
        $this->orderItem = $orderItem;
        $this->neededQuantity = $neededQuantity;
        $this->onQueue('inventory-allocation');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info("Processing inventory allocation for order item", [
            'order_item_id' => $this->orderItem->id,
            'product_id' => $this->orderItem->product_id,
            'needed_quantity' => $this->neededQuantity
        ]);

        // Get available inventory from inventory_items table
        $inventories = DB::table('inventories')
            ->join('inventory_items', 'inventories.id', '=', 'inventory_items.inventory_id')
            ->where('inventories.product_id', $this->orderItem->product_id)
            ->where('inventory_items.quantity', '>', 0)
            ->orderBy('inventory_items.expiry_date') // Prioritize items that expire sooner
            ->select(
                'inventory_items.id',
                'inventory_items.warehouse_id',
                'inventory_items.location',
                'inventory_items.batch_number',
                'inventory_items.expiry_date',
                'inventory_items.quantity',
                'inventory_items.uom',
                'inventory_items.unit_cost',
                'inventory_items.total_cost'
            )
            ->get();

        $totalAvailable = $inventories->sum('quantity');
        
        if ($totalAvailable <= 0) {
            Log::warning("No inventory available for allocation", [
                'order_item_id' => $this->orderItem->id,
                'product_id' => $this->orderItem->product_id
            ]);
            return;
        }

        // Calculate allocation ratio if not enough inventory
        $allocationRatio = min(1.0, $totalAvailable / $this->neededQuantity);
        $totalToAllocate = floor($this->neededQuantity * $allocationRatio);

        if ($totalToAllocate <= 0) {
            Log::info("No allocation needed", [
                'order_item_id' => $this->orderItem->id,
                'allocation_ratio' => $allocationRatio
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            // Update order item with allocation
            $this->orderItem->quantity_to_release = $totalToAllocate;
            $this->orderItem->save();

            // Allocate from batches
            $remainingToAllocate = $totalToAllocate;
            foreach ($inventories as $inventory) {
                if ($remainingToAllocate <= 0 || $inventory->quantity <= 0) {
                    continue;
                }

                $batchAllocation = min($inventory->quantity, $remainingToAllocate);

                // Create inventory allocation
                InventoryAllocation::create([
                    'order_item_id' => $this->orderItem->id,
                    'product_id' => $this->orderItem->product_id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'location' => $inventory->location,
                    'batch_number' => $inventory->batch_number,
                    'expiry_date' => $inventory->expiry_date,
                    'allocated_quantity' => $batchAllocation,
                    'allocation_type' => 'quarterly',
                    'unit_cost' => $inventory->unit_cost,
                    'total_cost' => $inventory->unit_cost * $batchAllocation,
                    'uom' => $inventory->uom,
                    'notes' => "Quarterly allocation from batch {$inventory->batch_number} (expires {$inventory->expiry_date})"
                ]);

                // Update inventory
                DB::table('inventory_items')
                    ->where('id', $inventory->id)
                    ->update([
                        'quantity' => DB::raw("quantity - {$batchAllocation}"),
                        'total_cost' => DB::raw("unit_cost * (quantity - {$batchAllocation})")
                    ]);

                $remainingToAllocate -= $batchAllocation;

                Log::info("Allocated from batch", [
                    'batch_number' => $inventory->batch_number,
                    'allocated_quantity' => $batchAllocation,
                    'remaining_in_batch' => $inventory->quantity - $batchAllocation
                ]);
            }

            DB::commit();
            Log::info("Successfully processed inventory allocation", [
                'order_item_id' => $this->orderItem->id,
                'total_allocated' => $totalToAllocate
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing inventory allocation", [
                'order_item_id' => $this->orderItem->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 