<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserTimeStamp;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BranchShop extends Model
{
    use HasFactory, SoftDeletes, UserTimeStamp;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'working_days' => 'array',
        'area' => 'decimal:2',
        'staff_count' => 'integer',
        'has_delivery' => 'boolean',
        'delivery_radius' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'sort_order' => 'integer',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status_badge', 'full_address', 'working_hours', 'shop_type_label'];

    /**
     * Get the status badge for the branch shop
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $this->getStatusBadge($attributes['status'] ?? 'inactive')
        );
    }

    /**
     * Get the full address
     */
    protected function fullAddress(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => 
                ($attributes['address'] ?? '') . ', ' . 
                ($attributes['ward'] ?? '') . ', ' . 
                ($attributes['district'] ?? '') . ', ' . 
                ($attributes['province'] ?? '')
        );
    }

    /**
     * Get working hours display
     */
    protected function workingHours(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => 
                ($attributes['opening_time'] ?? '08:00') . ' - ' . 
                ($attributes['closing_time'] ?? '22:00')
        );
    }

    /**
     * Get shop type label
     */
    protected function shopTypeLabel(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $this->getShopTypeLabel($attributes['shop_type'] ?? 'standard')
        );
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'active' => '<span class="badge badge-light-success">Hoạt động</span>',
            'inactive' => '<span class="badge badge-light-danger">Ngừng hoạt động</span>',
            'maintenance' => '<span class="badge badge-light-warning">Bảo trì</span>',
        ];
        return $badges[$status] ?? '<span class="badge badge-light-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get shop type label
     */
    private function getShopTypeLabel($type)
    {
        $labels = [
            'flagship' => 'Cửa hàng chính',
            'standard' => 'Cửa hàng tiêu chuẩn',
            'mini' => 'Cửa hàng mini',
            'kiosk' => 'Quầy hàng',
        ];
        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Get the manager for this branch shop
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the warehouse for this branch shop
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the user who created this branch shop
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this branch shop
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get orders from this branch shop
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'branch_shop_id');
    }

    /**
     * Relationship with users through pivot table
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branch_shops')
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
     * Get active users for this branch shop
     */
    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true);
    }

    /**
     * Get current users (not ended)
     */
    public function currentUsers()
    {
        return $this->users()
                    ->wherePivot('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('user_branch_shops.end_date')
                              ->orWhere('user_branch_shops.end_date', '>=', now()->toDateString());
                    });
    }

    /**
     * Relationship with customers created at this branch shop
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get managers of this branch shop
     */
    public function managers()
    {
        return $this->currentUsers()->wherePivot('role_in_shop', 'manager');
    }

    /**
     * Get staff of this branch shop
     */
    public function staff()
    {
        return $this->currentUsers()->wherePivot('role_in_shop', 'staff');
    }

    /**
     * Get cashiers of this branch shop
     */
    public function cashiers()
    {
        return $this->currentUsers()->wherePivot('role_in_shop', 'cashier');
    }

    /**
     * Get sales staff of this branch shop
     */
    public function salesStaff()
    {
        return $this->currentUsers()->wherePivot('role_in_shop', 'sales');
    }

    /**
     * Get warehouse keepers of this branch shop
     */
    public function warehouseKeepers()
    {
        return $this->currentUsers()->wherePivot('role_in_shop', 'warehouse_keeper');
    }

    /**
     * Check if user works in this branch shop
     */
    public function hasUser($userId)
    {
        return $this->currentUsers()->where('users.id', $userId)->exists();
    }

    /**
     * Get users count by role
     */
    public function getUsersCountByRole()
    {
        return $this->currentUsers()
                    ->selectRaw('role_in_shop, count(*) as count')
                    ->groupBy('role_in_shop')
                    ->pluck('count', 'role_in_shop');
    }

    /**
     * Get invoices from this branch shop
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'branch_shop_id');
    }

    /**
     * Scope a query to only include active branch shops
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include branch shops with delivery
     */
    public function scopeWithDelivery($query)
    {
        return $query->where('has_delivery', true);
    }

    /**
     * Scope a query to filter by shop type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('shop_type', $type);
    }

    /**
     * Scope a query to filter by province
     */
    public function scopeInProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    /**
     * Check if branch shop is currently open
     */
    public function isOpen()
    {
        if (!$this->opening_time || !$this->closing_time) {
            return true; // Assume open if no time set
        }

        $now = now()->format('H:i');
        $opening = $this->opening_time->format('H:i');
        $closing = $this->closing_time->format('H:i');

        return $now >= $opening && $now <= $closing;
    }

    /**
     * Check if branch shop works on given day
     */
    public function worksOnDay($day = null)
    {
        if (!$this->working_days) {
            return true; // Assume works all days if not set
        }

        $day = $day ?? now()->format('l'); // Monday, Tuesday, etc.
        return in_array(strtolower($day), array_map('strtolower', $this->working_days));
    }

    /**
     * Get distance to given coordinates (in km)
     */
    public function getDistanceTo($latitude, $longitude)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if branch shop can deliver to given coordinates
     */
    public function canDeliverTo($latitude, $longitude)
    {
        if (!$this->has_delivery || !$this->delivery_radius) {
            return false;
        }

        $distance = $this->getDistanceTo($latitude, $longitude);
        return $distance !== null && $distance <= $this->delivery_radius;
    }

    /**
     * Get branch shop statistics
     */
    public function getStatistics()
    {
        return [
            'total_orders' => $this->orders()->count(),
            'total_revenue' => $this->orders()->sum('final_amount'),
            'total_invoices' => $this->invoices()->count(),
            'staff_count' => $this->staff_count,
            'is_open' => $this->isOpen(),
            'works_today' => $this->worksOnDay(),
        ];
    }
}
