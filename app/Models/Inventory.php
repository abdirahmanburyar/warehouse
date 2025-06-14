<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Warehouse;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'manufacturing_date',
        'expiry_date',
        'batch_number',
        'barcode',
        'location_id',
        'notes',
        'uom',
        'unit_cost',
        'total_cost',
        'is_active',
        'physical_count',
        'physical_count_difference',
        'physical_count_remarks',
        'physical_count_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
