<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'original_quantity',
        'original_uom',
        'edited_by',
        'unit_cost',
        'total_cost',
        'uom',
    ];

    public function edited(){
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function packingLists()
    {
        return $this->hasMany(PackingList::class, 'po_id');
    }
}
