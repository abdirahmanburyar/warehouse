<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Liquidate extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($liquidate) {
            $latestLiquidate = static::latest()->first();
            $nextId = $latestLiquidate ? intval(substr($latestLiquidate->liquidate_id, -7)) + 1 : 1;
            $liquidate->liquidate_id = str_pad($nextId, 7, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = [
        'liquidate_id',
        'product_id',
        'liquidated_by',
        'liquidated_at',
        'quantity',
        'status',
        'type',
        'barcode',
        'expire_date',
        'batch_number',
        'uom',
        'location',
        'facility',
        'warehouse',
        'unit_cost',
        'tota_cost',
        'note',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'attachments',
    ];

    /**
     * Get the product that owns the liquidate record
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who liquidated the item
     */
    public function liquidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liquidated_by');
    }

    /**
     * Get the user who approved the liquidation
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who reviewed the liquidation
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who rejected the liquidation
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
