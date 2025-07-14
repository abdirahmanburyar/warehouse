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
            ->where('status', '!=', 'approved')
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
            ->where('status', 'pending')
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
}
