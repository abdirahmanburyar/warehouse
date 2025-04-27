<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_IN_USE = 'in_use';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED = 'retired';
    const STATUS_DAMAGED = 'damaged';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_IN_USE => 'In Use',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DAMAGED => 'Damaged',
        ];
    }

    protected $fillable = [
        'name',
        'serial_number',
        'category',
        'custody',
        'quantity',
        'location',
        'purchase_date',
        'purchase_cost',
        'transfer_date',
        'notes',
        'status'
    ];

    public function custodyHistories(): HasMany
    {
        return $this->hasMany(CustodyHistory::class);
    }

    public function histories()
    {
        return $this->hasMany(AssetHistory::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($asset) {
            $trackedFields = ['status', 'transfer_date', 'quantity'];
            
            foreach ($trackedFields as $field) {
                if ($asset->isDirty($field)) {
                    $asset->custodyHistories()->create([
                        'custodian' => $asset->custody,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'status' => $asset->$field,
                        'status_notes' => "Changed {$field} from {$asset->getOriginal($field)} to {$asset->$field}",
                    ]);
                }
            }
        });
    }
}
