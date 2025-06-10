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
        'status',
        'ref_no',
        'pk_date',
        'confirmed_at',
        'confirmed_by',
        'reviewed_at',
        'reviewed_by',
        'approved_at',
        'approved_by',
    ];
    public function items(){
        return $this->hasMany(PackingListItem::class, 'packing_list_id');
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function po_item()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_id');
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
