<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'channels',
        'is_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'quiet_days',
        'custom_settings',
    ];

    protected $casts = [
        'channels' => 'array',
        'quiet_days' => 'array',
        'custom_settings' => 'array',
        'is_enabled' => 'boolean',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i',
    ];

    /**
     * The attributes that should be filled with default values.
     */
    protected $attributes = [
        'is_enabled' => true,
    ];

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($setting) {
            if (empty($setting->channels)) {
                $setting->channels = ['web'];
            }
        });
    }

    /**
     * Get the user that owns the notification setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for enabled settings.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for disabled settings.
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Scope for specific notification type.
     */
    public function scopeForType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope for specific channel.
     */
    public function scopeForChannel($query, $channel)
    {
        return $query->whereJsonContains('channels', $channel);
    }

    /**
     * Check if user should receive notification at current time.
     */
    public function shouldReceiveNotification()
    {
        if (!$this->is_enabled) {
            return false;
        }

        // Check quiet hours
        if ($this->quiet_hours_start && $this->quiet_hours_end) {
            $now = now()->format('H:i');
            $start = $this->quiet_hours_start;
            $end = $this->quiet_hours_end;

            if ($start <= $end) {
                // Same day range
                if ($now >= $start && $now <= $end) {
                    return false;
                }
            } else {
                // Overnight range
                if ($now >= $start || $now <= $end) {
                    return false;
                }
            }
        }

        // Check quiet days
        if ($this->quiet_days && in_array(now()->dayOfWeek, $this->quiet_days)) {
            return false;
        }

        return true;
    }

    /**
     * Get all available notification types (simplified and cleaned).
     */
    public static function getAvailableTypes()
    {
        return [
            // === KHÁCH HÀNG ===
            'customer_birthday' => [
                'name' => 'Sinh nhật khách hàng',
                'description' => 'Thông báo khi có khách hàng sinh nhật',
                'category' => 'customers',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => true,
                'custom_settings' => [
                    'birthday_days_before' => [
                        'type' => 'select',
                        'label' => 'Thông báo trước',
                        'options' => [
                            1 => '1 ngày',
                            2 => '2 ngày',
                            3 => '3 ngày',
                            7 => '1 tuần',
                            14 => '2 tuần',
                            30 => '1 tháng'
                        ],
                        'default' => 2
                    ]
                ]
            ],

            // === SỔ QUỸ ===
            'payment_receipt' => [
                'name' => 'Phiếu thu',
                'description' => 'Hiển thị thông báo khi lập phiếu thu hoặc thanh toán công nợ cho KH thành công',
                'category' => 'cashbook',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'payment_voucher' => [
                'name' => 'Phiếu chi',
                'description' => 'Hiển thị thông báo khi lập phiếu chi hoặc thanh toán công nợ cho NCC, ĐTGH thành công',
                'category' => 'cashbook',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],

            // === HÀNG HÓA ===
            'inventory_update' => [
                'name' => 'Cập nhật tồn kho hàng hóa',
                'description' => 'Hiển thị thông báo khi cập nhật tồn kho của hàng hóa',
                'category' => 'inventory',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'inventory_check' => [
                'name' => 'Kiểm kho',
                'description' => 'Hiển thị thông báo khi hoàn thành phiếu kiểm kho',
                'category' => 'inventory',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'inventory_alert' => [
                'name' => 'Cảnh báo tồn kho',
                'description' => 'Hiển thị thông báo về cảnh báo tồn kho',
                'category' => 'inventory',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],
            'inventory_import' => [
                'name' => 'Nhập kho',
                'description' => 'Thông báo khi có giao dịch nhập kho',
                'category' => 'inventory',
                'default_channels' => ['web'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],
            'inventory_export' => [
                'name' => 'Xuất kho',
                'description' => 'Thông báo khi có giao dịch xuất kho',
                'category' => 'inventory',
                'default_channels' => ['web'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],
            'inventory_low_stock' => [
                'name' => 'Sắp hết hàng',
                'description' => 'Thông báo khi sản phẩm sắp hết hàng',
                'category' => 'inventory',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],
            'inventory_out_of_stock' => [
                'name' => 'Hết hàng',
                'description' => 'Thông báo khi sản phẩm hết hàng',
                'category' => 'inventory',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],

            // === GIAO DỊCH ===
            'order_complete' => [
                'name' => 'Hoàn thành đặt hàng',
                'description' => 'Thông báo khi đơn hàng được hoàn thành',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'order_cancel' => [
                'name' => 'Hủy đặt hàng',
                'description' => 'Thông báo khi đơn hàng bị hủy',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'invoice_complete' => [
                'name' => 'Hoàn thành hóa đơn',
                'description' => 'Thông báo khi hóa đơn được hoàn thành',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'invoice_cancel' => [
                'name' => 'Hủy hóa đơn',
                'description' => 'Thông báo khi hóa đơn bị hủy',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'shipping_order' => [
                'name' => 'Vận đơn',
                'description' => 'Thông báo về vận đơn',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'return_order' => [
                'name' => 'Trả hàng',
                'description' => 'Hiển thị thông báo khi có phiếu trả hàng mới',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'import_goods' => [
                'name' => 'Nhập hàng',
                'description' => 'Hiển thị thông báo khi hoàn thành phiếu nhập hàng',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'return_import' => [
                'name' => 'Trả hàng nhập',
                'description' => 'Hiển thị thông báo khi hoàn thành phiếu trả hàng nhập',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'transfer_complete' => [
                'name' => 'Hoàn thành chuyển hàng, nhận hàng',
                'description' => 'Thông báo về chuyển hàng',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'transfer_cancel' => [
                'name' => 'Hủy chuyển hàng',
                'description' => 'Thông báo khi chuyển hàng bị hủy',
                'category' => 'transactions',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],

            // === ĐƠN HÀNG ===
            'order_new' => [
                'name' => 'Đơn hàng mới',
                'description' => 'Thông báo khi có đơn hàng mới được tạo',
                'category' => 'orders',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],
            'order_update' => [
                'name' => 'Cập nhật đơn hàng',
                'description' => 'Thông báo khi đơn hàng được cập nhật',
                'category' => 'orders',
                'default_channels' => ['web'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'order_completed' => [
                'name' => 'Hoàn thành đơn hàng',
                'description' => 'Thông báo khi đơn hàng được hoàn thành',
                'category' => 'orders',
                'default_channels' => ['web'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],

            // === HÓA ĐƠN ===
            'invoice_new' => [
                'name' => 'Hóa đơn mới',
                'description' => 'Thông báo khi có hóa đơn mới được tạo',
                'category' => 'invoices',
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],
            'invoice_payment' => [
                'name' => 'Thanh toán hóa đơn',
                'description' => 'Thông báo khi hóa đơn được thanh toán',
                'category' => 'invoices',
                'default_channels' => ['web'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],

            // === SẢN PHẨM ===
            'product_new' => [
                'name' => 'Sản phẩm mới',
                'description' => 'Thông báo khi có sản phẩm mới được tạo',
                'category' => 'products',
                'default_channels' => ['web'],
                'default_enabled' => true,
                'supports_summary' => true,
            ],
            'product_update' => [
                'name' => 'Cập nhật sản phẩm',
                'description' => 'Thông báo khi sản phẩm được cập nhật',
                'category' => 'products',
                'default_channels' => ['web'],
                'default_enabled' => false,
                'supports_summary' => false,
            ],

            // === NGƯỜI DÙNG ===
            'user_login' => [
                'name' => 'Đăng nhập',
                'description' => 'Thông báo khi có người dùng đăng nhập',
                'category' => 'users',
                'default_channels' => ['web'],
                'default_enabled' => false,
                'supports_summary' => true,
            ],

            // === HỆ THỐNG ===
            'system_update' => [
                'name' => 'Cập nhật hệ thống',
                'description' => 'Thông báo về cập nhật hệ thống',
                'category' => 'system',
                'default_channels' => ['web', 'email'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
            'system_maintenance' => [
                'name' => 'Bảo trì hệ thống',
                'description' => 'Thông báo về bảo trì hệ thống',
                'category' => 'system',
                'default_channels' => ['web', 'email'],
                'default_enabled' => true,
                'supports_summary' => false,
            ],
        ];
    }

    /**
     * Get notification categories.
     */
    public static function getCategories()
    {
        return [
            'customers' => 'Khách hàng',
            'cashbook' => 'Sổ quỹ',
            'inventory' => 'Hàng hóa',
            'transactions' => 'Giao dịch',
            'orders' => 'Đơn hàng',
            'invoices' => 'Hóa đơn',
            'products' => 'Sản phẩm',
            'users' => 'Người dùng',
            'system' => 'Hệ thống',
        ];
    }

    /**
     * Get available notification channels.
     */
    public static function getAvailableChannels()
    {
        return [
            'web' => 'Thông báo web',
            'fcm' => 'Thông báo đẩy',
            'email' => 'Email',
            'sms' => 'SMS',
            'phone' => 'Điện thoại',
        ];
    }

    /**
     * Get default settings for a user.
     */
    public static function getDefaultSettings($userId)
    {
        $settings = [];
        $availableTypes = self::getAvailableTypes();

        foreach ($availableTypes as $type => $config) {
            // Prepare custom settings with default values
            $customSettings = [];
            if (isset($config['custom_settings'])) {
                foreach ($config['custom_settings'] as $key => $setting) {
                    $customSettings[$key] = $setting['default'] ?? null;
                }
            }

            $settings[] = [
                'user_id' => $userId,
                'notification_type' => $type,
                'channels' => $config['default_channels'],
                'is_enabled' => $config['default_enabled'],
                'custom_settings' => !empty($customSettings) ? $customSettings : null,
            ];
        }

        return $settings;
    }

    /**
     * Create default settings for a user.
     */
    public static function createDefaultForUser($userId)
    {
        $defaultSettings = self::getDefaultSettings($userId);

        foreach ($defaultSettings as $setting) {
            self::firstOrCreate(
                [
                    'user_id' => $setting['user_id'],
                    'notification_type' => $setting['notification_type'],
                ],
                $setting
            );
        }
    }

    /**
     * Get user's notification settings grouped by category.
     */
    public static function getUserSettingsByCategory($userId)
    {
        $settings = self::where('user_id', $userId)->get()->keyBy('notification_type');
        $availableTypes = self::getAvailableTypes();
        $categories = self::getCategories();
        $result = [];

        foreach ($categories as $categoryKey => $categoryName) {
            $result[$categoryKey] = [
                'name' => $categoryName,
                'types' => []
            ];
        }

        foreach ($availableTypes as $type => $config) {
            $setting = $settings->get($type);
            $result[$config['category']]['types'][$type] = [
                'config' => $config,
                'setting' => $setting ? [
                    'is_enabled' => $setting->is_enabled,
                    'channels' => $setting->channels,
                    'quiet_hours_start' => $setting->quiet_hours_start,
                    'quiet_hours_end' => $setting->quiet_hours_end,
                    'quiet_days' => $setting->quiet_days,
                    'custom_settings' => $setting->custom_settings,
                ] : null
            ];
        }

        return $result;
    }

    /**
     * Check if user should receive notification for a specific type.
     */
    public static function shouldUserReceiveNotification($userId, $notificationType, $channel = 'web')
    {
        $setting = self::where('user_id', $userId)
            ->where('notification_type', $notificationType)
            ->first();

        if (!$setting) {
            // If no setting exists, check default config
            $availableTypes = self::getAvailableTypes();
            if (isset($availableTypes[$notificationType])) {
                $config = $availableTypes[$notificationType];
                return $config['default_enabled'] && in_array($channel, $config['default_channels']);
            }
            return false;
        }

        // Check if enabled and channel is supported
        if (!$setting->is_enabled || !in_array($channel, $setting->channels)) {
            return false;
        }

        // Check quiet hours and days
        return $setting->shouldReceiveNotification();
    }

    /**
     * Update user notification settings with performance optimizations.
     */
    public static function updateUserSettings($userId, $settings)
    {
        // Prepare bulk data for better performance
        $updateData = [];
        $createData = [];

        // Get existing settings for this user
        $existingSettings = self::where('user_id', $userId)
            ->pluck('id', 'notification_type')
            ->toArray();

        foreach ($settings as $type => $config) {
            $data = [
                'is_enabled' => $config['is_enabled'] ?? true,
                'channels' => $config['channels'] ?? ['web'],
                'quiet_hours_start' => $config['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $config['quiet_hours_end'] ?? null,
                'quiet_days' => $config['quiet_days'] ?? null,
                'custom_settings' => $config['custom_settings'] ?? null,
                'updated_at' => now(),
            ];

            if (isset($existingSettings[$type])) {
                // Update existing setting
                $updateData[$existingSettings[$type]] = $data;
            } else {
                // Create new setting
                $createData[] = array_merge($data, [
                    'user_id' => $userId,
                    'notification_type' => $type,
                    'created_at' => now(),
                ]);
            }
        }

        // Perform bulk operations for better performance
        if (!empty($updateData)) {
            foreach ($updateData as $id => $data) {
                self::where('id', $id)->update($data);
            }
        }

        if (!empty($createData)) {
            self::insert($createData);
        }
    }
}
