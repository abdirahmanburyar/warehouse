<?php

namespace App\Jobs;

use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\ReceivedBackorder;
use App\Models\ReceivedBackorderItem;
use App\Models\Liquidate;
use App\Models\LiquidateItem;
use App\Notifications\PhysicalCountApprovalCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProcessPhysicalCountApprovalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3; // Retry 3 times if it fails

    protected $adjustmentId;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($adjustmentId, $userId)
    {
        $this->adjustmentId = $adjustmentId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting physical count approval processing for adjustment ID: {$this->adjustmentId}");
            
            $adjustment = InventoryAdjustment::findOrFail($this->adjustmentId);
            
            // Check if adjustment is still in reviewed status
            if ($adjustment->status !== 'reviewed') {
                Log::warning("Adjustment {$this->adjustmentId} is not in reviewed status. Current status: {$adjustment->status}");
                return;
            }

            // Process adjustment items in chunks to handle large datasets
            InventoryAdjustmentItem::where('parent_id', $this->adjustmentId)
                ->with('warehouse')
                ->chunkById(100, function ($chunk) use ($adjustment) {
                    foreach ($chunk as $adjustmentItem) {
                        $this->processAdjustmentItem($adjustmentItem, $adjustment);
                    }
                });

            // Update adjustment status to approved
            $adjustment->update([
                'status' => 'approved',
                'approved_by' => $this->userId,
                'approved_at' => now()
            ]);

            // Send notification to the user who approved it
            $user = \App\Models\User::find($this->userId);
            if ($user) {
                $user->notify(new PhysicalCountApprovalCompleted($adjustment));
            }

            Log::info("Successfully completed physical count approval processing for adjustment ID: {$this->adjustmentId}");

        } catch (\Exception $e) {
            Log::error("Error processing physical count approval for adjustment ID {$this->adjustmentId}: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Process a single adjustment item
     */
    protected function processAdjustmentItem($adjustmentItem, $adjustment): void
    {
        $difference = $adjustmentItem->difference;
        
        if ($difference > 0) {
            // Positive difference - create ReceivedBackorder
            $receivedBackorder = ReceivedBackorder::create([
                'received_by' => $this->userId,
                'received_at' => now(),
                'status' => 'pending',
                'type' => 'physical_count_adjustment',
                'warehouse_id' => $adjustmentItem->warehouse_id,
                'inventory_adjustment_id' => $adjustment->id,
                'note' => 'Physical count adjustment - positive difference'
            ]);
            
            // Create ReceivedBackorderItem
            ReceivedBackorderItem::create([
                'received_backorder_id' => $receivedBackorder->id,
                'product_id' => $adjustmentItem->product_id,
                'quantity' => $difference,
                'barcode' => $adjustmentItem->barcode,
                'expire_date' => $adjustmentItem->expiry_date,
                'batch_number' => $adjustmentItem->batch_number === 'N/A' ? null : $adjustmentItem->batch_number,
                'uom' => $adjustmentItem->uom,
                'location' => null,
                'note' => $adjustmentItem->remarks
            ]);
            
        } elseif ($difference < 0) {
            // Negative difference - create Liquidate
            $liquidate = Liquidate::create([
                'liquidated_by' => $this->userId,
                'liquidated_at' => now(),
                'status' => 'pending',
                'source' => 'physical_count_adjustment',
                'warehouse' => $adjustmentItem->warehouse->name ?? 'Unknown',
                'inventory_adjustment_id' => $adjustment->id
            ]);
            
            // Create LiquidateItem
            LiquidateItem::create([
                'liquidate_id' => $liquidate->id,
                'product_id' => $adjustmentItem->product_id,
                'quantity' => abs($difference),
                'barcode' => $adjustmentItem->barcode,
                'expire_date' => $adjustmentItem->expiry_date,
                'batch_number' => $adjustmentItem->batch_number === 'N/A' ? null : $adjustmentItem->batch_number,
                'uom' => $adjustmentItem->uom,
                'location' => null,
                'note' => $adjustmentItem->remarks,
                'type' => 'physical_count_adjustment'
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Physical count approval job failed for adjustment ID {$this->adjustmentId}: " . $exception->getMessage());
        
        // Optionally, you could send a notification to admin or update the adjustment status
        // $adjustment = InventoryAdjustment::find($this->adjustmentId);
        // if ($adjustment) {
        //     $adjustment->update(['status' => 'failed']);
        // }
    }
} 