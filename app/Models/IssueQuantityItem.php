<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueQuantityItem extends Model
{
    protected $fillable = [
        'product_id',
        'parent_id',
        'quantity',
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
}
