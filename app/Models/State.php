<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class State extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    /**
     * Get the districts for the state.
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
