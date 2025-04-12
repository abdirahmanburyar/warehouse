<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'sku' => $this->product->sku,
                    'barcode' => $this->product->barcode,
                    'category' => $this->product->category ? [
                        'id' => $this->product->category->id,
                        'name' => $this->product->category->name,
                    ] : null,
                    'dosage' => $this->product->dosage ? [
                        'id' => $this->product->dosage->id,
                        'name' => $this->product->dosage->name,
                    ] : null,
                ];
            }),
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                    'code' => $this->warehouse->code,
                ];
            }),
            'quantity' => $this->quantity,
            'reorder_level' => $this->reorder_level,
            'unit_cost' => $this->unit_cost,
            'unit_price' => $this->unit_price,
            'manufacturing_date' => $this->manufacturing_date ? $this->manufacturing_date->format('Y-m-d') : null,
            'expiry_date' => $this->expiry_date ? $this->expiry_date->format('Y-m-d') : null,
            'batch_number' => $this->batch_number,
            'location' => $this->location,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
