<?php

namespace Database\Factories;

use App\Models\UserSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSettingFactory extends Factory
{
    protected $model = UserSetting::class;

    public function definition()
    {
        $keys = [
            'theme', 'language', 'timezone', 'date_format', 'time_format',
            'items_per_page', 'notifications_enabled', 'email_notifications',
            'dashboard_widgets', 'sidebar_collapsed'
        ];

        $key = $this->faker->randomElement($keys);

        return [
            'user_id' => User::factory(),
            'key' => $key,
            'value' => $this->getValueForKey($key),
            'type' => $this->getTypeForKey($key),
            'description' => $this->getDescriptionForKey($key),
            'is_public' => $this->faker->boolean(20), // 20% chance of being public
            'is_cacheable' => $this->faker->boolean(80), // 80% chance of being cacheable
        ];
    }

    protected function getValueForKey($key)
    {
        return match($key) {
            'theme' => $this->faker->randomElement(['light', 'dark']),
            'language' => $this->faker->randomElement(['vi', 'en']),
            'timezone' => $this->faker->randomElement(['Asia/Ho_Chi_Minh', 'UTC', 'America/New_York']),
            'date_format' => $this->faker->randomElement(['d/m/Y', 'Y-m-d', 'm/d/Y']),
            'time_format' => $this->faker->randomElement(['H:i', 'h:i A']),
            'items_per_page' => $this->faker->randomElement([10, 25, 50, 100]),
            'notifications_enabled' => $this->faker->boolean() ? '1' : '0',
            'email_notifications' => $this->faker->boolean() ? '1' : '0',
            'dashboard_widgets' => json_encode($this->faker->randomElements(
                ['orders', 'revenue', 'inventory', 'customers', 'analytics', 'reports'],
                $this->faker->numberBetween(2, 4)
            )),
            'sidebar_collapsed' => $this->faker->boolean() ? '1' : '0',
            default => $this->faker->word,
        };
    }

    protected function getTypeForKey($key)
    {
        return match($key) {
            'theme', 'language', 'timezone', 'date_format', 'time_format' => 'string',
            'items_per_page' => 'integer',
            'notifications_enabled', 'email_notifications', 'sidebar_collapsed' => 'boolean',
            'dashboard_widgets' => 'json',
            default => 'string',
        };
    }

    protected function getDescriptionForKey($key)
    {
        return match($key) {
            'theme' => 'User interface theme preference',
            'language' => 'User interface language',
            'timezone' => 'User timezone setting',
            'date_format' => 'Preferred date format',
            'time_format' => 'Preferred time format',
            'items_per_page' => 'Number of items to display per page',
            'notifications_enabled' => 'Enable or disable notifications',
            'email_notifications' => 'Enable or disable email notifications',
            'dashboard_widgets' => 'Enabled dashboard widgets',
            'sidebar_collapsed' => 'Sidebar collapsed state',
            default => 'User setting',
        };
    }

    /**
     * Create a theme setting.
     */
    public function theme($theme = 'light')
    {
        return $this->state([
            'key' => 'theme',
            'value' => $theme,
            'type' => 'string',
            'description' => 'User interface theme preference',
        ]);
    }

    /**
     * Create a language setting.
     */
    public function language($language = 'vi')
    {
        return $this->state([
            'key' => 'language',
            'value' => $language,
            'type' => 'string',
            'description' => 'User interface language',
        ]);
    }

    /**
     * Create a boolean setting.
     */
    public function boolean($key, $value = true)
    {
        return $this->state([
            'key' => $key,
            'value' => $value ? '1' : '0',
            'type' => 'boolean',
            'description' => $this->getDescriptionForKey($key),
        ]);
    }

    /**
     * Create an integer setting.
     */
    public function integer($key, $value)
    {
        return $this->state([
            'key' => $key,
            'value' => (string) $value,
            'type' => 'integer',
            'description' => $this->getDescriptionForKey($key),
        ]);
    }

    /**
     * Create a JSON setting.
     */
    public function json($key, $value)
    {
        return $this->state([
            'key' => $key,
            'value' => json_encode($value),
            'type' => 'json',
            'description' => $this->getDescriptionForKey($key),
        ]);
    }

    /**
     * Create a public setting.
     */
    public function public()
    {
        return $this->state([
            'is_public' => true,
        ]);
    }

    /**
     * Create a private setting.
     */
    public function private()
    {
        return $this->state([
            'is_public' => false,
        ]);
    }

    /**
     * Create a cacheable setting.
     */
    public function cacheable()
    {
        return $this->state([
            'is_cacheable' => true,
        ]);
    }

    /**
     * Create a non-cacheable setting.
     */
    public function nonCacheable()
    {
        return $this->state([
            'is_cacheable' => false,
        ]);
    }
}
