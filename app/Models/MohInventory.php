<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MohInventory extends Model
{
    protected $fillable = [
        'uuid',
        'date',
        'reviewed_at',
        'reviewed_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($mohInventory) {
            if (empty($mohInventory->uuid)) {
                $mohInventory->uuid = 'MOH-' . strtoupper(uniqid());
            }
        });
    }


    /**
     * Get the user who reviewed the MOH inventory.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who approved the MOH inventory.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected the MOH inventory.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the MOH inventory items for the MOH inventory.
     */
    public function mohInventoryItems(): HasMany
    {
        return $this->hasMany(MohInventoryItem::class);
    }

    /**
     * Get the status attribute based on approval/rejection state.
     */
    public function getStatusAttribute(): string
    {
        if ($this->rejected_at) {
            return 'rejected';
        }
        
        if ($this->approved_at) {
            return 'approved';
        }
        
        if ($this->reviewed_at) {
            return 'reviewed';
        }
        
        return 'pending';
    }
}
