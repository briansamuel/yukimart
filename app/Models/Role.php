<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Role constants
     */
    const ADMIN = 'admin';
    const SHOP_MANAGER = 'shop_manager';
    const STAFF = 'staff';
    const PARTIME = 'partime';

    /**
     * Get all role types
     */
    public static function getRoleTypes()
    {
        return [
            self::ADMIN => 'Admin',
            self::SHOP_MANAGER => 'Shop Manager',
            self::STAFF => 'Staff',
            self::PARTIME => 'Part-time',
        ];
    }

    /**
     * Relationship with permissions
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Relationship with users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['assigned_at', 'assigned_by', 'expires_at', 'is_active'])
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
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if role has permission
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }

        if ($permission instanceof Permission) {
            return $this->permissions()->where('id', $permission->id)->exists();
        }

        return false;
    }

    /**
     * Grant permission to role
     */
    public function grantPermission($permission)
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
     * Revoke permission from role
     */
    public function revokePermission($permission)
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
     * Sync permissions for role
     */
    public function syncPermissions(array $permissions)
    {
        $permissionIds = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $permissionModel = Permission::where('name', $permission)->first();
                if ($permissionModel) {
                    $permissionIds[] = $permissionModel->id;
                }
            } elseif ($permission instanceof Permission) {
                $permissionIds[] = $permission->id;
            } elseif (is_numeric($permission)) {
                $permissionIds[] = $permission;
            }
        }

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    /**
     * Get role display name
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ?: ucfirst(str_replace('_', ' ', $this->name));
    }

    /**
     * Get role badge class
     */
    public function getBadgeClassAttribute()
    {
        $classes = [
            self::ADMIN => 'badge-light-danger',
            self::SHOP_MANAGER => 'badge-light-primary',
            self::STAFF => 'badge-light-success',
            self::PARTIME => 'badge-light-warning',
        ];

        return $classes[$this->name] ?? 'badge-light-secondary';
    }

    /**
     * Get role icon
     */
    public function getIconAttribute()
    {
        $icons = [
            self::ADMIN => 'ki-crown',
            self::SHOP_MANAGER => 'ki-shop',
            self::STAFF => 'ki-people',
            self::PARTIME => 'ki-timer',
        ];

        return $icons[$this->name] ?? 'ki-user';
    }

    /**
     * Check if role is admin
     */
    public function isAdmin()
    {
        return $this->name === self::ADMIN;
    }

    /**
     * Check if role is shop manager
     */
    public function isShopManager()
    {
        return $this->name === self::SHOP_MANAGER;
    }

    /**
     * Check if role is staff
     */
    public function isStaff()
    {
        return $this->name === self::STAFF;
    }

    /**
     * Check if role is part-time
     */
    public function isPartime()
    {
        return $this->name === self::PARTIME;
    }

    /**
     * Get permissions grouped by module
     */
    public function getPermissionsByModule()
    {
        return $this->permissions()
            ->orderBy('module')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('module');
    }

    /**
     * Get users count
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->wherePivot('is_active', true)->count();
    }

    /**
     * Get permissions count
     */
    public function getPermissionsCountAttribute()
    {
        return $this->permissions()->count();
    }
}
