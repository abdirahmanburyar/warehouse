<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facility extends Model
{

    protected $fillable = [
        'name',
        'email',
        'user_id',
        'district',
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

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
