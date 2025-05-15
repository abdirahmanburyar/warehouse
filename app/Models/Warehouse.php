<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;
    
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
        'postal_code',
        'manager_name',
        'manager_phone',
        'manager_email',
        'capacity',
        'status',
        'user_id',
        'special_handling_capabilities',
        'notes'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'capacity' => 'integer',
        'deleted_at' => 'datetime'
    ];
    

    /**
     * Get the users associated with the warehouse.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
