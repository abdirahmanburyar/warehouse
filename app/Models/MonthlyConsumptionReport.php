<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MonthlyConsumptionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'month_year',
        'generated_by',
    ];

    protected $casts = [
        'month_year' => 'string',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MonthlyConsumptionItem::class, 'parent_id');
    }

    /**
     * Get the month and year as a Carbon instance
     */
    public function getMonthYearAttribute($value)
    {
        return Carbon::createFromFormat('Y-m', $value);
    }

    /**
     * Set the month and year from a Carbon instance or string
     */
    public function setMonthYearAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['month_year'] = $value->format('Y-m');
        } else {
            $this->attributes['month_year'] = $value;
        }
    }

    /**
     * Get the total consumption quantity for this report
     */
    public function getTotalConsumptionAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get the number of unique products in this report
     */
    public function getProductCountAttribute()
    {
        return $this->items->count();
    }

    /**
     * Scope to filter by facility
     */
    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    /**
     * Scope to filter by month and year
     */
    public function scopeForMonthYear($query, $monthYear)
    {
        return $query->where('month_year', $monthYear);
    }
}
