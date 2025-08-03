<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'warehouse_id',
        'facility_id',
        'password',
        'title',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permission_updated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the warehouse that the user belongs to.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the trusted devices for the user.
     */
    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class);
    }

    /**
     * Get the permissions for the user.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
                    ->withTimestamps();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission)
    {
        // Admin users have all permissions
        if ($this->isAdmin()) {
            return true;
        }

        // Manager System permission grants full access
        if ($this->permissions()->where('name', 'manager-system')->exists()) {
            return true;
        }

        // Regular permission check
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }

        if (is_object($permission)) {
            return $this->permissions()->where('id', $permission->id)->exists();
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission($permissions)
    {
        // Admin users have all permissions
        if ($this->isAdmin()) {
            return true;
        }

        // Manager System permission grants full access
        if ($this->permissions()->where('name', 'manager-system')->exists()) {
            return true;
        }

        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        return $this->permissions()->whereIn('name', $permissions)->exists();
    }

    /**
     * Assign a permission to the user.
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission && !$this->hasPermission($permission)) {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    /**
     * Remove a permission from the user.
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission && $this->hasPermission($permission)) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }

    /**
     * Get all permissions for the user.
     * This method provides compatibility with Spatie package.
     */
    public function getAllPermissions()
    {
        return $this->permissions;
    }

    /**
     * Check if user has a specific permission (Spatie compatibility).
     */
    public function hasPermissionTo($permission)
    {
        return $this->hasPermission($permission);
    }



    /**
     * Check if user is a system administrator.
     */
    public function isAdmin()
    {
        // Check if user has admin email or username
        return in_array($this->email, ['admin@warehouse.com', 'admin@admin.com']) || 
               in_array($this->username, ['admin', 'administrator']);
    }
}
