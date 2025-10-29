<?php

namespace Database\Factories;

use App\Models\TenantSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantSetting>
 */
class TenantSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TenantSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = $this->faker->randomElement([
            TenantSetting::CATEGORY_GENERAL,
            TenantSetting::CATEGORY_INVENTORY,
            TenantSetting::CATEGORY_SALES,
            TenantSetting::CATEGORY_NOTIFICATIONS,
            TenantSetting::CATEGORY_INTEGRATIONS,
            TenantSetting::CATEGORY_SECURITY,
            TenantSetting::CATEGORY_APPEARANCE,
            TenantSetting::CATEGORY_BILLING
        ]);

        $settingData = $this->getSettingDataByCategory($category);

        return [
            'tenant_id' => Tenant::factory(),
            'category' => $category,
            'key' => $settingData['key'],
            'value' => $settingData['value'],
            'type' => $settingData['type'],
            'label' => $settingData['label'],
            'description' => $settingData['description'],
            'validation_rules' => $settingData['validation_rules'] ?? null,
            'options' => $settingData['options'] ?? null,
            'is_public' => $this->faker->boolean(30),
            'is_readonly' => $this->faker->boolean(10),
            'is_system' => $this->faker->boolean(20),
            'created_by' => User::factory(),
            'updated_by' => null
        ];
    }

    /**
     * Indicate that the setting is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true
        ]);
    }

    /**
     * Indicate that the setting is readonly.
     */
    public function readonly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_readonly' => true
        ]);
    }

    /**
     * Indicate that the setting is system setting.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
            'is_readonly' => true
        ]);
    }

    /**
     * Create general category setting.
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => TenantSetting::CATEGORY_GENERAL
        ]);
    }

    /**
     * Create inventory category setting.
     */
    public function inventory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => TenantSetting::CATEGORY_INVENTORY
        ]);
    }

    /**
     * Create sales category setting.
     */
    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => TenantSetting::CATEGORY_SALES
        ]);
    }

    /**
     * Get setting data by category
     */
    private function getSettingDataByCategory(string $category): array
    {
        $settings = [
            TenantSetting::CATEGORY_GENERAL => [
                [
                    'key' => 'company_name',
                    'value' => $this->faker->company(),
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Tên công ty',
                    'description' => 'Tên công ty hiển thị trên hệ thống',
                    'validation_rules' => 'required|max:255'
                ],
                [
                    'key' => 'timezone',
                    'value' => 'Asia/Ho_Chi_Minh',
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Múi giờ',
                    'description' => 'Múi giờ mặc định của hệ thống',
                    'options' => [
                        'Asia/Ho_Chi_Minh' => 'Việt Nam',
                        'Asia/Bangkok' => 'Thái Lan',
                        'Asia/Singapore' => 'Singapore',
                        'UTC' => 'UTC'
                    ]
                ],
                [
                    'key' => 'language',
                    'value' => 'vi',
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Ngôn ngữ',
                    'description' => 'Ngôn ngữ mặc định của hệ thống',
                    'options' => [
                        'vi' => 'Tiếng Việt',
                        'en' => 'English'
                    ]
                ]
            ],
            TenantSetting::CATEGORY_INVENTORY => [
                [
                    'key' => 'allow_negative_stock',
                    'value' => 'false',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Cho phép tồn kho âm',
                    'description' => 'Cho phép bán khi hết hàng'
                ],
                [
                    'key' => 'auto_generate_sku',
                    'value' => 'true',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Tự động tạo SKU',
                    'description' => 'Tự động tạo mã SKU cho sản phẩm mới'
                ],
                [
                    'key' => 'low_stock_threshold',
                    'value' => '10',
                    'type' => TenantSetting::TYPE_INTEGER,
                    'label' => 'Ngưỡng cảnh báo hết hàng',
                    'description' => 'Số lượng tồn kho tối thiểu để cảnh báo',
                    'validation_rules' => 'required|integer|min:0'
                ]
            ],
            TenantSetting::CATEGORY_SALES => [
                [
                    'key' => 'default_tax_rate',
                    'value' => '10.00',
                    'type' => TenantSetting::TYPE_DECIMAL,
                    'label' => 'Thuế suất mặc định (%)',
                    'description' => 'Thuế suất áp dụng cho đơn hàng',
                    'validation_rules' => 'required|numeric|min:0|max:100'
                ],
                [
                    'key' => 'invoice_prefix',
                    'value' => 'HD',
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Tiền tố hóa đơn',
                    'description' => 'Tiền tố cho số hóa đơn',
                    'validation_rules' => 'required|max:10'
                ],
                [
                    'key' => 'order_prefix',
                    'value' => 'DH',
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Tiền tố đơn hàng',
                    'description' => 'Tiền tố cho số đơn hàng',
                    'validation_rules' => 'required|max:10'
                ]
            ],
            TenantSetting::CATEGORY_NOTIFICATIONS => [
                [
                    'key' => 'email_notifications',
                    'value' => 'true',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Thông báo email',
                    'description' => 'Gửi thông báo qua email'
                ],
                [
                    'key' => 'sms_notifications',
                    'value' => 'false',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Thông báo SMS',
                    'description' => 'Gửi thông báo qua SMS'
                ],
                [
                    'key' => 'low_stock_alerts',
                    'value' => 'true',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Cảnh báo hết hàng',
                    'description' => 'Gửi cảnh báo khi sản phẩm sắp hết'
                ]
            ],
            TenantSetting::CATEGORY_INTEGRATIONS => [
                [
                    'key' => 'shopee_integration',
                    'value' => 'false',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Tích hợp Shopee',
                    'description' => 'Kích hoạt tích hợp với Shopee'
                ],
                [
                    'key' => 'api_access',
                    'value' => 'false',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Truy cập API',
                    'description' => 'Cho phép truy cập qua API'
                ]
            ],
            TenantSetting::CATEGORY_SECURITY => [
                [
                    'key' => 'session_timeout',
                    'value' => '120',
                    'type' => TenantSetting::TYPE_INTEGER,
                    'label' => 'Thời gian hết phiên (phút)',
                    'description' => 'Thời gian tự động đăng xuất',
                    'validation_rules' => 'required|integer|min:5|max:480'
                ],
                [
                    'key' => 'require_2fa',
                    'value' => 'false',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Bắt buộc xác thực 2 yếu tố',
                    'description' => 'Yêu cầu 2FA cho tất cả người dùng'
                ]
            ],
            TenantSetting::CATEGORY_APPEARANCE => [
                [
                    'key' => 'theme_color',
                    'value' => '#009ef7',
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Màu chủ đạo',
                    'description' => 'Màu chủ đạo của giao diện'
                ],
                [
                    'key' => 'sidebar_style',
                    'value' => 'dark',
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Kiểu sidebar',
                    'description' => 'Kiểu hiển thị của sidebar',
                    'options' => [
                        'dark' => 'Tối',
                        'light' => 'Sáng',
                        'primary' => 'Màu chủ đạo'
                    ]
                ]
            ],
            TenantSetting::CATEGORY_BILLING => [
                [
                    'key' => 'auto_billing',
                    'value' => 'true',
                    'type' => TenantSetting::TYPE_BOOLEAN,
                    'label' => 'Tự động thanh toán',
                    'description' => 'Tự động gia hạn và thanh toán'
                ],
                [
                    'key' => 'billing_email',
                    'value' => $this->faker->email(),
                    'type' => TenantSetting::TYPE_STRING,
                    'label' => 'Email thanh toán',
                    'description' => 'Email nhận thông báo thanh toán',
                    'validation_rules' => 'required|email'
                ]
            ]
        ];

        $categorySettings = $settings[$category] ?? $settings[TenantSetting::CATEGORY_GENERAL];
        return $this->faker->randomElement($categorySettings);
    }
}
