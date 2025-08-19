<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_maintenance';

    protected $fillable = [
        'asset_item_id',
        'maintenance_type',
        'description',
        'scheduled_date',
        'completed_date',
        'status',
        'cost',
        'performed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function assetItem(): BelongsTo
    {
        return $this->belongsTo(AssetItem::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_PREDICTIVE = 'predictive';

    public static function getMaintenanceTypes(): array
    {
        return [
            self::TYPE_PREVENTIVE => 'Preventive',
            self::TYPE_CORRECTIVE => 'Corrective',
            self::TYPE_EMERGENCY => 'Emergency',
            self::TYPE_PREDICTIVE => 'Predictive',
        ];
    }

    // Helper methods
    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOverdue(): bool
    {
        return $this->isScheduled() && $this->scheduled_date && $this->scheduled_date->isPast();
    }

    public function isUpcoming(): bool
    {
        return $this->isScheduled() && $this->scheduled_date && $this->scheduled_date->isFuture();
    }

    public function getDaysUntilScheduled(): int
    {
        if (!$this->scheduled_date || !$this->isScheduled()) {
            return 0;
        }
        
        return $this->scheduled_date->diffInDays(now());
    }

    public function getDaysSinceCompleted(): int
    {
        if (!$this->completed_date || !$this->isCompleted()) {
            return 0;
        }
        
        return $this->completed_date->diffInDays(now());
    }

    public function markInProgress()
    {
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
        return $this;
    }

    public function markCompleted($notes = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_date' => now(),
            'notes' => $notes,
        ]);
        return $this;
    }

    public function cancel($notes = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $notes,
        ]);
        return $this;
    }

    public function reschedule($newDate)
    {
        $this->update(['scheduled_date' => $newDate]);
        return $this;
    }

    public function getFormattedCost(): string
    {
        return $this->cost ? '$' . number_format($this->cost, 2) : 'N/A';
    }

    public function getAssetName(): string
    {
        return $this->assetItem->asset_name ?? 'Unknown Asset';
    }

    public function getAssetNumber(): string
    {
        return $this->assetItem->getAssetNumber() ?? 'Unknown';
    }

    public function getPerformerName(): string
    {
        return $this->performedBy->name ?? 'Unknown';
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('maintenance_type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('scheduled_date', '<', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('scheduled_date', '>=', now());
    }

    public function scopeByAssetItem($query, $assetItemId)
    {
        return $query->where('asset_item_id', $assetItemId);
    }

    public function scopeByPerformer($query, $performerId)
    {
        return $query->where('performed_by', $performerId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_date', [$startDate, $endDate]);
    }

    public function scopeByCostRange($query, $minCost, $maxCost)
    {
        return $query->whereBetween('cost', [$minCost, $maxCost]);
    }
}
