<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasApprovals;

class Asset extends Model
{
    use HasFactory, SoftDeletes, HasApprovals;

    protected $fillable = [
        'asset_tag',
        'asset_category_id',
        'serial_number',
        'item_description',
        'person_assigned',
        'asset_location_id',
        'sub_location_id',
        'fund_source_id',
        'region_id',
        'acquisition_date',
        'status',
        'original_value',
        'has_warranty',
        'has_documents',
        'asset_warranty_start',
        'asset_warranty_end',
        'submitted_for_approval',
        'submitted_at',
        'submitted_by'
    ];



    public function region(){
        return $this->belongsTo(Region::class);
    }
    
    public function fundSource()
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }
    
    /**
     * Get the attachments for the asset.
     */
    public function attachments()
    {
        return $this->hasMany(AssetAttachment::class);
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

    public function assetHistory()
    {
        return $this->hasMany(AssetHistory::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    const STATUS_ACTIVE = 'active';
    const STATUS_IN_USE = 'in_use';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED = 'retired';
    const STATUS_DISPOSED = 'disposed';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_IN_TRANSFER_PROCESS = 'in_transfer_process';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_IN_USE => 'In Use',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DISPOSED => 'Disposed',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_IN_TRANSFER_PROCESS => 'In Transfer Process',
        ];
    }

    /**
     * Submit asset for approval
     */
    public function submitForApproval()
    {
        // Check if approvals already exist for this asset
        if ($this->approvals()->exists()) {
            return $this; // Approvals already exist, don't create duplicates
        }

        $this->update([
            'submitted_for_approval' => true,
            'submitted_at' => now(),
            'submitted_by' => auth()->id(),
            'status' => self::STATUS_PENDING_APPROVAL
        ]);

        // Create single review step for asset approval
        $this->createApprovalSteps([
            [
                'role_id' => 1, // Use a general role ID that all users can have
                'action' => 'review',
                'sequence' => 1
            ]
        ]);

        return $this;
    }

    /**
     * Create automatic approvals for new assets
     */
    public function createAutomaticApprovals()
    {
        // Check if approvals already exist for this asset
        if ($this->approvals()->exists()) {
            return $this; // Approvals already exist, don't create duplicates
        }

        // Set status to pending approval
        $this->update([
            'status' => self::STATUS_PENDING_APPROVAL,
            'submitted_for_approval' => true,
            'submitted_at' => now(),
            'submitted_by' => auth()->id()
        ]);

        // Create single review step for asset approval
        $this->createApprovalSteps([
            [
                'role_id' => 1, // Use a general role ID that all users can have
                'action' => 'review',
                'sequence' => 1
            ]
        ]);

        return $this;
    }

    /**
     * Check if asset is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->submitted_for_approval && $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Check if asset can be approved by current user
     */
    public function canBeApprovedByCurrentUser(): bool
    {
        return $this->canApproveNextStep();
    }

    /**
     * Check if asset can be reviewed by current user
     */
    public function canBeReviewedByCurrentUser(): bool
    {
        return $this->canReviewNextStep();
    }

    /**
     * Check if asset can be approved/rejected by current user after review
     */
    public function canBeApprovedRejectedByCurrentUser(): bool
    {
        return $this->canApproveRejectNextStep();
    }

    /**
     * Get the current approval status for this asset
     */
    public function getApprovalStatus(): string
    {
        if (!$this->submitted_for_approval) {
            return 'not_submitted';
        }

        $pendingApprovals = $this->approvals()->where('status', 'pending')->count();
        $reviewedApprovals = $this->approvals()->where('status', 'reviewed')->count();
        $approvedApprovals = $this->approvals()->where('status', 'approved')->count();
        $rejectedApprovals = $this->approvals()->where('status', 'rejected')->count();

        if ($rejectedApprovals > 0) {
            return 'rejected';
        }

        if ($approvedApprovals > 0) {
            return 'approved';
        }

        if ($reviewedApprovals > 0) {
            return 'reviewed';
        }

        return 'pending';
    }

    /**
     * Clear all approvals for this asset
     */
    public function clearApprovals()
    {
        $this->approvals()->delete();
        $this->update([
            'submitted_for_approval' => false,
            'submitted_at' => null,
            'submitted_by' => null
        ]);
        
        return $this;
    }

    /**
     * Create asset history record
     */
    public function createHistoryRecord($action, $actionType, $oldValue = null, $newValue = null, $notes = null, $approvalId = null)
    {
        return $this->assetHistory()->create([
            'action' => $action,
            'action_type' => $actionType,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'notes' => $notes,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'approval_id' => $approvalId
        ]);
    }
}
