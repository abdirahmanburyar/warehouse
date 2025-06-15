<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryReport extends Model
{
    const STATUS_DRAFT = 'generated';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'month_year',
        'generated_by',
        'generated_at',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'status'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryReportItem::class);
    }

    /**
     * Get all available status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Check if report can be edited
     */
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Check if report can be submitted
     */
    public function canBeSubmitted()
    {
        return $this->status === self::STATUS_DRAFT || $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if report can be reviewed/approved/rejected
     */
    public function canBeReviewed()
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
    }
}
