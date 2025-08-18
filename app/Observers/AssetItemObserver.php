<?php

namespace App\Observers;

use App\Models\AssetItem;

class AssetItemObserver
{
    /**
     * Handle the AssetItem "created" event.
     */
    public function created(AssetItem $assetItem): void
    {
        // Calculate total cost when created
        $assetItem->calculateTotalCost();
        $assetItem->saveQuietly(); // Save without triggering events
    }

    /**
     * Handle the AssetItem "updated" event.
     */
    public function updated(AssetItem $assetItem): void
    {
        // Recalculate total cost if quantity or unit cost changed
        if ($assetItem->wasChanged(['quantity', 'unit_cost'])) {
            $assetItem->calculateTotalCost();
            $assetItem->saveQuietly(); // Save without triggering events
        }
    }

    /**
     * Handle the AssetItem "deleted" event.
     */
    public function deleted(AssetItem $assetItem): void
    {
        // Log deletion or perform cleanup if needed
    }

    /**
     * Handle the AssetItem "restored" event.
     */
    public function restored(AssetItem $assetItem): void
    {
        // Recalculate total cost when restored
        $assetItem->calculateTotalCost();
        $assetItem->saveQuietly(); // Save without triggering events
    }

    /**
     * Handle the AssetItem "force deleted" event.
     */
    public function forceDeleted(AssetItem $assetItem): void
    {
        // Perform permanent cleanup if needed
    }
}
