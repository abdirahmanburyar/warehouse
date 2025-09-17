<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivedQuantityItem extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quantity',
        'product_id',
        'parent_id',
        'warehouse_id',
    ];
    
    /**
     * Get the monthly report that owns this item.
     */
    public function monthlyReport()
    {
        return $this->belongsTo(MonthlyQuantityReceived::class, 'parent_id');
    }
    
    /**
     * Get the product associated with this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse for this item.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

}
