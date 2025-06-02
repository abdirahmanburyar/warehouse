<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueQuantityItem extends Model
{
    protected $fillable = [
        'product_id',
        'parent_id',
        'warehouse_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'issued_date',
        'barcode',
        'uom',
        'issued_by'
    ];

    /**
     * Get the parent report for this item.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(IssueQuantityReport::class, 'parent_id');
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse for this item.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the issuer for this item.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
