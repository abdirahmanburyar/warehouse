<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Warehouse;
use App\Models\Facility;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\User;

class Transfer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'transferID',
        'from_warehouse_id',
        'to_warehouse_id',
        'from_facility_id',
        'to_facility_id',
        'user_id',  
        'status',
        'expected_date',
        'dispatched_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'delivered_by',
        'received_by',
        'processed_by',
        'dispatched_at',
        'delivered_at',
        'received_at',
        'reviewed_by',
        'reviewed_at',
        'processed_at',
    ];

    public static function generateTransferId()
    {
        $latestTransfer = self::latest()->first();
        $latestId = $latestTransfer ? (int) $latestTransfer->transferID : 0;
        $nextId = $latestId + 1;

        // Determine the minimum length based on the latest ID's length, default to 4
        $minLength = max(strlen((string)$latestId), 4);

        // Return zero-padded ID dynamically
        return str_pad($nextId, $minLength, '0', STR_PAD_LEFT);
    }
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

     public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }
    
    public function fromFacility()
    {
        return $this->belongsTo(Facility::class, 'from_facility_id');
    }

    public function toFacility()
    {
        return $this->belongsTo(Facility::class, 'to_facility_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }
}
