<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyQuantityReceived extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'month_year',
        'generated_by',
        'total_quantity',
    ];
    
    
    /**
     * Get the items for this monthly report.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReceivedQuantityItem::class, 'parent_id');
    }

}
