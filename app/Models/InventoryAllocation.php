<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAllocation extends Model
{
    protected $fillable = [
        'order_item_id',
        'transfer_item_id',
        'product_id',
        'warehouse_id',
        'location_id',
        'batch_number',
        'expiry_date',
        'allocated_quantity',
        'allocation_type',
        'unit_cost',
        'total_cost',
        'notes',
        'uom'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }

    public function transfer_item(){
        return $this->belongsTo(TransferItem::class);
    }

    public function order_item(){
        return $this->belongsTo(OrderItem::class);
    }

    public function back_order(){
        return $this->hasMany(FacilityBackorder::class, 'inventory_allocation_id');
    }
}
