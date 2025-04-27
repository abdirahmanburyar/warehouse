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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'serial_number' => $this->serial_number,
            'category' => $this->category,
            'location' => $this->location,
            'custody' => $this->custody,
            'quantity' => $this->quantity,
            'purchase_date' => $this->purchase_date,
            'purchase_cost' => $this->purchase_cost,
            'transfer_date' => $this->transfer_date,
            'notes' => $this->notes,
            'status' => $this->status,            
            'custody_histories' => $this->whenLoaded('custodyHistories', function() {
                return $this->custodyHistories->map(function($history) {
                    return [
                        'id' => $history->id,
                        'custodian' => $history->custodian,
                        'assigned_by' => $history->assignedBy ? [
                            'id' => $history->assignedBy->id,
                            'name' => $history->assignedBy->name,
                        ] : null,
                        'assigned_at' => $history->assigned_at,
                        'returned_at' => $history->returned_at,
                        'assignment_notes' => $history->assignment_notes,
                        'return_notes' => $history->return_notes,
                        'status' => $history->status,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
