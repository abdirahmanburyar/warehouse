<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MohInventory extends Model
{
    protected $fillable = [
        'uuid',
        'reviewed_at',
        'reviewed_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
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
     * Get the MOH inventory items for the MOH inventory.
     */
    public function mohInventoryItems(): HasMany
    {
        return $this->hasMany(MohInventoryItem::class);
    }
}
