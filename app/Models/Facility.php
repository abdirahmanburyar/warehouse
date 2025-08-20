<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{

    protected $fillable = [
        'name',
        'email',
        'user_id',
        'district',
        'handled_by',
        'region',
        'phone',
        'address',
        'facility_type',
        'has_cold_storage',
        'is_active',
    ];

    public function inventories()
    {
        return $this->hasMany(FacilityInventory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function handledby(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Get the monthly reports for this facility
     */
    public function monthlyReports(): HasMany
    {
        return $this->hasMany(FacilityMonthlyReport::class);
    }
    
}
