<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'district_id',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    /**
     * Get the district that owns the city.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
