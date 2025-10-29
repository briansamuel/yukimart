<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TenantInvitation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_invitations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'email',
        'token',
        'role',
        'permissions',
        'message',
        'status',
        'expires_at',
        'accepted_at',
        'declined_at',
        'invited_by',
        'accepted_by',
        'email_sent_count',
        'last_email_sent_at',
        'accepted_from_ip'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'email_sent_count' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_badge',
        'role_badge',
        'is_expired',
        'days_until_expiry',
        'invitation_url'
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Role constants (same as TenantUser)
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';
    const ROLE_VIEWER = 'viewer';

    /**
     * Get all statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_ACCEPTED => 'Đã chấp nhận',
            self::STATUS_DECLINED => 'Đã từ chối',
            self::STATUS_EXPIRED => 'Hết hạn',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];
    }

    /**
     * Get all roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Quản trị viên',
            self::ROLE_MANAGER => 'Quản lý',
            self::ROLE_STAFF => 'Nhân viên',
            self::ROLE_VIEWER => 'Xem',
        ];
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (!$invitation->token) {
                $invitation->token = Str::random(64);
            }
            
            if (!$invitation->expires_at) {
                $invitation->expires_at = Carbon::now()->addDays(7);
            }
        });
    }

    /**
     * Relationship with tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relationship with inviter
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Relationship with accepter
     */
    public function accepter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
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
     * Get role badge
     */
    protected function roleBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getRoleBadge()
        );
    }

    /**
     * Check if invitation is expired
     */
    protected function isExpired(): Attribute
    {
        return new Attribute(
            get: fn() => Carbon::now()->gt($this->expires_at)
        );
    }

    /**
     * Get days until expiry
     */
    protected function daysUntilExpiry(): Attribute
    {
        return new Attribute(
            get: fn() => Carbon::now()->diffInDays($this->expires_at, false)
        );
    }

    /**
     * Get invitation URL
     */
    protected function invitationUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('tenant.invitation.accept', ['token' => $this->token])
        );
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge(): string
    {
        $badges = [
            self::STATUS_PENDING => '<span class="badge badge-warning">Chờ xác nhận</span>',
            self::STATUS_ACCEPTED => '<span class="badge badge-success">Đã chấp nhận</span>',
            self::STATUS_DECLINED => '<span class="badge badge-danger">Đã từ chối</span>',
            self::STATUS_EXPIRED => '<span class="badge badge-secondary">Hết hạn</span>',
            self::STATUS_CANCELLED => '<span class="badge badge-dark">Đã hủy</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-light">Không xác định</span>';
    }

    /**
     * Get role badge HTML
     */
    private function getRoleBadge(): string
    {
        $badges = [
            self::ROLE_ADMIN => '<span class="badge badge-danger">Quản trị viên</span>',
            self::ROLE_MANAGER => '<span class="badge badge-warning">Quản lý</span>',
            self::ROLE_STAFF => '<span class="badge badge-primary">Nhân viên</span>',
            self::ROLE_VIEWER => '<span class="badge badge-secondary">Xem</span>',
        ];

        return $badges[$this->role] ?? '<span class="badge badge-light">Không xác định</span>';
    }

    /**
     * Business Logic Methods
     */

    /**
     * Accept invitation
     */
    public function accept(User $user): bool
    {
        if (!$this->canBeAccepted()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => Carbon::now(),
            'accepted_by' => $user->id,
            'accepted_from_ip' => request()->ip()
        ]);

        // Create tenant-user relationship
        TenantUser::create([
            'tenant_id' => $this->tenant_id,
            'user_id' => $user->id,
            'role' => $this->role,
            'permissions' => $this->permissions,
            'is_active' => true,
            'is_primary' => false,
            'joined_at' => Carbon::now(),
            'invitation_status' => TenantUser::INVITATION_ACCEPTED,
            'invited_by' => $this->invited_by,
            'approved_by' => $user->id,
            'approved_at' => Carbon::now()
        ]);

        return true;
    }

    /**
     * Decline invitation
     */
    public function decline(): bool
    {
        if (!$this->canBeDeclined()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_DECLINED,
            'declined_at' => Carbon::now()
        ]);

        return true;
    }

    /**
     * Cancel invitation
     */
    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);
        return true;
    }

    /**
     * Resend invitation
     */
    public function resend(): bool
    {
        if (!$this->canBeResent()) {
            return false;
        }

        // Extend expiry date
        $this->update([
            'expires_at' => Carbon::now()->addDays(7),
            'status' => self::STATUS_PENDING,
            'email_sent_count' => $this->email_sent_count + 1,
            'last_email_sent_at' => Carbon::now()
        ]);

        return true;
    }

    /**
     * Check if invitation can be accepted
     */
    public function canBeAccepted(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->is_expired;
    }

    /**
     * Check if invitation can be declined
     */
    public function canBeDeclined(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->is_expired;
    }

    /**
     * Check if invitation can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_EXPIRED]);
    }

    /**
     * Check if invitation can be resent
     */
    public function canBeResent(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_EXPIRED]) 
               && $this->email_sent_count < 5; // Limit resend attempts
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): void
    {
        if ($this->status === self::STATUS_PENDING && $this->is_expired) {
            $this->update(['status' => self::STATUS_EXPIRED]);
        }
    }

    /**
     * Get invitation details for email
     */
    public function getEmailData(): array
    {
        return [
            'tenant_name' => $this->tenant->name,
            'inviter_name' => $this->inviter->full_name ?? $this->inviter->username,
            'role' => self::getRoles()[$this->role] ?? $this->role,
            'invitation_url' => $this->invitation_url,
            'expires_at' => $this->expires_at->format('d/m/Y H:i'),
            'message' => $this->message,
            'email' => $this->email
        ];
    }

    /**
     * Static helper methods
     */

    /**
     * Find invitation by token
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }

    /**
     * Create new invitation
     */
    public static function createInvitation(
        Tenant $tenant,
        string $email,
        string $role,
        ?array $permissions = null,
        ?string $message = null
    ): self {
        return static::create([
            'tenant_id' => $tenant->id,
            'email' => $email,
            'role' => $role,
            'permissions' => $permissions,
            'message' => $message,
            'status' => self::STATUS_PENDING,
            'invited_by' => auth()->id(),
            'email_sent_count' => 1,
            'last_email_sent_at' => Carbon::now()
        ]);
    }

    /**
     * Scopes
     */

    /**
     * Scope for pending invitations
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now())
                    ->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for active invitations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope by email
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }
}
