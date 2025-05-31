<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Inventory;
use App\Models\User;

class InventoryAdjustment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'inventory_id',
        'user_id',
        'quantity',
        'expiry_date',
        'uom',
        'product_id',
        'batch_number',
        'barcode',
        'physical_count',
        'difference',
        'remarks',
        'adjustment_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];
    
    protected $casts = [
        'adjustment_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];
    
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
