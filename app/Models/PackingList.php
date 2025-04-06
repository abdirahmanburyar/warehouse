<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'packing_list_number',
        'purchase_order_id',
        'packing_date',
        'warehouse_name',
        'location',
        'notes',
        'status',
        'total_amount',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'packing_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PackingListItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
