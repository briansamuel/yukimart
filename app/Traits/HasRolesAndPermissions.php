<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use App\Models\UserRole;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasRolesAndPermissions
{
    /**
     * Relationship with roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['assigned_at', 'assigned_by', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Relationship with permissions (direct)
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot(['type', 'assigned_at', 'assigned_by', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Relationship with user roles
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Relationship with user permissions
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Get active roles
     */
    public function getActiveRoles()
    {
        return $this->roles()
            ->wherePivot('is_active', true)
            ->get()
            ->filter(function ($role) {
                return is_null($role->pivot->expires_at) || $role->pivot->expires_at > now();
            });
    }

    /**
     * Get all permissions (from roles and direct)
     */
    public function getAllPermissions(): Collection
    {
        // Get permissions from roles
        $rolePermissions = $this->getActiveRoles()
            ->flatMap(function ($role) {
                return $role->permissions;
            });

        // Get direct permissions
        $directPermissions = $this->permissions()
            ->wherePivot('is_active', true)
            ->wherePivot('type', 'grant')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->get();

        // Get denied permissions
        $deniedPermissions = $this->permissions()
            ->wherePivot('is_active', true)
            ->wherePivot('type', 'deny')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->get();

        // Combine and filter out denied permissions
        $allPermissions = $rolePermissions->merge($directPermissions);
        $deniedPermissionIds = $deniedPermissions->pluck('id');

        return $allPermissions->filter(function ($permission) use ($deniedPermissionIds) {
            return !$deniedPermissionIds->contains($permission->id);
        })->unique('id');
    }

    /**
     * Check if user has role
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->getActiveRoles()->contains('name', $role);
        }

        if ($role instanceof Role) {
            return $this->getActiveRoles()->contains('id', $role->id);
        }

        return false;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->getAllPermissions()->contains('name', $permission);
        }

        if ($permission instanceof Permission) {
            return $this->getAllPermissions()->contains('id', $permission->id);
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user can access module
     */
    public function canAccess($module, $action = 'view'): bool
    {
        return $this->hasPermission("{$module}.{$action}");
    }

    /**
     * Assign role to user
     */
    public function assignRole($role, $assignedBy = null, $expiresAt = null): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role && !$this->hasRole($role)) {
            $this->roles()->attach($role->id, [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy,
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);
        }

        return $this;
    }

    /**
     * Remove role from user
     */
    public function removeRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }

        return $this;
    }

    /**
     * Sync user roles
     */
    public function syncRoles(array $roles): self
    {
        $roleIds = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $roleModel = Role::where('name', $role)->first();
                if ($roleModel) {
                    $roleIds[] = $roleModel->id;
                }
            } elseif ($role instanceof Role) {
                $roleIds[] = $role->id;
            } elseif (is_numeric($role)) {
                $roleIds[] = $role;
            }
        }

        $this->roles()->sync($roleIds);

        return $this;
    }

    /**
     * Grant permission to user
     */
    public function grantPermission($permission, $assignedBy = null, $expiresAt = null): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching([
                $permission->id => [
                    'type' => 'grant',
                    'assigned_at' => now(),
                    'assigned_by' => $assignedBy,
                    'expires_at' => $expiresAt,
                    'is_active' => true,
                ]
            ]);
        }

        return $this;
    }

    /**
     * Deny permission to user
     */
    public function denyPermission($permission, $assignedBy = null, $expiresAt = null): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching([
                $permission->id => [
                    'type' => 'deny',
                    'assigned_at' => now(),
                    'assigned_by' => $assignedBy,
                    'expires_at' => $expiresAt,
                    'is_active' => true,
                ]
            ]);
        }

        return $this;
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission($permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is shop manager
     */
    public function isShopManager(): bool
    {
        return $this->hasRole('shop_manager');
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * Check if user is part-time
     */
    public function isPartime(): bool
    {
        return $this->hasRole('partime');
    }

    /**
     * Get user's primary role
     */
    public function getPrimaryRole(): ?Role
    {
        return $this->getActiveRoles()->sortBy('sort_order')->first();
    }

    /**
     * Get user's role names
     */
    public function getRoleNames(): array
    {
        return $this->getActiveRoles()->pluck('name')->toArray();
    }

    /**
     * Get user's permission names
     */
    public function getPermissionNames(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }
}
