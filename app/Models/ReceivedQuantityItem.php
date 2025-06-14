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
        'parent_id',
        'received_by',
        'received_at',
        'transfer_id',
        'product_id',
        'packing_list_id',
        'expiry_date',
        'uom',
        'barcode',
        'batch_number',
        'unit_cost',
        'total_cost',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'received_at' => 'datetime',
        'expiry_date' => 'date',
        'quantity' => 'integer',
    ];
    
    /**
     * Get the monthly report that owns this item.
     */
    public function monthlyReport()
    {
        return $this->belongsTo(MonthlyQuantityReceived::class, 'parent_id');
    }
    
    /**
     * Get the user who received this item.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
    
    /**
     * Get the transfer associated with this item.
     */
    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }
    
    /**
     * Get the product associated with this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the packing list associated with this item.
     */
    public function packingList()
    {
        return $this->belongsTo(PackingList::class);
    }
}
