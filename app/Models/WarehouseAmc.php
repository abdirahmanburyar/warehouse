<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseAmc extends Model
{
    protected $fillable = [
        'product_id',
        'month_year',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Helper method to get or create AMC record for a product
    public static function getOrCreate($productId, $monthYear)
    {
        return static::firstOrCreate(
            [
                'product_id' => $productId,
                'month_year' => $monthYear
            ],
            ['quantity' => 0]
        );
    }

    // Helper method to update consumption for current month
    public static function updateConsumption($productId, $quantity)
    {
        $monthYear = now()->format('Y-m');
        
        return static::updateOrCreate(
            [
                'product_id' => $productId,
                'month_year' => $monthYear
            ],
            [
                'quantity' => $quantity
            ]
        );
    }

    // Calculate average monthly consumption
    public static function calculateAMC($productId, $months = 3)
    {
        $endDate = now();
        $startDate = now()->subMonths($months);

        $consumptions = static::where('product_id', $productId)
            ->where('month_year', '>=', $startDate->format('Y-m'))
            ->where('month_year', '<=', $endDate->format('Y-m'))
            ->get();

        if ($consumptions->isEmpty()) {
            return 0;
        }

        return ceil($consumptions->avg('quantity'));
    }
}
