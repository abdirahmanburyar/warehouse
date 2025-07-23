<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'action',
        'action_type', // 'status_change', 'transfer', 'retirement', 'approval'
        'old_value',
        'new_value',
        'notes',
        'performed_by',
        'performed_at',
        'approval_id', // Reference to the approval that triggered this action
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    /**
     * Get the asset that this history belongs to.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the approval that triggered this action.
     */
    public function approval()
    {
        return $this->belongsTo(AssetApproval::class);
    }

    /**
     * Scope a query to only include status changes.
     */
    public function scopeStatusChanges($query)
    {
        return $query->where('action_type', 'status_change');
    }

    /**
     * Scope a query to only include transfers.
     */
    public function scopeTransfers($query)
    {
        return $query->where('action_type', 'transfer');
    }

    /**
     * Scope a query to only include retirements.
     */
    public function scopeRetirements($query)
    {
        return $query->where('action_type', 'retirement');
    }

    /**
     * Scope a query to only include approvals.
     */
    public function scopeApprovals($query)
    {
        return $query->where('action_type', 'approval');
    }
}
