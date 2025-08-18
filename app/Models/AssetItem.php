<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'asset_number', // Sequential 4-digit number
        
        // AssetItem specific fields
        'item_name',
        'description',
        'serial_number', // Moved here - belongs to asset_items
        'model_number',
        'manufacturer',
        'quantity',
        'unit_of_measure',
        'unit_cost',
        'total_cost',
        'condition',
        'location_details',
        'expiry_date',
        'is_active',
        'notes',
        
        // Fields from Asset model (excluding acquisition_date, fund_source_id, and serial_number)
        'uuid',
        'tag_no',
        'asset_tag',
        'asset_category_id',
        'type_id',
        'serial_no',
        'item_description',
        'person_assigned',
        'asset_location_id',
        'assigned_to',
        'region_id',
        'sub_location_id',
        'has_warranty',
        'has_documents',
        'asset_warranty_start',
        'asset_warranty_end',
        'warranty_start',
        'warranty_months',
        'maintenance_interval_months',
        'last_maintenance_at',
        'purchase_date',
        'cost',
        'supplier',
        'transfer_date',
        'status',
        'original_value',
        'submitted_for_approval',
        'submitted_at',
        'submitted_by',
        'sub_location',
        'metadata',
    ];

    protected $casts = [
        // AssetItem specific casts
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        
        // Asset model casts
        'asset_warranty_start' => 'date',
        'asset_warranty_end' => 'date',
        'warranty_start' => 'date',
        'purchase_date' => 'date',
        'transfer_date' => 'date',
        'last_maintenance_at' => 'date',
        'cost' => 'decimal:2',
        'original_value' => 'decimal:2',
        'submitted_at' => 'datetime',
        'has_warranty' => 'boolean',
        'has_documents' => 'boolean',
        'submitted_for_approval' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the asset that owns this item.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the asset category for this item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    /**
     * Get the asset type for this item.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    /**
     * Get the asset location for this item.
     */
    public function assetLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    /**
     * Get the sub location for this item.
     */
    public function subLocation(): BelongsTo
    {
        return $this->belongsTo(SubLocation::class, 'sub_location_id');
    }

    /**
     * Get the region for this item.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * Get the user assigned to this item.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who submitted this item.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the condition options for asset items.
     */
    public static function getConditions(): array
    {
        return [
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor',
            'damaged' => 'Damaged',
        ];
    }

    /**
     * Get the status options for asset items.
     */
    public static function getStatuses(): array
    {
        return [
            'active' => 'Active',
            'in_transfer_process' => 'In Transfer Process',
            'in_use' => 'In Use',
            'maintenance' => 'Maintenance',
            'retired' => 'Retired',
            'disposed' => 'Disposed',
            'pending_approval' => 'Pending Approval',
        ];
    }

    /**
     * Calculate total cost based on quantity and unit cost.
     */
    public function calculateTotalCost(): void
    {
        if ($this->quantity && $this->unit_cost) {
            $this->total_cost = $this->quantity * $this->unit_cost;
        }
    }

    /**
     * Check if the item is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->isPast();
    }

    /**
     * Check if the item is expiring soon (within 30 days).
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Check if the item has warranty.
     */
    public function hasWarranty(): bool
    {
        if (!$this->has_warranty) {
            return false;
        }

        if ($this->asset_warranty_end && $this->asset_warranty_end->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the item needs maintenance.
     */
    public function needsMaintenance(): bool
    {
        if (!$this->maintenance_interval_months || $this->maintenance_interval_months <= 0) {
            return false;
        }

        if (!$this->last_maintenance_at) {
            return true;
        }

        $nextMaintenance = $this->last_maintenance_at->addMonths($this->maintenance_interval_months);
        return $nextMaintenance->isPast();
    }

    /**
     * Generate the next sequential asset number.
     * Maintains 4-digit format (0001-9999) then expands to 5+ digits (10000, 10001, etc.)
     */
    public static function generateNextAssetNumber(): string
    {
        $lastAssetItem = self::orderBy('asset_number', 'desc')->first();
        
        if (!$lastAssetItem || !$lastAssetItem->asset_number) {
            return '0001';
        }
        
        $lastNumber = (int) $lastAssetItem->asset_number;
        $nextNumber = $lastNumber + 1;
        
        // For numbers 1-9999, maintain 4-digit format with leading zeros
        if ($nextNumber <= 9999) {
            return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        // For numbers 10000 and above, return as-is (no padding needed)
        return (string) $nextNumber;
    }

    /**
     * Get the next available asset number without creating a record.
     */
    public static function getNextAssetNumber(): string
    {
        return self::generateNextAssetNumber();
    }

    /**
     * Get the formatted asset number with proper padding for display.
     * Always shows 4+ digits for consistency.
     */
    public function getFormattedAssetNumber(): string
    {
        $number = (int) $this->asset_number;
        
        // For numbers 1-9999, maintain 4-digit format
        if ($number <= 9999) {
            return str_pad($number, 4, '0', STR_PAD_LEFT);
        }
        
        // For numbers 10000+, return as-is
        return (string) $number;
    }

    /**
     * Get the current asset number as an integer.
     */
    public function getAssetNumberAsInteger(): int
    {
        return (int) $this->asset_number;
    }

    /**
     * Check if the asset number is in the 4-digit range (0001-9999).
     */
    public function isFourDigitAssetNumber(): bool
    {
        $number = (int) $this->asset_number;
        return $number >= 1 && $number <= 9999;
    }

    /**
     * Get the total count of asset items for reference.
     */
    public static function getTotalAssetItemCount(): int
    {
        return self::count();
    }

    /**
     * Boot method to automatically generate asset_number if not provided.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($assetItem) {
            if (empty($assetItem->asset_number)) {
                $assetItem->asset_number = self::generateNextAssetNumber();
            }
        });
    }

    /**
     * Scope to get only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get items by condition.
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Scope to get items by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get items expiring soon.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    /**
     * Scope to get items that need maintenance.
     */
    public function scopeNeedsMaintenance($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_maintenance_at')
              ->orWhereRaw('DATE_ADD(last_maintenance_at, INTERVAL maintenance_interval_months MONTH) <= ?', [now()]);
        });
    }

    /**
     * Scope to get items with warranty.
     */
    public function scopeWithWarranty($query)
    {
        return $query->where('has_warranty', true)
                    ->where(function ($q) {
                        $q->whereNull('asset_warranty_end')
                          ->orWhere('asset_warranty_end', '>', now());
                    });
    }
}
