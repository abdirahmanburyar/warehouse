<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpiredResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $expiryDate = $this->expiry_date ? Carbon::parse($this->expiry_date) : null;
        $today = Carbon::now();
        
        // Calculate expiry status
        $isExpired = $expiryDate && $expiryDate < $today;
        $isNearExpiry = !$isExpired && $expiryDate && $expiryDate <= $today->copy()->addDays(30);
        $daysUntilExpiry = $expiryDate ? $expiryDate->diffInDays($today, false) : null;
        
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'product_name' => $this->product_name ?? $this->product->name ?? 'Unknown',
            'warehouse_name' => $this->warehouse->name ?? 'Unknown',
            'quantity' => $this->quantity,
            'batch_number' => $this->batch_number,
            'location' => $this->location,
            'manufacturing_date' => $this->manufacturing_date,
            'expiry_date' => $this->expiry_date,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            
            // Pre-computed status information
            'is_expired' => $isExpired,
            'is_near_expiry' => $isNearExpiry,
            'days_until_expiry' => $daysUntilExpiry,
            'status' => !$this->is_active ? 'disposed' : ($isExpired ? 'expired' : ($isNearExpiry ? 'near' : 'active')),
            
            // Include relationships if loaded
            'product' => $this->whenLoaded('product'),
            'warehouse' => $this->whenLoaded('warehouse'),
        ];
    }
} 