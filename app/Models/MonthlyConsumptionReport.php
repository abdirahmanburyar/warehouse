<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyConsumptionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'month_year',
        'generated_by',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MonthlyConsumptionItem::class, 'parent_id');
    }
}
