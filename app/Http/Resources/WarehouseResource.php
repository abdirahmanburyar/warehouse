<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
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
            'code' => $this->code,
            'address' => $this->address,
            'state' => $this->state ? $this->state->name : null,
            'district' => $this->district ? $this->district->name : null,
            'city' => $this->city ? $this->city->name : null,
            'state_id' => $this->state_id,
            'district_id' => $this->district_id,
            'city_id' => $this->city_id,
            'manager_name' => $this->manager_name,
            'manager_phone' => $this->manager_phone,
            'manager_email' => $this->manager_email,
            'status' => $this->status,
            'status_badge' => $this->getStatusBadge(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Get the status badge HTML.
     *
     * @return string
     */
    private function getStatusBadge(): string
    {
        $statusClasses = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'maintenance' => 'bg-yellow-100 text-yellow-800',
        ];
        
        $class = $statusClasses[$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return $class;
    }
}
