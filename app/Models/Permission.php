<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'module',
        'action',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Module constants
     */
    const MODULE_PAGES = 'pages';
    const MODULE_PRODUCTS = 'products';
    const MODULE_CATEGORIES = 'categories';
    const MODULE_ORDERS = 'orders';
    const MODULE_CUSTOMERS = 'customers';
    const MODULE_INVENTORY = 'inventory';
    const MODULE_TRANSACTIONS = 'transactions';
    const MODULE_SUPPLIERS = 'suppliers';
    const MODULE_BRANCHES = 'branches';
    const MODULE_BRANCH_SHOPS = 'branch_shops';
    const MODULE_USERS = 'users';
    const MODULE_ROLES = 'roles';
    const MODULE_SETTINGS = 'settings';
    const MODULE_REPORTS = 'reports';
    const MODULE_NOTIFICATIONS = 'notifications';

    /**
     * Action constants
     */
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_EDIT = 'edit';
    const ACTION_DELETE = 'delete';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';
    const ACTION_MANAGE = 'manage';

    /**
     * Get all modules
     */
    public static function getModules()
    {
        return [
            self::MODULE_PAGES => 'Pages',
            self::MODULE_PRODUCTS => 'Products',
            self::MODULE_CATEGORIES => 'Categories',
            self::MODULE_ORDERS => 'Orders',
            self::MODULE_CUSTOMERS => 'Customers',
            self::MODULE_INVENTORY => 'Inventory',
            self::MODULE_TRANSACTIONS => 'Transactions',
            self::MODULE_SUPPLIERS => 'Suppliers',
            self::MODULE_BRANCHES => 'Branches',
            self::MODULE_BRANCH_SHOPS => 'Branch Shops',
            self::MODULE_USERS => 'Users',
            self::MODULE_ROLES => 'Roles',
            self::MODULE_SETTINGS => 'Settings',
            self::MODULE_REPORTS => 'Reports',
            self::MODULE_NOTIFICATIONS => 'Notifications',
        ];
    }

    /**
     * Get all actions
     */
    public static function getActions()
    {
        return [
            self::ACTION_VIEW => 'View',
            self::ACTION_CREATE => 'Create',
            self::ACTION_EDIT => 'Edit',
            self::ACTION_DELETE => 'Delete',
            self::ACTION_EXPORT => 'Export',
            self::ACTION_IMPORT => 'Import',
            self::ACTION_MANAGE => 'Manage',
        ];
    }

    /**
     * Relationship with roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Relationship with users (direct permissions)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot(['type', 'assigned_at', 'assigned_by', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Relationship with user permissions
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Scope for active permissions
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
        return $query->orderBy('sort_order')->orderBy('module')->orderBy('action');
    }

    /**
     * Scope by module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ?: ucfirst($this->action) . ' ' . ucfirst($this->module);
    }

    /**
     * Get module display name
     */
    public function getModuleDisplayNameAttribute()
    {
        $modules = self::getModules();
        return $modules[$this->module] ?? ucfirst($this->module);
    }

    /**
     * Get action display name
     */
    public function getActionDisplayNameAttribute()
    {
        $actions = self::getActions();
        return $actions[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get permission badge class
     */
    public function getBadgeClassAttribute()
    {
        $classes = [
            self::ACTION_VIEW => 'badge-light-info',
            self::ACTION_CREATE => 'badge-light-success',
            self::ACTION_EDIT => 'badge-light-warning',
            self::ACTION_DELETE => 'badge-light-danger',
            self::ACTION_EXPORT => 'badge-light-primary',
            self::ACTION_IMPORT => 'badge-light-primary',
            self::ACTION_MANAGE => 'badge-light-dark',
        ];

        return $classes[$this->action] ?? 'badge-light-secondary';
    }

    /**
     * Get permission icon
     */
    public function getIconAttribute()
    {
        $icons = [
            self::ACTION_VIEW => 'ki-eye',
            self::ACTION_CREATE => 'ki-plus',
            self::ACTION_EDIT => 'ki-pencil',
            self::ACTION_DELETE => 'ki-trash',
            self::ACTION_EXPORT => 'ki-exit-down',
            self::ACTION_IMPORT => 'ki-entrance-left',
            self::ACTION_MANAGE => 'ki-setting-2',
        ];

        return $icons[$this->action] ?? 'ki-security-user';
    }

    /**
     * Generate permission name
     */
    public static function generateName($module, $action)
    {
        return $module . '.' . $action;
    }

    /**
     * Create permission if not exists
     */
    public static function createIfNotExists($module, $action, $displayName = null, $description = null)
    {
        $name = self::generateName($module, $action);
        
        return self::firstOrCreate(
            ['name' => $name],
            [
                'display_name' => $displayName ?: ucfirst($action) . ' ' . ucfirst($module),
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );
    }

    /**
     * Get roles count
     */
    public function getRolesCountAttribute()
    {
        return $this->roles()->count();
    }

    /**
     * Get users count (direct permissions)
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->wherePivot('is_active', true)->count();
    }

    /**
     * Check if permission is for specific module and action
     */
    public function isFor($module, $action = null)
    {
        if ($action) {
            return $this->module === $module && $this->action === $action;
        }
        
        return $this->module === $module;
    }
}
