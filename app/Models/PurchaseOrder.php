<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',
        'total_amount',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function po_items()
    {
        return $this->hasMany(PoItem::class, 'purchase_order_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function packingLists()
    {
        return $this->hasMany(PackingList::class, 'purchase_order_id');
    }

    public function receivedGoodsNotes()
    {
        return $this->hasManyThrough(
            ReceivedGoodsNote::class,
            PackingList::class,
            'purchase_order_id', // Foreign key on packing_lists table
            'packing_list_id',   // Foreign key on received_goods_notes table
            'id',                // Local key on purchase_orders table
            'id'                 // Local key on packing_lists table
        );
    }
}
