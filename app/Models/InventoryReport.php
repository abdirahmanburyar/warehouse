<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryReport extends Model
{
    protected $fillable = [
        'month_year',
        'generated_by',
        'generated_at'
    ];

    protected $casts = [
        'generated_at' => 'datetime'
    ];

    /**
     * Get all items in this inventory report
     */
    public function items(): HasMany
    {
        return $this->hasMany(InventoryReportItem::class);
    }
}
