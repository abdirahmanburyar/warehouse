<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'liquidated_by',
        'liquidated_at',
        'status',
        'source',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'facility',
        'warehouse',
        'rejection_reason',
        'back_order_id',
        'packing_list_id',
        'order_id',
        'transfer_id',
        'inventory_adjustment_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'liquidated_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the items for this liquidation
     */
    public function items(): HasMany
    {
        return $this->hasMany(LiquidateItem::class);
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

    /**
     * Get the back order associated with this liquidation
     */
    public function backOrder(): BelongsTo
    {
        return $this->belongsTo(BackOrder::class);
    }

    /**
     * Get the approvals for this liquidation
     */
    public function approvals()
    {
        return $this->hasMany(\App\Models\Approval::class, 'model_id')->where('model', 'liquidate');
    }

    /**
     * Get the inventory adjustment associated with this liquidation
     */
    public function inventoryAdjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class);
    }
}
