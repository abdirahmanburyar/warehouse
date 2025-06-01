<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuedQuantity extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'issued_date',
        'barcode',
        'issued_by'
    ];
}
