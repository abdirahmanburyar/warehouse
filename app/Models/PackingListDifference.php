<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListDifference extends Model
{
    use HasFactory;

    protected $table = 'packing_list_differences';

    protected $fillable = [
        'packing_listitem_id',
        'order_item_id',
        'transfer_item_id',
        'back_order_id',
        'product_id',
        'quantity',
        'finalized',
        'status',
        'notes'
    ];

    public function packingListItem()
    {
        return $this->belongsTo(PackingListItem::class, 'packing_listitem_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function transferItem()
    {
        return $this->belongsTo(TransferItem::class, 'transfer_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function backOrder()
    {
        return $this->belongsTo(BackOrder::class);
    }
}
