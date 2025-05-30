<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvarageMonthlyconsumption extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monthly_consumptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'facility_id',
        'product_id',
        'amc',
        'month_year',
        'quantity'
    ];

    /**
     * Get the facility that owns the consumption record.
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the product that owns the consumption record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to filter by month and year.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $monthYear Format: YYYY-MM
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMonthYear($query, $monthYear)
    {
        return $query->where('month_year', $monthYear);
    }

    /**
     * Scope a query to filter by facility.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $facilityId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    /**
     * Scope a query to filter by product.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Calculate the average consumption for a product at a facility over a specified number of months.
     *
     * @param int $facilityId
     * @param int $productId
     * @param int $months Number of months to include in the average
     * @return float|null
     */
    public static function calculateAverage($facilityId, $productId, $months = 3)
    {
        $records = self::where('facility_id', $facilityId)
            ->where('product_id', $productId)
            ->orderBy('month_year', 'desc')
            ->take($months)
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        return $records->avg('quantity');
    }
}
