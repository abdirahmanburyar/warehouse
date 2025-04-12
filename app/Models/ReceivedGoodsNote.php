<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivedGoodsNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'packing_list_id',
        'rgn_number',
        'receiver_id',
        'warehouse_id',
        'receiving_notes',
        'supplier_vehicle_number',
        'supplier_driver_name',
        'supplier_driver_phone',
        'received_at',
        'status',
    ];

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

}
