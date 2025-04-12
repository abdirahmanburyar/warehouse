<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\User;

class Warehouse extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'capacity',
        'temperature_min',
        'temperature_max',
        'humidity_min',
        'humidity_max',
        'status',
        'has_cold_storage',
        'has_hazardous_storage',
        'is_active',
        'manager_name',
        'manager_email',
        'manager_phone',
        'notes'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'capacity' => 'integer',
        'temperature_min' => 'integer',
        'temperature_max' => 'integer',
        'has_cold_storage' => 'boolean',
        'has_hazardous_storage' => 'boolean',
        'is_active' => 'boolean',
    ];
    

    /**
     * Get the users associated with the warehouse.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
