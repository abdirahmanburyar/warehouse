<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReorderLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'img',
        'product_id',
        'amc',
        'lead_time',
        'reorder_level'
    ];

    protected $casts = [
        'amc' => 'decimal:2',
        'lead_time' => 'integer',
        'reorder_level' => 'decimal:2'
    ];

    /**
     * Get the product that owns the reorder level
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate reorder level based on AMC and lead time
     */
    public function calculateReorderLevel()
    {
        $this->reorder_level = $this->amc * $this->lead_time;
        return $this->reorder_level;
    }

    /**
     * Boot method to automatically calculate reorder level before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($reorderLevel) {
            $reorderLevel->calculateReorderLevel();
        });
    }
}
