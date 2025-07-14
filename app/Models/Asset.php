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

    protected $casts = [
        'submitted_for_approval' => 'boolean',
        'submitted_at' => 'datetime',
        'acquisition_date' => 'date',
        'asset_warranty_start' => 'date',
        'asset_warranty_end' => 'date',
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

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_IN_USE => 'In Use',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DISPOSED => 'Disposed',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        ];
    }

    /**
     * Submit asset for approval
     */
    public function submitForApproval()
    {
        $this->update([
            'submitted_for_approval' => true,
            'submitted_at' => now(),
            'submitted_by' => auth()->id(),
            'status' => self::STATUS_PENDING_APPROVAL
        ]);

        // Create approval steps
        $this->createApprovalSteps([
            [
                'role_id' => 2, // asset_manager role ID
                'action' => 'verify',
                'sequence' => 1
            ],
            [
                'role_id' => 3, // finance_manager role ID
                'action' => 'approve',
                'sequence' => 2
            ]
        ]);

        return $this;
    }

    /**
     * Create automatic approvals for new assets
     */
    public function createAutomaticApprovals()
    {
        // Set status to pending approval
        $this->update([
            'status' => self::STATUS_PENDING_APPROVAL,
            'submitted_for_approval' => true,
            'submitted_at' => now(),
            'submitted_by' => auth()->id()
        ]);

        // Create approval steps
        $this->createApprovalSteps([
            [
                'role_id' => 2, // asset_manager role ID
                'action' => 'verify',
                'sequence' => 1
            ],
            [
                'role_id' => 3, // finance_manager role ID
                'action' => 'approve',
                'sequence' => 2
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
}
