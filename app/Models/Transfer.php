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
        'created_by',
        'approved_by',
        'approved_at',
        'dispatched_by',
        'dispatched_at',
        'rejected_by',
        'rejected_at',
        'quantity',
        'transfer_date',
        'status',
        'note'
    ];

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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatchedBy()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
    
    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }
}
