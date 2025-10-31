<?php

namespace App\Models;

use App\Helpers\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasFactory, HasRoles, HasApiTokens, TenantScoped;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'username', 'email', 'password',
    // ];

    protected $guarded = [];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    // protected $dateFormat = 'd/m/Y H:i:s';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['last_ago', 'first_letter_name', 'page_edit', 'page_delete', 'page_detail', 'background'];

    protected function firstLetterName(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => mb_substr($attributes['full_name'], 0, 1),
        );
    }

    protected function background(): Attribute
    {
        return new Attribute(
            get: fn($value,  $attributes) => Common::randomBackground(),
        );
    }

    /**
     * Get the team ID for Spatie Permission package
     * This is required for multi-tenant support
     */
    public function getPermissionsTeamId()
    {
        return $this->tenant_id;
    }

    /**
     * Get user setting value
     */
    public function getSetting($key, $default = null)
    {
        $setting = \App\Models\UserSetting::where('user_id', $this->id)
                                         ->where('key', $key)
                                         ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set user setting value
     */
    public function setSetting($key, $value)
    {
        return \App\Models\UserSetting::updateOrCreate(
            ['user_id' => $this->id, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Relationship with branch shops through pivot table
     */
    public function branchShops()
    {
        return $this->belongsToMany(BranchShop::class, 'user_branch_shops')
                    ->withPivot([
                        'role_in_shop',
                        'start_date',
                        'end_date',
                        'is_active',
                        'is_primary',
                        'notes',
                        'assigned_by',
                        'assigned_at'
                    ])
                    ->withTimestamps();
    }

    /**
     * Get active branch shops for this user
     */
    public function activeBranchShops()
    {
        return $this->branchShops()->wherePivot('is_active', true);
    }

    /**
     * Get primary branch shop for this user
     */
    public function primaryBranchShop()
    {
        return $this->branchShops()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get current branch shops (not ended)
     */
    public function currentBranchShops()
    {
        return $this->branchShops()
                    ->wherePivot('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('user_branch_shops.end_date')
                              ->orWhere('user_branch_shops.end_date', '>=', now()->toDateString());
                    });
    }

    /**
     * Check if user works in specific branch shop
     */
    public function worksInBranchShop($branchShopId)
    {
        return $this->currentBranchShops()->where('branch_shops.id', $branchShopId)->exists();
    }

    /**
     * Check if user is manager of specific branch shop
     */
    public function isManagerOf($branchShopId)
    {
        return $this->currentBranchShops()
                    ->where('branch_shops.id', $branchShopId)
                    ->wherePivot('role_in_shop', 'manager')
                    ->exists();
    }

    /**
     * Get user's role in specific branch shop
     */
    public function getRoleInBranchShop($branchShopId)
    {
        $branchShop = $this->branchShops()
                          ->where('branch_shops.id', $branchShopId)
                          ->wherePivot('is_active', true)
                          ->first();

        return $branchShop ? $branchShop->pivot->role_in_shop : null;
    }



    /**
     * Get formatted birth date for forms
     */
    public function getFormattedBirthDateAttribute()
    {
        return $this->birth_date ? $this->birth_date->format('Y-m-d') : '';
    }

    /**
     * Get display birth date
     */
    public function getDisplayBirthDateAttribute()
    {
        return $this->birth_date ? $this->birth_date->format('d/m/Y') : '';
    }

    /**
     * Format pivot date safely
     */
    public static function formatPivotDate($date, $format = 'd/m/Y')
    {
        if (!$date) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return '-';
        }
    }


    /**
     * Get the user's human created_at.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function lastAgo(): Attribute
    {
        Carbon::setLocale('vi');
        return new Attribute(
            get: fn ($value, $attributes) => isset($attributes['created_at'])
                ? Carbon::parse($attributes['created_at'])->diffForHumans()
                : '-'
        );
    }

    /**
     * Get the user's edit page url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function pageEdit(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => route('admin.users.edit', ['id' => $attributes['id']]),
        );
    }

    /**
     * Get the user's delete page url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function pageDelete(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => route('admin.users.destroy', ['id' => $attributes['id']]),
        );
    }

    /**
     * Get the user's detail page url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function pageDetail(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => route('admin.users.show', ['id' => $attributes['id']]),
        );
    }
    /**
     * Get the user's formatted created_at for display.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function createdAtFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => isset($attributes['created_at'])
                ? Carbon::parse($attributes['created_at'])->format('d/m/Y H:i:s')
                : '-'
        );
    }

    /**
     * Get the user's formatted updated_at for display.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function updatedAtFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => isset($attributes['updated_at'])
                ? Carbon::parse($attributes['updated_at'])->format('d/m/Y H:i:s')
                : '-'
        );
    }

    /**
     * Orders created by this user
     */
    public function createdOrders()
    {
        return $this->hasMany(\App\Models\Order::class, 'created_by');
    }

    /**
     * Orders sold by this user
     */
    public function soldOrders()
    {
        return $this->hasMany(\App\Models\Order::class, 'sold_by');
    }

    /**
     * Invoices created by this user
     */
    public function createdInvoices()
    {
        return $this->hasMany(\App\Models\Invoice::class, 'created_by');
    }

    /**
     * Invoices sold by this user
     */
    public function soldInvoices()
    {
        return $this->hasMany(\App\Models\Invoice::class, 'sold_by');
    }

    /**
     * Payments created by this user
     */
    public function createdPayments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'created_by');
    }

    /**
     * Tenant Relationships
     */

    /**
     * Get the tenant this user belongs to (primary tenant)
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all tenants this user has access to
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
                    ->withPivot([
                        'role',
                        'permissions',
                        'restrictions',
                        'is_active',
                        'is_primary',
                        'joined_at',
                        'last_access_at',
                        'access_expires_at',
                        'invitation_status'
                    ])
                    ->withTimestamps();
    }

    /**
     * Get active tenants for this user
     */
    public function activeTenants(): BelongsToMany
    {
        return $this->tenants()->wherePivot('is_active', true);
    }

    /**
     * Get primary tenant for this user
     */
    public function primaryTenant(): BelongsTo
    {
        $tenantUser = $this->tenants()->wherePivot('is_primary', true)->first();
        return $tenantUser ? $tenantUser->tenant() : $this->tenant();
    }

    /**
     * Get current tenant for this user (from session or primary)
     */
    public function getCurrentTenant(): ?Tenant
    {
        // Try to get from session first
        if (session()->has('current_tenant_id')) {
            $tenantId = session('current_tenant_id');
            return $this->tenants()->where('tenants.id', $tenantId)->first();
        }

        // Fallback to primary tenant
        return $this->primaryTenant;
    }

    /**
     * Check if user belongs to specific tenant
     */
    public function belongsToTenant(int $tenantId): bool
    {
        return $this->tenants()->where('tenants.id', $tenantId)->exists();
    }

    /**
     * Check if user has role in specific tenant
     */
    public function hasRoleInTenant(int $tenantId, string $role): bool
    {
        return $this->tenants()
                    ->where('tenants.id', $tenantId)
                    ->wherePivot('role', $role)
                    ->exists();
    }

    /**
     * Check if user is owner of specific tenant
     */
    public function isOwnerOfTenant(int $tenantId): bool
    {
        return $this->hasRoleInTenant($tenantId, TenantUser::ROLE_OWNER);
    }

    /**
     * Check if user is admin of specific tenant
     */
    public function isAdminOfTenant(int $tenantId): bool
    {
        return $this->tenants()
                    ->where('tenants.id', $tenantId)
                    ->wherePivotIn('role', [TenantUser::ROLE_OWNER, TenantUser::ROLE_ADMIN])
                    ->exists();
    }

    /**
     * Get user's role in specific tenant
     */
    public function getRoleInTenant(int $tenantId): ?string
    {
        $tenantUser = $this->tenants()->where('tenants.id', $tenantId)->first();
        return $tenantUser ? $tenantUser->pivot->role : null;
    }

    /**
     * Switch to specific tenant
     */
    public function switchToTenant(int $tenantId): bool
    {
        if (!$this->belongsToTenant($tenantId)) {
            return false;
        }

        session(['current_tenant_id' => $tenantId]);

        // Update last access time
        $this->tenants()->updateExistingPivot($tenantId, [
            'last_access_at' => Carbon::now()
        ]);

        return true;
    }

    /**
     * Check if user is super admin (can access all tenants)
     */
    public function isSuperAdmin(): bool
    {
        // You can implement this based on your business logic
        // For example, check if user has a specific role or permission
        return $this->hasRole('super_admin') || $this->email === 'admin@yukimart.local';
    }

    /**
     * Get tenant-specific settings
     */
    public function getTenantSetting(string $key, $default = null, ?int $tenantId = null)
    {
        $tenantId = $tenantId ?: $this->getCurrentTenant()?->id;

        if (!$tenantId) {
            return $default;
        }

        return TenantSetting::getForTenant($tenantId, $key, $default);
    }

    /**
     * Set tenant-specific setting
     */
    public function setTenantSetting(string $key, $value, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?: $this->getCurrentTenant()?->id;

        if ($tenantId) {
            TenantSetting::setForTenant($tenantId, $key, $value);
        }
    }
}
