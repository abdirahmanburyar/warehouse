<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_list_id',
        'uom',
        'barcode',
        'product_id',
        'warehouse_id',
        'expire_date',
        'batch_number',
        'po_item_id',
        'location',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    public function packingListDifferences()
    {
        return $this->hasMany(PackingListDifference::class, 'packing_list_item_id');
    }

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    protected static function booted()
    {
        static::saving(function ($packingListItem) {
            $packingListItem->total_cost = $packingListItem->unit_cost * $packingListItem->quantity;
        });
    }
}
