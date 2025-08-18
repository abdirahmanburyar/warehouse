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
        'uuid',
        'tag_no',
        'name',
        'asset_tag',
        'asset_category_id',
        'type_id',
        'serial_number',
        'asset_location_id',
        'assignee_id',
        'fund_source_id',
        'region_id',
        'sub_location_id',
        'sub_location',
        'acquisition_date',
        'purchase_date',
        'cost',
        'supplier',
        'transfer_date',
        'status',
        'original_value',
        'has_warranty',
        'has_documents',
        'asset_warranty_start',
        'asset_warranty_end',
        'warranty_start',
        'warranty_months',
        'maintenance_interval_months',
        'last_maintenance_at',
        'metadata',
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

    public function assetLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Assignee::class, 'assignee_id');
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

    public function subLocation(): BelongsTo
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

    public function approvals()
    {
        return $this->morphMany(AssetApproval::class, 'approvable');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the asset items for this asset.
     */
    public function assetItems()
    {
        return $this->hasMany(AssetItem::class);
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

        // Create single approval step (no roles)
        $this->createApprovalSteps([
            [
                'action' => 'approve',
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

        // Create single approval step (no roles)
        $this->createApprovalSteps([
            [
                'action' => 'approve',
                'sequence' => 1
            ]
        ]);

        return $this;
    }

    /**
     * Create approval steps for this asset
     */
    public function createApprovalSteps(array $steps)
    {
        foreach ($steps as $step) {
            $this->approvals()->create([
                'role_id' => $step['role_id'],
                'action' => $step['action'],
                'sequence' => $step['sequence'],
                'status' => 'pending',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ]);
        }

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

    /**
     * Create history record for status change
     */
    public function createStatusChangeHistory($oldStatus, $newStatus, $notes = null, $approvalId = null)
    {
        return $this->createHistoryRecord(
            'status_changed',
            'status_change',
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $notes,
            $approvalId
        );
    }

    /**
     * Create history record for custody change
     */
    public function createCustodyChangeHistory($oldCustodian, $newCustodian, $notes = null, $approvalId = null)
    {
        // Resolve IDs when possible
        $oldAssigneeId = $oldCustodian instanceof Assignee ? $oldCustodian->id : (is_numeric($oldCustodian) ? $oldCustodian : optional(Assignee::where('name', $oldCustodian)->first())->id);
        $newAssigneeId = $newCustodian instanceof Assignee ? $newCustodian->id : (is_numeric($newCustodian) ? $newCustodian : optional(Assignee::where('name', $newCustodian)->first())->id);

        $history = $this->createHistoryRecord(
            'custody_changed',
            'transfer',
            ['assignee_id' => $oldAssigneeId, 'person_assigned' => $oldCustodian],
            ['assignee_id' => $newAssigneeId, 'person_assigned' => $newCustodian],
            $notes,
            $approvalId
        );

        // Also set assignee_id on history for quick FK lookup
        if ($newAssigneeId) {
            $history->assignee_id = $newAssigneeId;
            $history->save();
        }

        return $history;
    }

    /**
     * Get recent history records
     */
    public function getRecentHistory($limit = 10)
    {
        return $this->assetHistory()
            ->with(['performer', 'approval'])
            ->orderBy('performed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get history by action type
     */
    public function getHistoryByType($actionType, $limit = null)
    {
        $query = $this->assetHistory()
            ->with(['performer', 'approval'])
            ->where('action_type', $actionType)
            ->orderBy('performed_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Check if current user can approve the next step
     */
    public function canApproveNextStep(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $nextStep = $this->getNextApprovalStep();
        if (!$nextStep) return false;

        // Check if user has the required permission
        if ($nextStep->action === 'review') {
            return $user->hasPermission('asset-review');
        } elseif ($nextStep->action === 'approve') {
            return $user->hasPermission('asset-approve');
        }

        return false;
    }

    /**
     * Check if current user can review the next step
     */
    public function canReviewNextStep(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $nextStep = $this->getNextApprovalStep();
        if (!$nextStep) return false;

        // Can only review if step is pending and action is review
        if ($nextStep->status !== 'pending' || $nextStep->action !== 'review') {
            return false;
        }

        // Check if user has the required permission
        return $user->hasPermission('asset-review');
    }

    /**
     * Check if current user can approve/reject after review
     */
    public function canApproveRejectNextStep(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $nextStep = $this->getNextApprovalStep();
        if (!$nextStep) return false;

        // Can only approve/reject if step is reviewed and action is review
        if ($nextStep->status !== 'reviewed' || $nextStep->action !== 'review') {
            return false;
        }

        // Check if user has the required permission
        return $user->hasPermission('asset-approve') || $user->hasPermission('asset-reject');
    }

    /**
     * Get the next approval step that needs attention
     */
    public function getNextApprovalStep()
    {
        return $this->approvals()
            ->with(['approver', 'reviewer'])
            ->whereIn('status', ['pending', 'reviewed'])
            ->orderBy('sequence')
            ->first();
    }

    /**
     * Get all approval steps for this asset
     */
    public function getAllApprovalSteps()
    {
        return $this->approvals()
            ->with(['approver', 'reviewer'])
            ->orderBy('sequence')
            ->get();
    }
}
