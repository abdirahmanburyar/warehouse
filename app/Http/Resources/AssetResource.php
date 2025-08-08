<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $metadata = is_array($this->metadata ?? null) ? $this->metadata : (json_decode($this->metadata ?? '[]', true) ?: []);
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'asset_tag' => $this->asset_tag,
            'tag_no' => $this->tag_no,
            'name' => $this->name,
            'asset_category_id' => $this->asset_category_id,
            'type_id' => $this->type_id,
            'serial_number' => $this->serial_number,
            'serial_no' => $this->serial_no,
            'item_description' => $this->item_description,
            'person_assigned' => $this->person_assigned,
            'asset_location_id' => $this->asset_location_id,
            'assigned_to' => $this->assigned_to,
            'assignee_id' => $this->assignee_id,
            'fund_source_id' => $this->fund_source_id,
            'sub_location_id' => $this->sub_location_id,
            'acquisition_date' => optional($this->acquisition_date)->format('Y-m-d'),
            'has_warranty' => (bool) $this->has_warranty,
            'has_documents' => (bool) $this->has_documents,
            'asset_warranty_start' => optional($this->asset_warranty_start)->format('Y-m-d'),
            'asset_warranty_end' => optional($this->asset_warranty_end)->format('Y-m-d'),
            'warranty_start' => optional($this->warranty_start)->format('Y-m-d'),
            'warranty_months' => $this->warranty_months,
            'maintenance_interval_months' => $this->maintenance_interval_months,
            'last_maintenance_at' => optional($this->last_maintenance_at)->format('Y-m-d'),
            'metadata' => $metadata,
            'region_id' => $this->region_id,
            'sub_location' => $this->whenLoaded('subLocation', fn() => [
                'id' => $this->subLocation->id,
                'name' => $this->subLocation->name,
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'location' => $this->whenLoaded('location', fn() => [
                'id' => $this->location->id,
                'name' => $this->location->name,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn() => [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
            'type' => $this->whenLoaded('type', fn() => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ]),
            'history' => $this->whenLoaded('history', fn() => $this->history),
            'attachments' => $this->whenLoaded('attachments', fn() => $this->attachments),
            'fund_source' => $this->whenLoaded('fundSource', fn() => $this->fundSource),
            'region' => $this->whenLoaded('region', fn() => $this->region),
            'status' => $this->status,
            'original_value' => $this->original_value,
            'submitted_for_approval' => (bool) $this->submitted_for_approval,
            'submitted_at' => $this->submitted_at,
            'submitted_by' => $this->submitted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
