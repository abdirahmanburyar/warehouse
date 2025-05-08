<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_list_number',
        'purchase_order_id',
        'product_id',
        'warehouse_id',
        'expire_date',
        'batch_number',
        'location',
        'quantity',
        'unit_cost',
        'total_cost'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function differences()
    {
        return $this->hasMany(PackingListDifference::class, 'packing_list_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function purchaseOrderItems(){
        return $this->hasMany(PurchaseOrderItem::class, 'packing_list_id', 'id');
    }
}
