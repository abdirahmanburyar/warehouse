<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Warehouse;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reorder_level',
        'manufacturing_date',
        'expiry_date',
        'batch_number',
        'location',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the product that owns the inventory.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the inventory.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
