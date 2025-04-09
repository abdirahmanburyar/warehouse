<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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
            'po_number' => $this->po_number,
            'po_date' => $this->po_date,
            'status' => $this->status,
            'supplier' => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
            ],
            'po_items' => $this->po_items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'received_quantity' => $item->received_quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->total_cost,
                    'warehouse_id' => $item->warehouse_id,
                    'warehouse_name' => optional($item->warehouse)->name,
                    'location' => $item->location,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date,
                    'generic_name' => $item->generic_name,
                ];
            }),
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
