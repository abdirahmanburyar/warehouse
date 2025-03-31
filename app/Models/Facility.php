<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Facility extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'user_id',
        'phone',
        'address',
        'city',
        'state',
        'facility_type',
        'has_cold_storage',
        'special_handling_capabilities',
        'is_24_hour',
        'is_active',
    ];

    protected $casts = [
        'has_cold_storage' => 'boolean',
        'is_24_hour' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
