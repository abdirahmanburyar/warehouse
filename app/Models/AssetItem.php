<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AssetHistory;

class AssetItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'asset_tag',
        'asset_name',
        'serial_number',
        'asset_category_id',
        'asset_type_id',
        'assignee_id',
        'status',
        'original_value',
    ];

    protected $casts = [
        'original_value' => 'decimal:2',
    ];

    // Relationships
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Assignee::class, 'assignee_id');
    }

    public function assetHistory(): HasMany
    {
        return $this->hasMany(AssetHistory::class);
    }

    public function maintenance(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function depreciation(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    // Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED = 'retired';
    const STATUS_DISPOSED = 'disposed';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DISPOSED => 'Disposed',
        ];
    }

    // Helper methods
    public function getAssetNumber(): string
    {
        return $this->asset->asset_number ?? 'Unknown';
    }

    public function getAssetLocation(): string
    {
        return $this->asset->assetLocation->name ?? 'Unknown';
    }

    public function getSubLocation(): string
    {
        return $this->asset->subLocation->name ?? 'Unknown';
    }

    public function getRegion(): string
    {
        return $this->asset->region->name ?? 'Unknown';
    }

    public function getFundSource(): string
    {
        return $this->asset->fundSource->name ?? 'Unknown';
    }

    public function getAcquisitionDate(): string
    {
        return $this->asset->acquisition_date?->format('Y-m-d') ?? 'Unknown';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function needsMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function getCurrentValue(): float
    {
        $depreciation = $this->depreciation()->latest()->first();
        return $depreciation ? $depreciation->current_value : $this->original_value;
    }

    public function getDepreciationAmount(): float
    {
        $depreciation = $this->depreciation()->latest()->first();
        return $depreciation ? $depreciation->accumulated_depreciation : 0;
    }

    public function getMaintenanceHistory()
    {
        return $this->maintenance()->orderBy('created_at', 'desc')->get();
    }

    public function getUpcomingMaintenance()
    {
        return $this->maintenance()
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date')
            ->get();
    }

    public function getCompletedMaintenance()
    {
        return $this->maintenance()
            ->where('status', 'completed')
            ->orderBy('completed_date', 'desc')
            ->get();
    }

    public function scheduleMaintenance($type, $description, $scheduledDate, $cost = null)
    {
        return $this->maintenance()->create([
            'maintenance_type' => $type,
            'description' => $description,
            'scheduled_date' => $scheduledDate,
            'cost' => $cost,
            'status' => 'scheduled',
        ]);
    }

    public function completeMaintenance($maintenanceId, $notes = null)
    {
        $maintenance = $this->maintenance()->find($maintenanceId);
        if ($maintenance) {
            $maintenance->update([
                'status' => 'completed',
                'completed_date' => now(),
                'notes' => $notes,
            ]);
        }
        return $maintenance;
    }

    public function calculateDepreciation($method = 'straight_line', $usefulLifeYears = 5, $salvageValue = 0)
    {
        $depreciation = $this->depreciation()->create([
            'original_value' => $this->original_value,
            'salvage_value' => $salvageValue,
            'useful_life_years' => $usefulLifeYears,
            'depreciation_method' => $method,
            'current_value' => $this->original_value,
            'accumulated_depreciation' => 0,
            'depreciation_start_date' => now(),
            'last_calculated_date' => now(),
        ]);

        // Calculate initial depreciation rate
        switch ($method) {
            case 'straight_line':
                $rate = ($this->original_value - $salvageValue) / $usefulLifeYears;
                break;
            case 'declining_balance':
                $rate = ($this->original_value - $salvageValue) * 0.2; // 20% declining balance
                break;
            default:
                $rate = ($this->original_value - $salvageValue) / $usefulLifeYears;
        }

        $depreciation->update([
            'depreciation_rate' => $rate,
        ]);

        return $depreciation;
    }

    /**
     * Create a history record for this asset item
     */
    public function createHistory(array $data): AssetHistory
    {
        return AssetHistory::create([
            'asset_item_id' => $this->id,
            'action' => $data['action'] ?? 'unknown',
            'action_type' => $data['action_type'] ?? 'general',
            'old_value' => $data['old_value'] ?? null,
            'new_value' => $data['new_value'] ?? null,
            'notes' => $data['notes'] ?? '',
            'performed_by' => $data['performed_by'] ?? null,
            'performed_at' => $data['performed_at'] ?? now(),
            'approval_id' => $data['approval_id'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('asset_category_id', $categoryId);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('asset_type_id', $typeId);
    }

    public function scopeByAssignee($query, $assigneeId)
    {
        return $query->where('assignee_id', $assigneeId);
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function scopeByAsset($query, $assetId)
    {
        return $query->where('asset_id', $assetId);
    }

    public function scopeByValueRange($query, $minValue, $maxValue)
    {
        return $query->whereBetween('original_value', [$minValue, $maxValue]);
    }
}
