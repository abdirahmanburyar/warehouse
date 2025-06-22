<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposal extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($disposal) {
            $latestDisposal = static::latest()->first();
            $nextId = $latestDisposal ? intval(substr($latestDisposal->disposal_id, -7)) + 1 : 1;
            $disposal->disposal_id = str_pad($nextId, 7, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = [
        'disposal_id',
        'product_id',
        'disposed_by',
        'disposed_at',
        'quantity',
        'status',
        'barcode',
        'expire_date',
        'batch_number',
        'uom',
        'attachments',
        'note',
        'type',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'location',
        'facility',
        'warehouse',
        'unit_cost',
        'tota_cost',
        'attachments',
    ];

    /**
     * Get the product that owns the disposal record
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who disposed the item
     */
    public function disposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    /**
     * Get the user who approved the disposal
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who reviewed the disposal
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who rejected the disposal
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
