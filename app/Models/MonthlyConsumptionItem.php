<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyConsumptionItem extends Model
{
    protected $fillable = [
        'parent_id',
        'product_id',
        'batch_number',
        'uom',
        'dispense_date',
        'expiry_date',
        'quantity',
    ];

    public function monthlyConsumptionReport()
    {
        return $this->belongsTo(MonthlyConsumptionReport::class, 'parent_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
