<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackOrder extends Model
{
    protected $fillable = [
        'back_order_number',
        'packing_list_id',
        'back_order_date',
        'total_items',
        'total_quantity',
        'status',
        'notes',
        'source_type',
        'attach_documents',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'back_order_date' => 'date',
        'total_items' => 'integer',
        'total_quantity' => 'integer',
        'attach_documents' => 'array',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class);
    }

    public function differences(): HasMany
    {
        return $this->hasMany(PackingListDifference::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(BackOrderHistory::class);
    }

    /**
     * Boot method to auto-generate back order number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->back_order_number = self::generateBackOrderNumber();
        });
    }

    /**
     * Generate auto-incrementing back order number
     */
    private static function generateBackOrderNumber()
    {
        $year = now()->year;
        $lastBackOrder = self::where('back_order_number', 'like', "BO-{$year}-%")
            ->orderBy('back_order_number', 'desc')
            ->first();
        
        if ($lastBackOrder) {
            $lastNumber = (int) substr($lastBackOrder->back_order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "BO-{$year}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update totals based on differences
     */
    public function updateTotals()
    {
        $this->update([
            'total_items' => $this->differences()->count(),
            'total_quantity' => $this->differences()->sum('quantity')
        ]);
    }
}
