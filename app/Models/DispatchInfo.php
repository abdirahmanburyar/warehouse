<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchInfo extends Model
{
    protected $fillable = [
        'order_id',
        'transfer_id',
        'driver_name',
        'no_of_cartoons',
        'driver_number',
        'plate_number',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }
}
