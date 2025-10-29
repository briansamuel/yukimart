<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TenantSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'category',
        'key',
        'value',
        'type',
        'label',
        'description',
        'validation_rules',
        'options',
        'is_public',
        'is_readonly',
        'is_system',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
        'is_readonly' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'typed_value',
        'display_value',
        'category_badge'
    ];

    /**
     * Setting type constants
     */
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ARRAY = 'array';
    const TYPE_DECIMAL = 'decimal';

    /**
     * Setting category constants
     */
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_INVENTORY = 'inventory';
    const CATEGORY_SALES = 'sales';
    const CATEGORY_NOTIFICATIONS = 'notifications';
    const CATEGORY_INTEGRATIONS = 'integrations';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_APPEARANCE = 'appearance';
    const CATEGORY_BILLING = 'billing';

    /**
     * Get all setting types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_STRING => 'Chuỗi',
            self::TYPE_INTEGER => 'Số nguyên',
            self::TYPE_BOOLEAN => 'Đúng/Sai',
            self::TYPE_JSON => 'JSON',
            self::TYPE_ARRAY => 'Mảng',
            self::TYPE_DECIMAL => 'Số thập phân',
        ];
    }

    /**
     * Get all setting categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_GENERAL => 'Chung',
            self::CATEGORY_INVENTORY => 'Kho hàng',
            self::CATEGORY_SALES => 'Bán hàng',
            self::CATEGORY_NOTIFICATIONS => 'Thông báo',
            self::CATEGORY_INTEGRATIONS => 'Tích hợp',
            self::CATEGORY_SECURITY => 'Bảo mật',
            self::CATEGORY_APPEARANCE => 'Giao diện',
            self::CATEGORY_BILLING => 'Thanh toán',
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
     * Relationship with creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with updater
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get typed value
     */
    protected function typedValue(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getTypedValue()
        );
    }

    /**
     * Get display value
     */
    protected function displayValue(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getDisplayValue()
        );
    }

    /**
     * Get category badge
     */
    protected function categoryBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getCategoryBadge()
        );
    }

    /**
     * Get value with proper type casting
     */
    public function getTypedValue()
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            
            case self::TYPE_INTEGER:
                return (int) $this->value;
            
            case self::TYPE_DECIMAL:
                return (float) $this->value;
            
            case self::TYPE_JSON:
            case self::TYPE_ARRAY:
                return json_decode($this->value, true);
            
            case self::TYPE_STRING:
            default:
                return $this->value;
        }
    }

    /**
     * Get human-readable display value
     */
    private function getDisplayValue(): string
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return $this->getTypedValue() ? 'Có' : 'Không';
            
            case self::TYPE_JSON:
            case self::TYPE_ARRAY:
                $value = $this->getTypedValue();
                return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $this->value;
            
            default:
                return (string) $this->value;
        }
    }

    /**
     * Get category badge HTML
     */
    private function getCategoryBadge(): string
    {
        $badges = [
            self::CATEGORY_GENERAL => '<span class="badge badge-primary">Chung</span>',
            self::CATEGORY_INVENTORY => '<span class="badge badge-success">Kho hàng</span>',
            self::CATEGORY_SALES => '<span class="badge badge-warning">Bán hàng</span>',
            self::CATEGORY_NOTIFICATIONS => '<span class="badge badge-info">Thông báo</span>',
            self::CATEGORY_INTEGRATIONS => '<span class="badge badge-secondary">Tích hợp</span>',
            self::CATEGORY_SECURITY => '<span class="badge badge-danger">Bảo mật</span>',
            self::CATEGORY_APPEARANCE => '<span class="badge badge-dark">Giao diện</span>',
            self::CATEGORY_BILLING => '<span class="badge badge-light">Thanh toán</span>',
        ];

        return $badges[$this->category] ?? '<span class="badge badge-secondary">Khác</span>';
    }

    /**
     * Business Logic Methods
     */

    /**
     * Set value with proper type casting
     */
    public function setValue($value): void
    {
        $processedValue = $this->processValueForStorage($value);
        $this->update(['value' => $processedValue]);
    }

    /**
     * Process value for storage based on type
     */
    private function processValueForStorage($value): string
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return $value ? '1' : '0';
            
            case self::TYPE_JSON:
            case self::TYPE_ARRAY:
                return is_string($value) ? $value : json_encode($value);
            
            default:
                return (string) $value;
        }
    }

    /**
     * Validate value against validation rules
     */
    public function validateValue($value): bool
    {
        if (!$this->validation_rules) {
            return true;
        }

        // Parse validation rules
        $rules = explode('|', $this->validation_rules);
        
        foreach ($rules as $rule) {
            if (!$this->validateSingleRule($value, $rule)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate single rule
     */
    private function validateSingleRule($value, string $rule): bool
    {
        if (str_starts_with($rule, 'min:')) {
            $min = (int) substr($rule, 4);
            return strlen((string) $value) >= $min;
        }

        if (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            return strlen((string) $value) <= $max;
        }

        if ($rule === 'required') {
            return !empty($value);
        }

        if ($rule === 'numeric') {
            return is_numeric($value);
        }

        if ($rule === 'email') {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }

        if ($rule === 'url') {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }

    /**
     * Check if setting can be modified
     */
    public function canModify(): bool
    {
        if ($this->is_readonly) {
            return false;
        }

        if ($this->is_system && !$this->userCanModifySystemSettings()) {
            return false;
        }

        return true;
    }

    /**
     * Check if current user can modify system settings
     */
    private function userCanModifySystemSettings(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Check if user is super admin
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        // Check if user has super admin role
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return true;
        }

        // Check if user email is admin
        if ($user->email === 'admin@yukimart.local') {
            return true;
        }

        return false;
    }

    /**
     * Get setting options for select/radio inputs
     */
    public function getSelectOptions(): array
    {
        return $this->options ?? [];
    }

    /**
     * Check if value is in allowed options
     */
    public function isValidOption($value): bool
    {
        $options = $this->getSelectOptions();
        
        if (empty($options)) {
            return true; // No restrictions
        }

        return in_array($value, array_keys($options));
    }

    /**
     * Static helper methods
     */

    /**
     * Get setting value for tenant
     */
    public static function getForTenant(int $tenantId, string $key, $default = null)
    {
        $setting = static::where('tenant_id', $tenantId)
                         ->where('key', $key)
                         ->first();

        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Set setting value for tenant
     */
    public static function setForTenant(int $tenantId, string $key, $value, array $attributes = []): self
    {
        $type = static::determineType($value);
        
        return static::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'key' => $key
            ],
            array_merge([
                'value' => static::processValueForType($value, $type),
                'type' => $type,
                'category' => $attributes['category'] ?? self::CATEGORY_GENERAL,
                'updated_by' => auth()->id()
            ], $attributes)
        );
    }

    /**
     * Determine type from value
     */
    private static function determineType($value): string
    {
        if (is_bool($value)) return self::TYPE_BOOLEAN;
        if (is_int($value)) return self::TYPE_INTEGER;
        if (is_float($value)) return self::TYPE_DECIMAL;
        if (is_array($value)) return self::TYPE_ARRAY;
        return self::TYPE_STRING;
    }

    /**
     * Process value for specific type
     */
    private static function processValueForType($value, string $type): string
    {
        switch ($type) {
            case self::TYPE_BOOLEAN:
                return $value ? '1' : '0';
            case self::TYPE_ARRAY:
            case self::TYPE_JSON:
                return is_string($value) ? $value : json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Scopes
     */

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for system settings
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for editable settings
     */
    public function scopeEditable($query)
    {
        return $query->where('is_readonly', false);
    }

    /**
     * Get setting value (shorthand for current tenant)
     */
    public static function get(string $key, $default = null)
    {
        $tenantId = auth()->user()->tenant_id ?? null;

        if (!$tenantId) {
            return $default;
        }

        return static::getForTenant($tenantId, $key, $default);
    }

    /**
     * Set setting value (shorthand for current tenant)
     */
    public static function set(string $key, $value, string $category = self::CATEGORY_GENERAL): self
    {
        $tenantId = auth()->user()->tenant_id ?? null;

        if (!$tenantId) {
            throw new \Exception('No tenant context available');
        }

        return static::setForTenant($tenantId, $key, $value, ['category' => $category]);
    }
}
