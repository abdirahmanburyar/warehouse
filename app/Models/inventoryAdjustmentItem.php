<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'parent_id',
        'user_id',
        'quantity',
        'expiry_date',
        'location_id',
        'warehouse_id',
        'uom',
        'product_id',
        'batch_number',
        'barcode',
        'physical_count',
        'difference',
        'remarks'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function inventoryAdjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'parent_id');
    }
}
