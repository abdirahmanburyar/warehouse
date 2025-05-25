<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Liquidate extends Model
{
    protected $fillable = [
        'product_id',
        'purchase_order_id',
        'packing_list_id',
        'inventory_id',
        'liquidated_by',
        'liquidated_at',
        'quantity',
        'status',
        'note',
        'approved_by',
        'approved_at',
    ];

    /**
     * Get the product that owns the liquidate record
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the purchase order that owns the liquidate record
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the packing list that owns the liquidate record
     */
    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class);
    }

    /**
     * Get the inventory that owns the liquidate record
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the user who liquidated the item
     */
    public function liquidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liquidated_by');
    }

    /**
     * Get the user who approved the liquidation
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
