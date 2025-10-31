<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class TenantUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'permissions',
        'restrictions',
        'is_active',
        'is_primary',
        'joined_at',
        'last_access_at',
        'access_expires_at',
        'invitation_status',
        'invitation_token',
        'invitation_sent_at',
        'invitation_expires_at',
        'invited_by',
        'approved_by',
        'approved_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'restrictions' => 'array',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'joined_at' => 'datetime',
        'last_access_at' => 'datetime',
        'access_expires_at' => 'date',
        'invitation_sent_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'role_badge',
        'status_badge',
        'is_invitation_expired',
        'days_since_joined',
        'access_status'
    ];

    /**
     * Role constants
     */
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';
    const ROLE_VIEWER = 'viewer';

    /**
     * Invitation status constants
     */
    const INVITATION_PENDING = 'pending';
    const INVITATION_ACCEPTED = 'accepted';
    const INVITATION_DECLINED = 'declined';
    const INVITATION_EXPIRED = 'expired';

    /**
     * Get all available roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_OWNER => 'Chủ sở hữu',
            self::ROLE_ADMIN => 'Quản trị viên',
            self::ROLE_MANAGER => 'Quản lý',
            self::ROLE_STAFF => 'Nhân viên',
            self::ROLE_VIEWER => 'Xem',
        ];
    }

    /**
     * Get all invitation statuses
     */
    public static function getInvitationStatuses(): array
    {
        return [
            self::INVITATION_PENDING => 'Chờ xác nhận',
            self::INVITATION_ACCEPTED => 'Đã chấp nhận',
            self::INVITATION_DECLINED => 'Đã từ chối',
            self::INVITATION_EXPIRED => 'Hết hạn',
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
     * Relationship with inviter
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Relationship with approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get role badge
     */
    protected function roleBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getRoleBadge()
        );
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
     * Check if invitation is expired
     */
    protected function isInvitationExpired(): Attribute
    {
        return new Attribute(
            get: fn() => $this->invitation_expires_at && Carbon::now()->gt($this->invitation_expires_at)
        );
    }

    /**
     * Get days since joined
     */
    protected function daysSinceJoined(): Attribute
    {
        return new Attribute(
            get: fn() => $this->joined_at ? Carbon::now()->diffInDays($this->joined_at) : null
        );
    }

    /**
     * Get access status
     */
    protected function accessStatus(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getAccessStatus()
        );
    }

    /**
     * Get role badge HTML
     */
    private function getRoleBadge(): string
    {
        $badges = [
            self::ROLE_OWNER => '<span class="badge badge-dark">Chủ sở hữu</span>',
            self::ROLE_ADMIN => '<span class="badge badge-danger">Quản trị viên</span>',
            self::ROLE_MANAGER => '<span class="badge badge-warning">Quản lý</span>',
            self::ROLE_STAFF => '<span class="badge badge-primary">Nhân viên</span>',
            self::ROLE_VIEWER => '<span class="badge badge-secondary">Xem</span>',
        ];

        return $badges[$this->role] ?? '<span class="badge badge-light">Không xác định</span>';
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge(): string
    {
        if (!$this->is_active) {
            return '<span class="badge badge-secondary">Không hoạt động</span>';
        }

        if ($this->access_expires_at && Carbon::now()->gt($this->access_expires_at)) {
            return '<span class="badge badge-danger">Hết hạn truy cập</span>';
        }

        if ($this->invitation_status === self::INVITATION_PENDING) {
            return '<span class="badge badge-warning">Chờ xác nhận</span>';
        }

        return '<span class="badge badge-success">Hoạt động</span>';
    }

    /**
     * Get access status
     */
    private function getAccessStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->access_expires_at && Carbon::now()->gt($this->access_expires_at)) {
            return 'expired';
        }

        if ($this->invitation_status === self::INVITATION_PENDING) {
            return 'pending';
        }

        return 'active';
    }

    /**
     * Business Logic Methods
     */

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Add permission to user
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Remove permission from user
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Check if user has restriction
     */
    public function hasRestriction(string $restriction): bool
    {
        $restrictions = $this->restrictions ?? [];
        return in_array($restriction, $restrictions);
    }

    /**
     * Activate user in tenant
     */
    public function activate(): void
    {
        $this->update([
            'is_active' => true,
            'approved_at' => Carbon::now(),
            'approved_by' => auth()->id()
        ]);
    }

    /**
     * Deactivate user in tenant
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Accept invitation
     */
    public function acceptInvitation(): void
    {
        $this->update([
            'invitation_status' => self::INVITATION_ACCEPTED,
            'is_active' => true,
            'joined_at' => Carbon::now(),
            'approved_at' => Carbon::now()
        ]);
    }

    /**
     * Decline invitation
     */
    public function declineInvitation(): void
    {
        $this->update([
            'invitation_status' => self::INVITATION_DECLINED,
            'is_active' => false
        ]);
    }

    /**
     * Update last access time
     */
    public function updateLastAccess(): void
    {
        $this->update(['last_access_at' => Carbon::now()]);
    }



    /**
     * Check if user is owner of tenant
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Check if user is admin of tenant
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if user can manage products
     */
    public function canManageProducts(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if user can view reports
     */
    public function canViewReports(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if user can access tenant
     */
    public function canAccess(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->invitation_status !== self::INVITATION_ACCEPTED) {
            return false;
        }

        if ($this->access_expires_at && Carbon::now()->gt($this->access_expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user access is expired
     */
    public function isExpired(): bool
    {
        return $this->access_expires_at && Carbon::now()->gt($this->access_expires_at);
    }

    /**
     * Check if user invitation is pending
     */
    public function isPending(): bool
    {
        return $this->invitation_status === self::INVITATION_PENDING;
    }

    /**
     * Scopes
     */

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for pending invitations
     */
    public function scopePendingInvitations($query)
    {
        return $query->where('invitation_status', self::INVITATION_PENDING);
    }

    /**
     * Scope for expired access
     */
    public function scopeExpiredAccess($query)
    {
        return $query->whereNotNull('access_expires_at')
                    ->where('access_expires_at', '<', Carbon::now());
    }
}
