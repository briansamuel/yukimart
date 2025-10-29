<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'domain',
        'email',
        'phone',
        'address',
        'tax_number',
        'business_type',
        'industry',
        'employee_count',
        'status',
        'plan_type',
        'trial_ends_at',
        'subscription_starts_at',
        'subscription_ends_at',
        'max_users',
        'max_branch_shops',
        'max_products',
        'storage_limit',
        'api_rate_limit',
        'current_users',
        'current_branch_shops',
        'current_products',
        'current_storage_used',
        'settings',
        'features',
        'timezone',
        'currency',
        'language',
        'logo_url',
        'favicon_url',
        'theme_settings',
        'custom_css',
        'database_name',
        'integration_settings',
        'notification_settings',
        'monthly_fee',
        'setup_fee',
        'billing_cycle',
        'next_billing_date',
        'owner_id',
        'created_by',
        'updated_by',
        'last_activity_at',
        'last_activity_ip',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'date',
        'subscription_starts_at' => 'date',
        'subscription_ends_at' => 'date',
        'next_billing_date' => 'date',
        'last_activity_at' => 'datetime',
        'settings' => 'array',
        'features' => 'array',
        'theme_settings' => 'array',
        'integration_settings' => 'array',
        'notification_settings' => 'array',
        'metadata' => 'array',
        'max_users' => 'integer',
        'max_branch_shops' => 'integer',
        'max_products' => 'integer',
        'storage_limit' => 'integer',
        'api_rate_limit' => 'integer',
        'current_users' => 'integer',
        'current_branch_shops' => 'integer',
        'current_products' => 'integer',
        'current_storage_used' => 'integer',
        'employee_count' => 'integer',
        'monthly_fee' => 'decimal:2',
        'setup_fee' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_badge',
        'plan_badge',
        'usage_percentage',
        'is_trial',
        'is_active',
        'days_until_expiry',
        'full_domain'
    ];

    /**
     * Business type constants
     */
    const BUSINESS_TYPE_RETAIL = 'retail';
    const BUSINESS_TYPE_WHOLESALE = 'wholesale';
    const BUSINESS_TYPE_RESTAURANT = 'restaurant';
    const BUSINESS_TYPE_SERVICE = 'service';
    const BUSINESS_TYPE_MANUFACTURING = 'manufacturing';
    const BUSINESS_TYPE_OTHER = 'other';

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TRIAL = 'trial';
    const STATUS_EXPIRED = 'expired';

    /**
     * Plan type constants
     */
    const PLAN_TRIAL = 'trial';
    const PLAN_BASIC = 'basic';
    const PLAN_PREMIUM = 'premium';
    const PLAN_ENTERPRISE = 'enterprise';
    const PLAN_CUSTOM = 'custom';

    /**
     * Billing cycle constants
     */
    const BILLING_MONTHLY = 'monthly';
    const BILLING_QUARTERLY = 'quarterly';
    const BILLING_YEARLY = 'yearly';

    /**
     * Get all business types
     */
    public static function getBusinessTypes(): array
    {
        return [
            self::BUSINESS_TYPE_RETAIL => 'Bán lẻ',
            self::BUSINESS_TYPE_WHOLESALE => 'Bán sỉ',
            self::BUSINESS_TYPE_RESTAURANT => 'Nhà hàng',
            self::BUSINESS_TYPE_SERVICE => 'Dịch vụ',
            self::BUSINESS_TYPE_MANUFACTURING => 'Sản xuất',
            self::BUSINESS_TYPE_OTHER => 'Khác',
        ];
    }

    /**
     * Get all status types
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_INACTIVE => 'Không hoạt động',
            self::STATUS_SUSPENDED => 'Tạm ngưng',
            self::STATUS_TRIAL => 'Dùng thử',
            self::STATUS_EXPIRED => 'Hết hạn',
        ];
    }

    /**
     * Get all plan types
     */
    public static function getPlanTypes(): array
    {
        return [
            self::PLAN_TRIAL => 'Dùng thử',
            self::PLAN_BASIC => 'Cơ bản',
            self::PLAN_PREMIUM => 'Cao cấp',
            self::PLAN_ENTERPRISE => 'Doanh nghiệp',
            self::PLAN_CUSTOM => 'Tùy chỉnh',
        ];
    }

    /**
     * Relationship with users through pivot table
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_users')
                    ->withPivot([
                        'role',
                        'permissions',
                        'restrictions',
                        'is_active',
                        'is_primary',
                        'joined_at',
                        'last_access_at',
                        'access_expires_at',
                        'invitation_status',
                        'invited_by',
                        'approved_by',
                        'approved_at'
                    ])
                    ->withTimestamps();
    }

    /**
     * Get active users for this tenant
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_active', true);
    }

    /**
     * Get tenant owners
     */
    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Get tenant admins
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    /**
     * Relationship with tenant settings
     */
    public function tenantSettings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    /**
     * Relationship with tenant invitations
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class);
    }

    /**
     * Relationship with tenant activity logs
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(TenantActivityLog::class);
    }

    /**
     * Relationship with branch shops
     */
    public function branchShops(): HasMany
    {
        return $this->hasMany(BranchShop::class);
    }

    /**
     * Relationship with products
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relationship with customers
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Relationship with orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relationship with invoices
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Relationship with owner user
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relationship with creator user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get status badge
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getStatusBadge()
        );
    }

    /**
     * Get plan badge
     */
    protected function planBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getPlanBadge()
        );
    }

    /**
     * Get usage percentage
     */
    protected function usagePercentage(): Attribute
    {
        return new Attribute(
            get: fn() => $this->calculateUsagePercentage()
        );
    }

    /**
     * Check if tenant is on trial
     */
    protected function isTrial(): Attribute
    {
        return new Attribute(
            get: fn() => $this->status === self::STATUS_TRIAL || $this->plan_type === self::PLAN_TRIAL
        );
    }

    /**
     * Check if tenant is active
     */
    protected function isActive(): Attribute
    {
        return new Attribute(
            get: fn() => $this->status === self::STATUS_ACTIVE
        );
    }

    /**
     * Get days until expiry
     */
    protected function daysUntilExpiry(): Attribute
    {
        return new Attribute(
            get: fn() => $this->calculateDaysUntilExpiry()
        );
    }

    /**
     * Get full domain
     */
    protected function fullDomain(): Attribute
    {
        return new Attribute(
            get: fn() => $this->domain ?: ($this->subdomain ? $this->subdomain . '.' . config('app.domain', 'yukimart.local') : null)
        );
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge(): string
    {
        $badges = [
            self::STATUS_ACTIVE => '<span class="badge badge-success">Hoạt động</span>',
            self::STATUS_INACTIVE => '<span class="badge badge-secondary">Không hoạt động</span>',
            self::STATUS_SUSPENDED => '<span class="badge badge-warning">Tạm ngưng</span>',
            self::STATUS_TRIAL => '<span class="badge badge-info">Dùng thử</span>',
            self::STATUS_EXPIRED => '<span class="badge badge-danger">Hết hạn</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">Không xác định</span>';
    }

    /**
     * Get plan badge HTML
     */
    private function getPlanBadge(): string
    {
        $badges = [
            self::PLAN_TRIAL => '<span class="badge badge-light">Dùng thử</span>',
            self::PLAN_BASIC => '<span class="badge badge-primary">Cơ bản</span>',
            self::PLAN_PREMIUM => '<span class="badge badge-success">Cao cấp</span>',
            self::PLAN_ENTERPRISE => '<span class="badge badge-dark">Doanh nghiệp</span>',
            self::PLAN_CUSTOM => '<span class="badge badge-warning">Tùy chỉnh</span>',
        ];

        return $badges[$this->plan_type] ?? '<span class="badge badge-secondary">Không xác định</span>';
    }

    /**
     * Calculate usage percentage
     */
    private function calculateUsagePercentage(): array
    {
        return [
            'users' => $this->max_users > 0 ? round(($this->current_users / $this->max_users) * 100, 2) : 0,
            'branch_shops' => $this->max_branch_shops > 0 ? round(($this->current_branch_shops / $this->max_branch_shops) * 100, 2) : 0,
            'products' => $this->max_products > 0 ? round(($this->current_products / $this->max_products) * 100, 2) : 0,
            'storage' => $this->storage_limit > 0 ? round(($this->current_storage_used / $this->storage_limit) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate days until expiry
     */
    private function calculateDaysUntilExpiry(): ?int
    {
        if ($this->subscription_ends_at) {
            return Carbon::now()->diffInDays($this->subscription_ends_at, false);
        }

        if ($this->trial_ends_at && $this->is_trial) {
            return Carbon::now()->diffInDays($this->trial_ends_at, false);
        }

        return null;
    }

    /**
     * Business Logic Methods
     */

    /**
     * Check if tenant can add more users
     */
    public function canAddUsers(int $count = 1): bool
    {
        return ($this->current_users + $count) <= $this->max_users;
    }

    /**
     * Check if tenant can add more branch shops
     */
    public function canAddBranchShops(int $count = 1): bool
    {
        return ($this->current_branch_shops + $count) <= $this->max_branch_shops;
    }

    /**
     * Check if tenant can add more products
     */
    public function canAddProducts(int $count = 1): bool
    {
        return ($this->current_products + $count) <= $this->max_products;
    }

    /**
     * Check if tenant has storage space
     */
    public function hasStorageSpace(int $bytes): bool
    {
        return ($this->current_storage_used + $bytes) <= $this->storage_limit;
    }

    /**
     * Check if feature is enabled for tenant
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return isset($features[$feature]) && $features[$feature] === true;
    }

    /**
     * Enable a feature for tenant
     */
    public function enableFeature(string $feature): void
    {
        $features = $this->features ?? [];
        $features[$feature] = true;
        $this->update(['features' => $features]);
    }

    /**
     * Disable a feature for tenant
     */
    public function disableFeature(string $feature): void
    {
        $features = $this->features ?? [];
        $features[$feature] = false;
        $this->update(['features' => $features]);
    }

    /**
     * Get remaining user slots
     */
    public function getRemainingUsers(): int
    {
        return max(0, $this->max_users - $this->current_users);
    }

    /**
     * Get remaining product slots
     */
    public function getRemainingProducts(): int
    {
        return max(0, $this->max_products - $this->current_products);
    }

    /**
     * Get remaining storage space in bytes
     */
    public function getRemainingStorage(): int
    {
        return max(0, $this->storage_limit - $this->current_storage_used);
    }

    /**
     * Get tenant setting value
     */
    public function getSetting(string $key, $default = null)
    {
        $setting = $this->tenantSettings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set tenant setting value
     */
    public function setSetting(string $key, $value, string $category = 'general'): void
    {
        $this->tenantSettings()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'category' => $category,
                'type' => $this->getValueType($value)
            ]
        );
    }

    /**
     * Update usage statistics
     */
    public function updateUsageStats(): void
    {
        $this->update([
            'current_users' => $this->users()->count(),
            'current_branch_shops' => $this->branchShops()->count(),
            'current_products' => $this->products()->count(),
            'current_storage_used' => $this->calculateStorageUsed(),
        ]);
    }

    /**
     * Check if tenant subscription is expired
     */
    public function isExpired(): bool
    {
        if ($this->subscription_ends_at) {
            return Carbon::now()->gt($this->subscription_ends_at);
        }

        if ($this->trial_ends_at && $this->is_trial) {
            return Carbon::now()->gt($this->trial_ends_at);
        }

        return false;
    }

    /**
     * Check if tenant is near expiry
     */
    public function isNearExpiry(int $days = 7): bool
    {
        $daysUntilExpiry = $this->days_until_expiry;
        return $daysUntilExpiry !== null && $daysUntilExpiry <= $days && $daysUntilExpiry > 0;
    }

    /**
     * Suspend tenant
     */
    public function suspend(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
            'metadata' => array_merge($this->metadata ?? [], [
                'suspended_at' => Carbon::now(),
                'suspension_reason' => $reason
            ])
        ]);
    }

    /**
     * Activate tenant
     */
    public function activate(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'metadata' => array_merge($this->metadata ?? [], [
                'activated_at' => Carbon::now()
            ])
        ]);
    }

    /**
     * Expire tenant
     */
    public function expire(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
            'metadata' => array_merge($this->metadata ?? [], [
                'expired_at' => Carbon::now()
            ])
        ]);
    }

    /**
     * Record activity
     */
    public function recordActivity(string $action, array $properties = []): void
    {
        $this->activityLogs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->update(['last_activity_at' => Carbon::now()]);
    }

    /**
     * Get value type for setting
     */
    private function getValueType($value): string
    {
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'decimal';
        if (is_array($value)) return 'json';
        return 'string';
    }

    /**
     * Calculate storage used
     */
    private function calculateStorageUsed(): int
    {
        // This would calculate actual storage used
        // For now, return 0 as placeholder
        return 0;
    }

    /**
     * Scopes
     */

    /**
     * Scope for active tenants
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for trial tenants
     */
    public function scopeTrial($query)
    {
        return $query->where('status', self::STATUS_TRIAL)
                    ->orWhere('plan_type', self::PLAN_TRIAL);
    }

    /**
     * Scope for expired tenants
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere(function($q) {
                        $q->whereNotNull('subscription_ends_at')
                          ->where('subscription_ends_at', '<', Carbon::now());
                    })
                    ->orWhere(function($q) {
                        $q->whereNotNull('trial_ends_at')
                          ->where('trial_ends_at', '<', Carbon::now())
                          ->where('plan_type', self::PLAN_TRIAL);
                    });
    }

    /**
     * Scope for tenants by plan
     */
    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan_type', $plan);
    }
}
