<?php

namespace App\Traits;

use App\Models\AssetApproval;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasApprovals
{
    /**
     * Get all approvals for this model.
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(AssetApproval::class, 'approvable');
    }

    /**
     * Get pending approvals for this model.
     */
    public function pendingApprovals()
    {
        return $this->approvals()->pending();
    }

    /**
     * Check if all required approvals are completed.
     */
    public function isFullyApproved(): bool
    {
        return $this->approvals()
            ->whereNotIn('status', ['approved', 'rejected'])
            ->doesntExist();
    }

    /**
     * Create approval steps for this model.
     */
    public function createApprovalSteps(array $steps)
    {
        foreach ($steps as $step) {
            $this->approvals()->create([
                'role_id' => $step['role_id'],
                'action' => $step['action'],
                'sequence' => $step['sequence'] ?? 1,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);
        }
    }

    /**
     * Get the next pending approval step.
     */
    public function getNextApprovalStep()
    {
        return $this->approvals()
            ->whereIn('status', ['pending', 'reviewed'])
            ->orderBy('sequence')
            ->first();
    }

    /**
     * Check if the current user can approve the next step.
     */
    public function canApproveNextStep(): bool
    {
        $nextStep = $this->getNextApprovalStep();
        if (!$nextStep) {
            return false;
        }

        return auth()->user()->hasRole($nextStep->role->name);
    }

    /**
     * Check if the current user can review the next step.
     */
    public function canReviewNextStep(): bool
    {
        $nextStep = $this->getNextApprovalStep();
        if (!$nextStep || $nextStep->action !== 'review' || $nextStep->status !== 'pending') {
            return false;
        }

        return auth()->user()->hasRole($nextStep->role->name);
    }

    /**
     * Check if the current user can approve/reject the reviewed step.
     */
    public function canApproveRejectNextStep(): bool
    {
        $nextStep = $this->getNextApprovalStep();
        if (!$nextStep || $nextStep->action !== 'review' || $nextStep->status !== 'reviewed') {
            return false;
        }

        return auth()->user()->hasRole($nextStep->role->name);
    }
}
