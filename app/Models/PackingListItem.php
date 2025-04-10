<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_list_id',
        'purchase_order_item_id',
        'brand_name',
        'generic_name',
        'expiry_date',
        'batch_number',
        'unit_cost',
        'quantity',
        'total_cost'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
