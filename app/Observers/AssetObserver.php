<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\Assignee;
use App\Models\AssetLocation;
use App\Models\SubLocation;
use App\Models\Region;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\FundSource;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        // If controller already created initial history, skip
        if ($asset->assetHistory()->exists()) {
            return;
        }
        // Record initial status
        if (!empty($asset->status)) {
            $asset->createStatusChangeHistory(null, $asset->status, 'Asset created');
        }
        // Record initial custody if any
        if (!empty($asset->assignee_id)) {
            $asset->createCustodyChangeHistory(null, $asset->assignee_id, 'Initial custody');
        } elseif (!empty($asset->person_assigned)) {
            $asset->createCustodyChangeHistory(null, $asset->person_assigned, 'Initial custody');
        }
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        // Deduplicate by looking for identical recent asset_history entries
        $now = now();

        // Status change
        if ($asset->wasChanged('status')) {
            $recent = AssetHistory::where('asset_id', $asset->id)
                ->where('action', 'status_changed')
                ->where('action_type', 'status_change')
                ->where('performed_at', '>=', $now->copy()->subMinute())
                ->where('new_value->status', $asset->status)
                ->exists();
            if (!$recent) {
                $asset->createStatusChangeHistory($asset->getOriginal('status'), $asset->status, 'Status updated');
            }
        }

        // Custody change via assignee
        if ($asset->wasChanged('assignee_id')) {
            $oldId = $asset->getOriginal('assignee_id');
            $newId = $asset->assignee_id;
            $recent = AssetHistory::where('asset_id', $asset->id)
                ->where('action', 'custody_changed')
                ->where('action_type', 'transfer')
                ->where('performed_at', '>=', $now->copy()->subMinute())
                ->where('new_value->assignee_id', $newId)
                ->exists();
            if (!$recent) {
                $asset->createCustodyChangeHistory($oldId, $newId, 'Custody updated');
            }
        } elseif ($asset->wasChanged('person_assigned')) {
            $oldName = $asset->getOriginal('person_assigned');
            $newName = $asset->person_assigned;
            $recent = AssetHistory::where('asset_id', $asset->id)
                ->where('action', 'custody_changed')
                ->where('action_type', 'transfer')
                ->where('performed_at', '>=', $now->copy()->subMinute())
                ->where('new_value->person_assigned', $newName)
                ->exists();
            if (!$recent) {
                $asset->createCustodyChangeHistory($oldName, $newName, 'Custody updated');
            }
        }

        // Classification changes (region/location/sub-location/category/type/fund source)
        $oldValues = [];
        $newValues = [];

        if ($asset->wasChanged('region_id')) {
            $oldId = $asset->getOriginal('region_id');
            $newId = $asset->region_id;
            $oldValues['region'] = ['id' => $oldId, 'name' => optional(Region::find($oldId))->name];
            $newValues['region'] = ['id' => $newId, 'name' => optional(Region::find($newId))->name];
        }

        if ($asset->wasChanged('asset_location_id')) {
            $oldId = $asset->getOriginal('asset_location_id');
            $newId = $asset->asset_location_id;
            $oldValues['asset_location'] = ['id' => $oldId, 'name' => optional(AssetLocation::find($oldId))->name];
            $newValues['asset_location'] = ['id' => $newId, 'name' => optional(AssetLocation::find($newId))->name];
        }

        if ($asset->wasChanged('sub_location_id')) {
            $oldId = $asset->getOriginal('sub_location_id');
            $newId = $asset->sub_location_id;
            $oldValues['sub_location'] = ['id' => $oldId, 'name' => optional(SubLocation::find($oldId))->name];
            $newValues['sub_location'] = ['id' => $newId, 'name' => optional(SubLocation::find($newId))->name];
        }

        if ($asset->wasChanged('asset_category_id')) {
            $oldId = $asset->getOriginal('asset_category_id');
            $newId = $asset->asset_category_id;
            $oldValues['category'] = ['id' => $oldId, 'name' => optional(AssetCategory::find($oldId))->name];
            $newValues['category'] = ['id' => $newId, 'name' => optional(AssetCategory::find($newId))->name];
        }

        if ($asset->wasChanged('type_id')) {
            $oldId = $asset->getOriginal('type_id');
            $newId = $asset->type_id;
            $oldValues['type'] = ['id' => $oldId, 'name' => optional(AssetType::find($oldId))->name];
            $newValues['type'] = ['id' => $newId, 'name' => optional(AssetType::find($newId))->name];
        }

        if ($asset->wasChanged('fund_source_id')) {
            $oldId = $asset->getOriginal('fund_source_id');
            $newId = $asset->fund_source_id;
            $oldValues['fund_source'] = ['id' => $oldId, 'name' => optional(FundSource::find($oldId))->name];
            $newValues['fund_source'] = ['id' => $newId, 'name' => optional(FundSource::find($newId))->name];
        }

        if (!empty($oldValues) || !empty($newValues)) {
            // Create a single classification change history entry
            AssetHistory::create([
                'asset_id' => $asset->id,
                'action' => 'classification_changed',
                'action_type' => 'classification',
                'old_value' => $oldValues ?: null,
                'new_value' => $newValues ?: null,
                'notes' => 'Classification details updated',
                'performed_by' => auth()->id(),
                'performed_at' => $now,
            ]);
        }
    }
}
