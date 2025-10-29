<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class TenantActivityLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_activity_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
        'method',
        'url',
        'properties',
        'old_values',
        'new_values'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'entity_id' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'action_badge',
        'time_ago',
        'entity_name',
        'changes_summary'
    ];

    /**
     * Action constants
     */
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';
    const ACTION_BACKUP = 'backup';
    const ACTION_RESTORE = 'restore';
    const ACTION_INVITE_USER = 'invite_user';
    const ACTION_REMOVE_USER = 'remove_user';
    const ACTION_CHANGE_SETTINGS = 'change_settings';

    /**
     * Get all actions
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_LOGIN => 'Đăng nhập',
            self::ACTION_LOGOUT => 'Đăng xuất',
            self::ACTION_CREATE => 'Tạo mới',
            self::ACTION_UPDATE => 'Cập nhật',
            self::ACTION_DELETE => 'Xóa',
            self::ACTION_VIEW => 'Xem',
            self::ACTION_EXPORT => 'Xuất dữ liệu',
            self::ACTION_IMPORT => 'Nhập dữ liệu',
            self::ACTION_BACKUP => 'Sao lưu',
            self::ACTION_RESTORE => 'Khôi phục',
            self::ACTION_INVITE_USER => 'Mời người dùng',
            self::ACTION_REMOVE_USER => 'Xóa người dùng',
            self::ACTION_CHANGE_SETTINGS => 'Thay đổi cài đặt',
        ];
    }

    /**
     * Relationship with tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relationship with user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action badge
     */
    protected function actionBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getActionBadge()
        );
    }

    /**
     * Get time ago
     */
    protected function timeAgo(): Attribute
    {
        return new Attribute(
            get: fn() => $this->created_at->diffForHumans()
        );
    }

    /**
     * Get entity name
     */
    protected function entityName(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getEntityName()
        );
    }

    /**
     * Get changes summary
     */
    protected function changesSummary(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getChangesSummary()
        );
    }

    /**
     * Get action badge HTML
     */
    private function getActionBadge(): string
    {
        $badges = [
            self::ACTION_LOGIN => '<span class="badge badge-success">Đăng nhập</span>',
            self::ACTION_LOGOUT => '<span class="badge badge-secondary">Đăng xuất</span>',
            self::ACTION_CREATE => '<span class="badge badge-primary">Tạo mới</span>',
            self::ACTION_UPDATE => '<span class="badge badge-warning">Cập nhật</span>',
            self::ACTION_DELETE => '<span class="badge badge-danger">Xóa</span>',
            self::ACTION_VIEW => '<span class="badge badge-info">Xem</span>',
            self::ACTION_EXPORT => '<span class="badge badge-dark">Xuất</span>',
            self::ACTION_IMPORT => '<span class="badge badge-dark">Nhập</span>',
            self::ACTION_BACKUP => '<span class="badge badge-secondary">Sao lưu</span>',
            self::ACTION_RESTORE => '<span class="badge badge-warning">Khôi phục</span>',
            self::ACTION_INVITE_USER => '<span class="badge badge-info">Mời</span>',
            self::ACTION_REMOVE_USER => '<span class="badge badge-danger">Xóa user</span>',
            self::ACTION_CHANGE_SETTINGS => '<span class="badge badge-warning">Cài đặt</span>',
        ];

        return $badges[$this->action] ?? '<span class="badge badge-light">' . ucfirst($this->action) . '</span>';
    }

    /**
     * Get entity name
     */
    private function getEntityName(): string
    {
        if (!$this->entity_type) {
            return '';
        }

        $entityNames = [
            'App\Models\User' => 'Người dùng',
            'App\Models\Product' => 'Sản phẩm',
            'App\Models\Order' => 'Đơn hàng',
            'App\Models\Invoice' => 'Hóa đơn',
            'App\Models\Customer' => 'Khách hàng',
            'App\Models\Supplier' => 'Nhà cung cấp',
            'App\Models\Category' => 'Danh mục',
            'App\Models\BranchShop' => 'Chi nhánh',
            'App\Models\Tenant' => 'Tenant',
            'App\Models\TenantSetting' => 'Cài đặt',
        ];

        $className = class_basename($this->entity_type);
        return $entityNames[$this->entity_type] ?? $className;
    }

    /**
     * Get changes summary
     */
    private function getChangesSummary(): string
    {
        if (!$this->old_values || !$this->new_values) {
            return '';
        }

        $changes = [];
        $oldValues = $this->old_values;
        $newValues = $this->new_values;

        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[] = "{$field}: {$oldValue} → {$newValue}";
            }
        }

        return implode(', ', array_slice($changes, 0, 3)) . (count($changes) > 3 ? '...' : '');
    }

    /**
     * Static helper methods
     */

    /**
     * Log activity
     */
    public static function logActivity(
        string $action,
        ?string $description = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $properties = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return static::create([
            'tenant_id' => static::getCurrentTenantId(),
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'method' => request()->method(),
            'url' => request()->url(),
            'properties' => $properties,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    /**
     * Log login activity
     */
    public static function logLogin(User $user): self
    {
        return static::logActivity(
            self::ACTION_LOGIN,
            "Người dùng {$user->username} đăng nhập",
            User::class,
            $user->id
        );
    }

    /**
     * Log logout activity
     */
    public static function logLogout(User $user): self
    {
        return static::logActivity(
            self::ACTION_LOGOUT,
            "Người dùng {$user->username} đăng xuất",
            User::class,
            $user->id
        );
    }

    /**
     * Log model changes
     */
    public static function logModelChanges(Model $model, string $action): self
    {
        $entityName = class_basename($model);
        $description = match($action) {
            self::ACTION_CREATE => "Tạo mới {$entityName}",
            self::ACTION_UPDATE => "Cập nhật {$entityName}",
            self::ACTION_DELETE => "Xóa {$entityName}",
            default => "{$action} {$entityName}"
        };

        $oldValues = null;
        $newValues = null;

        if ($action === self::ACTION_UPDATE && $model->isDirty()) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getDirty();
        }

        return static::logActivity(
            $action,
            $description,
            get_class($model),
            $model->id,
            null,
            $oldValues,
            $newValues
        );
    }

    /**
     * Get current tenant ID
     */
    private static function getCurrentTenantId(): ?int
    {
        if (app()->bound('current_tenant')) {
            $tenant = app('current_tenant');
            return is_object($tenant) ? $tenant->id : $tenant;
        }

        if (auth()->check() && isset(auth()->user()->tenant_id)) {
            return auth()->user()->tenant_id;
        }

        return session('current_tenant_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope by entity
     */
    public function scopeByEntity($query, string $entityType, ?int $entityId = null)
    {
        $query = $query->where('entity_type', $entityType);
        
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    /**
     * Scope by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope by date range
     */
    public function scopeByDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope for important actions
     */
    public function scopeImportant($query)
    {
        return $query->whereIn('action', [
            self::ACTION_LOGIN,
            self::ACTION_DELETE,
            self::ACTION_INVITE_USER,
            self::ACTION_REMOVE_USER,
            self::ACTION_CHANGE_SETTINGS
        ]);
    }
}
