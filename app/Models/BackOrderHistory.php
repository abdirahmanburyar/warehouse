<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PackingList;
use App\Models\Product;
use App\Models\User;

class BackOrderHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'packing_list_id',
        'order_id',
        'transfer_id',
        'product_id',
        'quantity',
        'status',
        'action',
        'note',
        'performed_by'
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
