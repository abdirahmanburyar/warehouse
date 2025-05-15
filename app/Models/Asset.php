<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_tag',
        'asset_category_id',
        'serial_number',
        'item_description',
        'person_assigned',
        'asset_location_id',
        'sub_location_id',
        'acquisition_date',
        'status',
        'original_value',
        'source_agency'
    ];

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function subLocation()
    {
        return $this->belongsTo(SubLocation::class, 'sub_location_id');
    }

    public function history()
    {
        return $this->hasMany(CustodyHistory::class);
    }


    const STATUS_ACTIVE = 'active';
    const STATUS_IN_USE = 'in_use';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED = 'retired';
    const STATUS_DISPOSED = 'disposed';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_IN_USE => 'In Use',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DISPOSED => 'Disposed',
        ];
    }
}
