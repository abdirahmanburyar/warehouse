<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\CustodyHistory;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        CustodyHistory::create([
            'asset_id' => $asset->id,
            'custodian' => $asset->custody,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'status' => $asset->status,
            'status_notes' => 'Initial status on asset creation'
        ]);
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        $changes = [];
        $notes = [];

        // Check for custody change
        if ($asset->wasChanged('custody') && $asset->custody !== null) {
            $changes['custodian'] = $asset->custody;
            $notes[] = 'Custody changed to: ' . $asset->custody;
        }

        // Check for status change
        if ($asset->wasChanged('status')) {
            $changes['status'] = $asset->status;
            $notes[] = 'Status changed to: ' . $asset->status;
        }

        // Only create history if there are changes
        if (!empty($changes)) {
            // Check if there's an existing record with the same changes
            $existingRecord = CustodyHistory::where('asset_id', $asset->id)
                ->where(function($query) use ($changes) {
                    foreach($changes as $field => $value) {
                        $query->where($field, $value);
                    }
                })
                ->whereNull('returned_at')
                ->exists();

            // Only create a new record if it doesn't exist
            if (!$existingRecord) {
                CustodyHistory::create([
                    'asset_id' => $asset->id,
                    'custodian' => $changes['custodian'] ?? $asset->custody,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'status' => $changes['status'] ?? $asset->status,
                    'status_notes' => implode('; ', $notes)
                ]);
            }
        }
    }
}
