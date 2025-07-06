<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivedBackorderResource extends JsonResource
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
            'received_backorder_number' => $this->received_backorder_number,
            'product_id' => $this->product_id,
            'product' => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'productID' => $this->product?->productID,
            ],
            'received_by' => $this->received_by,
            'received_by_user' => [
                'id' => $this->receivedBy?->id,
                'name' => $this->receivedBy?->name,
            ],
            'barcode' => $this->barcode,
            'expire_date' => $this->expire_date,
            'batch_number' => $this->batch_number,
            'uom' => $this->uom,
            'received_at' => $this->received_at,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'type' => $this->type,
            'location' => $this->location,
            'facility' => $this->facility,
            'warehouse' => $this->warehouse,
            'unit_cost' => $this->unit_cost,
            'total_cost' => $this->total_cost,
            'note' => $this->note,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_by_user' => [
                'id' => $this->reviewedBy?->id,
                'name' => $this->reviewedBy?->name,
            ],
            'reviewed_at' => $this->reviewed_at,
            'approved_by' => $this->approved_by,
            'approved_by_user' => [
                'id' => $this->approvedBy?->id,
                'name' => $this->approvedBy?->name,
            ],
            'approved_at' => $this->approved_at,
            'rejected_by' => $this->rejected_by,
            'rejected_by_user' => [
                'id' => $this->rejectedBy?->id,
                'name' => $this->rejectedBy?->name,
            ],
            'rejected_at' => $this->rejected_at,
            'rejection_reason' => $this->rejection_reason,
            'attachments' => $this->attachments,
            'back_order_id' => $this->back_order_id,
            'packing_list_id' => $this->packing_list_id,
            'packing_list_number' => $this->packing_list_number,
            'purchase_order_id' => $this->purchase_order_id,
            'purchase_order_number' => $this->purchase_order_number,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 