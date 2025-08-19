<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AssetHistory;
use App\Models\User;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_number',
        'acquisition_date',
        'fund_source_id',
        'region_id',
        'asset_location_id',
        'sub_location_id',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
    ];

    // Relationships
    public function assetItems(): HasMany
    {
        return $this->hasMany(AssetItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function assetLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function subLocation(): BelongsTo
    {
        return $this->belongsTo(SubLocation::class, 'sub_location_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // Helper methods
    public function getTotalItemCount(): int
    {
        return $this->assetItems()->count();
    }

    public function getActiveItemCount(): int
    {
        return $this->assetItems()->where('status', 'active')->count();
    }

    public function getTotalValue(): float
    {
        return $this->assetItems()->sum('original_value');
    }

    public function getAverageItemValue(): float
    {
        $itemCount = $this->getTotalItemCount();
        return $itemCount > 0 ? $this->getTotalValue() / $itemCount : 0;
    }

    public function hasActiveItems(): bool
    {
        return $this->assetItems()->where('status', 'active')->exists();
    }

    public function hasItemsNeedingMaintenance(): bool
    {
        return $this->assetItems()->where('status', 'maintenance')->exists();
    }

    public function getMaintenanceItems()
    {
        return $this->assetItems()->where('status', 'maintenance')->get();
    }

    public function getDocumentsByType($type = null)
    {
        $query = $this->documents();
        if ($type) {
            $query->where('document_type', $type);
        }
        return $query->get();
    }

    public function generateAssetNumber(): string
    {
        $prefix = 'ASSET';
        $lastAsset = self::where('asset_number', 'like', $prefix . '-%')
            ->orderBy('asset_number', 'desc')
            ->first();

        if (!$lastAsset) {
            return $prefix . '-001';
        }

        $lastNumber = (int) str_replace($prefix . '-', '', $lastAsset->asset_number);
        $nextNumber = $lastNumber + 1;
        
        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create a history record for this asset
     */
    public function createHistory(array $data): AssetHistory
    {
        return AssetHistory::create([
            'asset_id' => $this->id,
            'action' => $data['action'] ?? 'unknown',
            'action_type' => $data['action_type'] ?? 'general',
            'old_value' => $data['old_value'] ?? null,
            'new_value' => $data['new_value'] ?? null,
            'notes' => $data['notes'] ?? '',
            'performed_by' => $data['performed_by'] ?? auth()->id(),
            'performed_at' => $data['performed_at'] ?? now(),
            'approval_id' => $data['approval_id'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
        ]);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($asset) {
            if (empty($asset->asset_number)) {
                $asset->asset_number = $asset->generateAssetNumber();
            }
        });
    }
}
