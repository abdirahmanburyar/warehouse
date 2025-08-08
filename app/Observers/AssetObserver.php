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
            'custodian' => $asset->person_assigned ?? 'Unassigned',
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
        if ($asset->wasChanged('person_assigned') && $asset->person_assigned !== null) {
            $changes['custodian'] = $asset->person_assigned;
            $notes[] = 'Custody changed to: ' . $asset->person_assigned;
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
                    'custodian' => $changes['custodian'] ?? $asset->person_assigned,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'status' => $changes['status'] ?? $asset->status,
                    'status_notes' => implode('; ', $notes)
                ]);
            }
        }
    }
}
