<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_list_number',
        'purchase_order_id',
        'packing_date',
        'created_by',
        'updated_by',
        'status'
    ];

    public function receivedGoodsNotes()
    {
        return $this->hasOne(ReceivedGoodsNote::class, 'packing_list_id');
    }

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

    public function purchaseOrderItems(){
        return $this->hasMany(PurchaseOrderItem::class, 'packing_list_id', 'id');
    }
}
