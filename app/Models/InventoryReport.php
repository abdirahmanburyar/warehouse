<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryReport extends Model
{
    protected $fillable = [
        'month_year',
        'generated_by',
        'generated_at',
        'status'
    ];

    protected $casts = [
        'generated_at' => 'datetime'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryReportItem::class);
    }

}
