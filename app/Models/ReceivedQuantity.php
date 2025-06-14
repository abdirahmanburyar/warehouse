<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivedQuantity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quantity',
        'received_by',
        'received_at',
        'product_id',
        'transfer_id',
        'packing_list_id',
        'expiry_date',
        'uom',
        'barcode',
        'batch_number',
        'unit_cost',
        'total_cost',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    /**
     * Get the user who received the quantity.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the transfer associated with the received quantity.
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    /**
     * Get the packing list associated with the received quantity.
     */
    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class);
    }
}
