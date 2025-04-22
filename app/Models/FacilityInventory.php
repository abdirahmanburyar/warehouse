<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacilityInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'facility_id',
        'quantity',
        'expiry_date',
        'batch_number',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
