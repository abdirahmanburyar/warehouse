<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueQuantityReport extends Model
{
    protected $fillable = [
        'month_year',
        'total_quantity',
        'generated_by'
    ];

    /**
     * Get the items for this report.
     */
    public function items(): HasMany
    {
        return $this->hasMany(IssueQuantityItem::class, 'parent_id');
    }
}
