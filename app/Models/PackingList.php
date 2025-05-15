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
        'po_id',
        'status',
        'product_id',
        'warehouse_id',
        'ref_no',
        'expire_date',
        'batch_number',
        'location_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'confirmed_at',
        'confirmed_by',
        'reviewed_at',
        'reviewed_by',
        'approved_at',
        'approved_by',

    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function po_item()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_id');
    }

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

    protected static function booted()
    {
        static::saving(function ($packingList) {
            $packingList->total_cost = $packingList->unit_cost * $packingList->quantity;
        });
    }
}
