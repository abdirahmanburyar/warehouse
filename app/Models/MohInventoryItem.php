<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MohInventoryItem extends Model
{
    protected $fillable = [
        'moh_inventory_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'expiry_date',
        'batch_number',
        'barcode',
        'location',
        'notes',
        'uom',
        'source',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'unit_cost' => 'double',
        'total_cost' => 'double',
    ];

    /**
     * Get the MOH inventory that owns the MOH inventory item.
     */
    public function mohInventory(): BelongsTo
    {
        return $this->belongsTo(MohInventory::class);
    }

    /**
     * Get the product that owns the MOH inventory item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the MOH inventory item.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
