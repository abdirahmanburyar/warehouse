<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseOrder;

class PoItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'item_code',
        'item_description',
        'uom',
        'quantity',
        'original_quantity',
        'received_quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'original_quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
