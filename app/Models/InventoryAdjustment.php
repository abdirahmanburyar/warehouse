<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryAdjustment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'month_year',
        'adjustment_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason'
    ];

    public function items()
    {
        return $this->hasMany(InventoryAdjustmentItem::class, 'parent_id');
    }
    
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }   

    
    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }



}
