<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        $slug = Str::slug($name);
        
        return [
            'name' => $name,
            'slug' => $slug,
            'subdomain' => $slug,
            'domain' => null,
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'tax_number' => $this->faker->numerify('##########'),
            'business_type' => $this->faker->randomElement([
                Tenant::BUSINESS_TYPE_RETAIL,
                Tenant::BUSINESS_TYPE_WHOLESALE,
                Tenant::BUSINESS_TYPE_RESTAURANT,
                Tenant::BUSINESS_TYPE_SERVICE,
                Tenant::BUSINESS_TYPE_MANUFACTURING,
                Tenant::BUSINESS_TYPE_OTHER
            ]),
            'industry' => $this->faker->randomElement([
                'Thời trang', 'Điện tử', 'Thực phẩm', 'Mỹ phẩm', 
                'Nội thất', 'Sách', 'Thể thao', 'Ô tô'
            ]),
            'employee_count' => $this->faker->numberBetween(1, 100),
            'status' => $this->faker->randomElement([
                Tenant::STATUS_ACTIVE,
                Tenant::STATUS_TRIAL,
                Tenant::STATUS_INACTIVE
            ]),
            'plan_type' => $this->faker->randomElement([
                Tenant::PLAN_TRIAL,
                Tenant::PLAN_BASIC,
                Tenant::PLAN_PREMIUM,
                Tenant::PLAN_ENTERPRISE
            ]),
            'trial_ends_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'subscription_starts_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'subscription_ends_at' => $this->faker->optional()->dateTimeBetween('now', '+365 days'),
            'max_users' => $this->faker->randomElement([5, 10, 25, 50, 100]),
            'max_branch_shops' => $this->faker->randomElement([1, 3, 5, 10]),
            'max_products' => $this->faker->randomElement([100, 500, 1000, 5000, 10000]),
            'storage_limit' => $this->faker->randomElement([
                1073741824,    // 1GB
                5368709120,    // 5GB
                10737418240,   // 10GB
                21474836480,   // 20GB
                53687091200    // 50GB
            ]),
            'api_rate_limit' => $this->faker->randomElement([1000, 2500, 5000, 10000]),
            'current_users' => $this->faker->numberBetween(1, 5),
            'current_branch_shops' => $this->faker->numberBetween(1, 3),
            'current_products' => $this->faker->numberBetween(10, 100),
            'current_storage_used' => $this->faker->numberBetween(100000000, 500000000), // 100MB - 500MB
            'settings' => [
                'allow_negative_inventory' => $this->faker->boolean(),
                'auto_generate_sku' => $this->faker->boolean(80),
                'default_tax_rate' => $this->faker->randomFloat(2, 0, 15),
                'invoice_prefix' => $this->faker->randomElement(['HD', 'INV', 'BILL']),
                'order_prefix' => $this->faker->randomElement(['DH', 'ORD', 'SO']),
                'return_prefix' => $this->faker->randomElement(['TH', 'RET', 'RO'])
            ],
            'features' => [
                'inventory_management' => $this->faker->boolean(90),
                'multi_branch' => $this->faker->boolean(70),
                'pos_system' => $this->faker->boolean(80),
                'online_ordering' => $this->faker->boolean(60),
                'reporting' => $this->faker->boolean(85),
                'api_access' => $this->faker->boolean(40),
                'marketplace_integration' => $this->faker->boolean(30),
                'backup_restore' => $this->faker->boolean(50)
            ],
            'timezone' => $this->faker->randomElement([
                'Asia/Ho_Chi_Minh',
                'Asia/Bangkok',
                'Asia/Singapore',
                'UTC'
            ]),
            'currency' => $this->faker->randomElement(['VND', 'USD', 'EUR']),
            'language' => $this->faker->randomElement(['vi', 'en']),
            'logo_url' => $this->faker->optional()->imageUrl(200, 200, 'business'),
            'favicon_url' => $this->faker->optional()->imageUrl(32, 32, 'business'),
            'theme_settings' => [
                'primary_color' => $this->faker->hexColor(),
                'secondary_color' => $this->faker->hexColor(),
                'sidebar_color' => $this->faker->randomElement(['dark', 'light', 'primary']),
                'layout' => $this->faker->randomElement(['fixed', 'fluid'])
            ],
            'custom_css' => null,
            'database_name' => null,
            'integration_settings' => [
                'shopee_enabled' => $this->faker->boolean(20),
                'lazada_enabled' => $this->faker->boolean(15),
                'tiki_enabled' => $this->faker->boolean(10)
            ],
            'notification_settings' => [
                'email_notifications' => $this->faker->boolean(90),
                'sms_notifications' => $this->faker->boolean(60),
                'push_notifications' => $this->faker->boolean(80),
                'low_stock_alerts' => $this->faker->boolean(85)
            ],
            'monthly_fee' => $this->faker->randomFloat(2, 0, 500),
            'setup_fee' => $this->faker->randomFloat(2, 0, 100),
            'billing_cycle' => $this->faker->randomElement([
                Tenant::BILLING_MONTHLY,
                Tenant::BILLING_QUARTERLY,
                Tenant::BILLING_YEARLY
            ]),
            'next_billing_date' => $this->faker->optional()->dateTimeBetween('now', '+90 days'),
            'owner_id' => null, // Will be set after creation
            'created_by' => null,
            'updated_by' => null,
            'last_activity_at' => $this->faker->optional()->dateTimeBetween('-7 days', 'now'),
            'last_activity_ip' => $this->faker->optional()->ipv4(),
            'metadata' => [
                'source' => $this->faker->randomElement(['website', 'referral', 'marketing', 'direct']),
                'notes' => $this->faker->optional()->sentence()
            ]
        ];
    }

    /**
     * Indicate that the tenant is on trial.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Tenant::STATUS_TRIAL,
            'plan_type' => Tenant::PLAN_TRIAL,
            'trial_ends_at' => Carbon::now()->addDays(14),
            'subscription_starts_at' => null,
            'subscription_ends_at' => null,
            'monthly_fee' => 0,
            'setup_fee' => 0
        ]);
    }

    /**
     * Indicate that the tenant is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Tenant::STATUS_ACTIVE,
            'plan_type' => $this->faker->randomElement([
                Tenant::PLAN_BASIC,
                Tenant::PLAN_PREMIUM,
                Tenant::PLAN_ENTERPRISE
            ]),
            'subscription_starts_at' => Carbon::now()->subDays(30),
            'subscription_ends_at' => Carbon::now()->addDays(335),
            'trial_ends_at' => null
        ]);
    }

    /**
     * Indicate that the tenant is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Tenant::STATUS_SUSPENDED,
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'suspended_at' => Carbon::now()->subDays(rand(1, 30)),
                'suspension_reason' => $this->faker->randomElement([
                    'Payment overdue',
                    'Terms violation',
                    'Security concern',
                    'User request'
                ])
            ])
        ]);
    }

    /**
     * Indicate that the tenant is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Tenant::STATUS_EXPIRED,
            'subscription_ends_at' => Carbon::now()->subDays(rand(1, 30)),
            'trial_ends_at' => Carbon::now()->subDays(rand(1, 30))
        ]);
    }

    /**
     * Indicate that the tenant has basic plan.
     */
    public function basicPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => Tenant::PLAN_BASIC,
            'max_users' => 5,
            'max_branch_shops' => 1,
            'max_products' => 500,
            'storage_limit' => 1073741824, // 1GB
            'monthly_fee' => 99000
        ]);
    }

    /**
     * Indicate that the tenant has premium plan.
     */
    public function premiumPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => Tenant::PLAN_PREMIUM,
            'max_users' => 25,
            'max_branch_shops' => 5,
            'max_products' => 2500,
            'storage_limit' => 5368709120, // 5GB
            'monthly_fee' => 299000
        ]);
    }

    /**
     * Indicate that the tenant has enterprise plan.
     */
    public function enterprisePlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => Tenant::PLAN_ENTERPRISE,
            'max_users' => 100,
            'max_branch_shops' => 20,
            'max_products' => 10000,
            'storage_limit' => 21474836480, // 20GB
            'monthly_fee' => 999000
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Tenant $tenant) {
            // Create default settings for the tenant
            $this->createDefaultSettings($tenant);
        });
    }

    /**
     * Create default settings for tenant
     */
    private function createDefaultSettings(Tenant $tenant): void
    {
        $defaultSettings = [
            ['category' => 'general', 'key' => 'company_name', 'value' => $tenant->name, 'type' => 'string'],
            ['category' => 'general', 'key' => 'timezone', 'value' => $tenant->timezone, 'type' => 'string'],
            ['category' => 'general', 'key' => 'currency', 'value' => $tenant->currency, 'type' => 'string'],
            ['category' => 'general', 'key' => 'language', 'value' => $tenant->language, 'type' => 'string'],
            ['category' => 'inventory', 'key' => 'allow_negative_stock', 'value' => 'false', 'type' => 'boolean'],
            ['category' => 'inventory', 'key' => 'auto_generate_sku', 'value' => 'true', 'type' => 'boolean'],
            ['category' => 'sales', 'key' => 'default_tax_rate', 'value' => '10', 'type' => 'decimal'],
            ['category' => 'notifications', 'key' => 'email_notifications', 'value' => 'true', 'type' => 'boolean'],
        ];

        foreach ($defaultSettings as $setting) {
            $tenant->tenantSettings()->create($setting);
        }
    }
}
