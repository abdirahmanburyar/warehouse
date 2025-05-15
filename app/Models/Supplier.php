<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'notes',
        'status',
    ];


    /**
     * Get the supplies for the supplier.
     */
    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }
}
