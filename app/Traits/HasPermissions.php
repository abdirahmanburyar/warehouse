<?php

namespace App\Traits;

use App\Models\Permission;

trait HasPermissions
{
    /**
     * Check if the user has a specific permission.
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            return $this->hasPermission($permission);
        }

        return false;
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission($permissions): bool
    {
        return $this->hasAnyPermission($permissions);
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions($permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for the user.
     */
    public function getAllPermissions()
    {
        return $this->permissions;
    }

    /**
     * Get permissions grouped by module.
     */
    public function getPermissionsByModule()
    {
        return $this->permissions->groupBy('module');
    }

    /**
     * Check if user has permission for a specific module.
     */
    public function canAccessModule($module): bool
    {
        return $this->permissions()->where('module', $module)->exists();
    }

    /**
     * Get user's permission names as an array.
     */
    public function getPermissionNames(): array
    {
        return $this->permissions->pluck('name')->toArray();
    }

    /**
     * Check if user has view-only access.
     */
    public function isViewOnly(): bool
    {
        return $this->hasPermission('view-only-access');
    }

    /**
     * Check if user is a system manager.
     */
    public function isSystemManager(): bool
    {
        return $this->hasPermission('manager-system');
    }

    /**
     * Check if user can perform actions (not view-only).
     */
    public function canPerformActions(): bool
    {
        return !$this->isViewOnly();
    }
}
