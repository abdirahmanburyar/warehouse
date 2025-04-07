<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseOrder;

class PoItem extends Model
{
    protected $fillable = [
        'item_code',
        'item_description',
        'quantity',
        'uom',
        'unit_cost',
        'total_cost',
        'purchase_order_id'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
