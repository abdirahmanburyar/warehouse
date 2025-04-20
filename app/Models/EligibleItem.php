<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibleItem extends Model
{
    protected $fillable = [
        'product_id',
        'facility_type'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
