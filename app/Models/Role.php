<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
        'is_active',
        'sort_order',
        'settings',
        'tenant_id',
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
     * NOTE: Commented out to use Spatie's permissions() relationship
     * which uses 'role_has_permissions' table instead of 'role_permissions'
     */
    // public function permissions(): BelongsToMany
    // {
    //     return $this->belongsToMany(Permission::class, 'role_permissions')
    //         ->withTimestamps();
    // }

    /**
     * Relationship with users
     * NOTE: Commented out to use Spatie's users() relationship
     * which uses 'model_has_roles' table instead of 'user_roles'
     */
    // public function users(): BelongsToMany
    // {
    //     return $this->belongsToMany(User::class, 'user_roles')
    //         ->withPivot(['assigned_at', 'assigned_by', 'expires_at', 'is_active'])
    //         ->withTimestamps();
    // }

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

    // syncPermissions method is inherited from Spatie\Permission\Models\Role

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
        // Spatie's users() relationship uses model_has_roles table
        // which doesn't have is_active column
        return $this->users()->count();
    }

    /**
     * Get permissions count
     */
    public function getPermissionsCountAttribute()
    {
        return $this->permissions()->count();
    }
}
