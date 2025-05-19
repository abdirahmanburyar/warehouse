<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disposal extends Model
{
    protected $fillable = [
        'inventory_id',
        'packing_list_id',
        'purchase_order_id',
        'quantity',
        'expired_date',
        'disposed_by',
        'disposed_at',
        'status',
        'note',
        'product_id',
    ];
}
