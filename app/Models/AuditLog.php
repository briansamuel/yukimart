<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'description',
        'tags',
        'created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was affected
     */
    public function model()
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Scope for filtering by action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by model type
     */
    public function scopeModelType($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Get formatted action name
     */
    public function getActionDisplayAttribute()
    {
        $actions = [
            'created' => 'Tạo mới',
            'updated' => 'Cập nhật',
            'deleted' => 'Xóa',
            'restored' => 'Khôi phục',
            'viewed' => 'Xem',
            'login' => 'Đăng nhập',
            'logout' => 'Đăng xuất',
            'exported' => 'Xuất dữ liệu',
            'imported' => 'Nhập dữ liệu',
            'backup' => 'Sao lưu',
            'restore' => 'Khôi phục',
        ];

        return $actions[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get formatted model name
     */
    public function getModelDisplayAttribute()
    {
        $models = [
            'App\Models\Product' => 'Sản phẩm',
            'App\Models\Order' => 'Đơn hàng',
            'App\Models\Customer' => 'Khách hàng',
            'App\Models\User' => 'Người dùng',
            'App\Models\Inventory' => 'Tồn kho',
            'App\Models\InventoryTransaction' => 'Giao dịch tồn kho',
            'App\Models\ProductCategory' => 'Danh mục sản phẩm',
            'App\Models\Supplier' => 'Nhà cung cấp',
            'App\Models\Warehouse' => 'Kho',
        ];

        return $models[$this->model_type] ?? class_basename($this->model_type);
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute()
    {
        $icons = [
            'created' => 'fa-plus-circle text-success',
            'updated' => 'fa-edit text-warning',
            'deleted' => 'fa-trash text-danger',
            'restored' => 'fa-undo text-info',
            'viewed' => 'fa-eye text-primary',
            'login' => 'fa-sign-in-alt text-success',
            'logout' => 'fa-sign-out-alt text-muted',
            'exported' => 'fa-download text-info',
            'imported' => 'fa-upload text-info',
            'backup' => 'fa-archive text-warning',
            'restore' => 'fa-history text-info',
        ];

        return $icons[$this->action] ?? 'fa-circle text-secondary';
    }

    /**
     * Get time ago format
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute()
    {
        if (empty($this->old_values) || empty($this->new_values)) {
            return null;
        }

        $changes = [];
        foreach ($this->new_values as $field => $newValue) {
            $oldValue = $this->old_values[$field] ?? null;
            if ($oldValue != $newValue) {
                $changes[] = [
                    'field' => $field,
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }

    /**
     * Create audit log entry
     */
    public static function createLog($action, $model = null, $oldValues = [], $newValues = [], $description = null)
    {
        $user = auth()->user();
        $request = request();

        return static::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'description' => $description,
        ]);
    }

    /**
     * Log model creation
     */
    public static function logCreated($model, $description = null)
    {
        return static::createLog('created', $model, [], $model->toArray(), $description);
    }

    /**
     * Log model update
     */
    public static function logUpdated($model, $oldValues, $description = null)
    {
        return static::createLog('updated', $model, $oldValues, $model->toArray(), $description);
    }

    /**
     * Log model deletion
     */
    public static function logDeleted($model, $description = null)
    {
        return static::createLog('deleted', $model, $model->toArray(), [], $description);
    }

    /**
     * Log user login
     */
    public static function logLogin($user, $description = null)
    {
        return static::createLog('login', $user, [], [], $description ?? 'Người dùng đăng nhập');
    }

    /**
     * Log user logout
     */
    public static function logLogout($user, $description = null)
    {
        return static::createLog('logout', $user, [], [], $description ?? 'Người dùng đăng xuất');
    }

    /**
     * Log custom action
     */
    public static function logAction($action, $model = null, $description = null, $data = [])
    {
        return static::createLog($action, $model, [], $data, $description);
    }

    /**
     * Get statistics
     */
    public static function getStatistics($filters = [])
    {
        $query = static::query();

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        return [
            'total_logs' => $query->count(),
            'actions_count' => $query->groupBy('action')->selectRaw('action, count(*) as count')->pluck('count', 'action'),
            'models_count' => $query->groupBy('model_type')->selectRaw('model_type, count(*) as count')->pluck('count', 'model_type'),
            'users_count' => $query->groupBy('user_id')->selectRaw('user_id, count(*) as count')->pluck('count', 'user_id'),
            'daily_activity' => $query->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
        ];
    }

    /**
     * Clean old logs
     */
    public static function cleanOldLogs($days = 90)
    {
        $cutoffDate = Carbon::now()->subDays($days);
        return static::where('created_at', '<', $cutoffDate)->delete();
    }
}
