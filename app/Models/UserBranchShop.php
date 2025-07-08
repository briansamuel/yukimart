<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserBranchShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_shop_id',
        'role_in_shop',
        'start_date',
        'end_date',
        'is_active',
        'is_primary',
        'notes',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['role_label', 'status_badge', 'work_duration', 'formatted_start_date', 'display_start_date'];

    /**
     * Role constants
     */
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';
    const ROLE_CASHIER = 'cashier';
    const ROLE_SALES = 'sales';
    const ROLE_WAREHOUSE_KEEPER = 'warehouse_keeper';

    /**
     * Get all available roles
     */
    public static function getRoles()
    {
        return [
            self::ROLE_MANAGER => __('branch_shops.roles.manager'),
            self::ROLE_STAFF => __('branch_shops.roles.staff'),
            self::ROLE_CASHIER => __('branch_shops.roles.cashier'),
            self::ROLE_SALES => __('branch_shops.roles.sales'),
            self::ROLE_WAREHOUSE_KEEPER => __('branch_shops.roles.warehouse_keeper'),
        ];
    }

    /**
     * Relationship with user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with branch shop
     */
    public function branchShop(): BelongsTo
    {
        return $this->belongsTo(BranchShop::class);
    }

    /**
     * Relationship with assigner
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get role label
     */
    protected function roleLabel(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => self::getRoles()[$attributes['role_in_shop']] ?? $attributes['role_in_shop']
        );
    }

    /**
     * Get status badge
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                if (!$attributes['is_active']) {
                    return 'badge-light-danger';
                }
                
                if ($attributes['end_date'] && now()->gt($attributes['end_date'])) {
                    return 'badge-light-warning';
                }
                
                return 'badge-light-success';
            }
        );
    }

    /**
     * Get work duration
     */
    protected function workDuration(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $startDate = $attributes['start_date'] ? \Carbon\Carbon::parse($attributes['start_date']) : null;
                $endDate = $attributes['end_date'] ? \Carbon\Carbon::parse($attributes['end_date']) : now();

                if (!$startDate) {
                    return null;
                }

                return $startDate->diffInDays($endDate) . ' ' . __('common.days');
            }
        );
    }

    /**
     * Get formatted start date for forms (Y-m-d)
     */
    protected function formattedStartDate(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $attributes['start_date'] ? \Carbon\Carbon::parse($attributes['start_date'])->format('Y-m-d') : ''
        );
    }

    /**
     * Get display start date (d/m/Y)
     */
    protected function displayStartDate(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $attributes['start_date'] ? \Carbon\Carbon::parse($attributes['start_date'])->format('d/m/Y') : '-'
        );
    }

    /**
     * Scope for active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for primary branch assignments
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for current assignments (not ended)
     */
    public function scopeCurrent($query)
    {
        return $query->where(function($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now()->toDateString());
        });
    }

    /**
     * Scope for specific role
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role_in_shop', $role);
    }

    /**
     * Scope for managers
     */
    public function scopeManagers($query)
    {
        return $query->where('role_in_shop', self::ROLE_MANAGER);
    }

    /**
     * Scope for staff
     */
    public function scopeStaff($query)
    {
        return $query->where('role_in_shop', self::ROLE_STAFF);
    }

    /**
     * Check if assignment is currently active
     */
    public function isCurrentlyActive()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->end_date && now()->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user is manager in this branch
     */
    public function isManager()
    {
        return $this->role_in_shop === self::ROLE_MANAGER;
    }

    /**
     * Check if this is user's primary branch
     */
    public function isPrimaryBranch()
    {
        return $this->is_primary;
    }

    /**
     * Set as primary branch (and unset others)
     */
    public function setAsPrimary()
    {
        // Unset other primary branches for this user
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * End assignment
     */
    public function endAssignment($endDate = null)
    {
        $this->update([
            'end_date' => $endDate ?: now()->toDateString(),
            'is_active' => false,
        ]);
    }

    /**
     * Extend assignment
     */
    public function extendAssignment($newEndDate = null)
    {
        $this->update([
            'end_date' => $newEndDate,
            'is_active' => true,
        ]);
    }
}
