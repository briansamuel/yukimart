<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = ['created', 'updated', 'deleted', 'viewed', 'login', 'logout', 'exported', 'imported'];
        $modelTypes = [
            'App\Models\Product',
            'App\Models\Order', 
            'App\Models\Customer',
            'App\Models\User',
            'App\Models\Inventory',
            'App\Models\Supplier'
        ];
        
        $action = $this->faker->randomElement($actions);
        $modelType = $this->faker->randomElement($modelTypes);
        $modelId = $this->faker->numberBetween(1, 100);
        
        // Generate realistic old and new values based on action
        $oldValues = null;
        $newValues = null;
        
        if ($action === 'created') {
            $newValues = $this->generateModelData($modelType);
        } elseif ($action === 'updated') {
            $oldValues = $this->generateModelData($modelType);
            $newValues = $this->generateModelData($modelType);
        } elseif ($action === 'deleted') {
            $oldValues = $this->generateModelData($modelType);
        }

        return [
            'user_id' => User::factory(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'url' => $this->faker->url(),
            'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'description' => $this->generateDescription($action, $modelType),
            'tags' => $this->faker->randomElements(['admin', 'user', 'system', 'api', 'web'], $this->faker->numberBetween(1, 3)),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Generate sample model data based on model type
     */
    private function generateModelData(string $modelType): array
    {
        switch ($modelType) {
            case 'App\Models\Product':
                return [
                    'name' => $this->faker->words(3, true),
                    'price' => $this->faker->randomFloat(2, 10000, 1000000),
                    'stock_quantity' => $this->faker->numberBetween(0, 100),
                    'status' => $this->faker->randomElement(['active', 'inactive']),
                ];
                
            case 'App\Models\Order':
                return [
                    'customer_id' => $this->faker->numberBetween(1, 50),
                    'total_amount' => $this->faker->randomFloat(2, 50000, 5000000),
                    'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
                    'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
                ];
                
            case 'App\Models\Customer':
                return [
                    'name' => $this->faker->name(),
                    'email' => $this->faker->email(),
                    'phone' => $this->faker->phoneNumber(),
                    'status' => $this->faker->randomElement(['active', 'inactive']),
                ];
                
            case 'App\Models\User':
                return [
                    'name' => $this->faker->name(),
                    'email' => $this->faker->email(),
                    'status' => $this->faker->randomElement(['active', 'inactive']),
                ];
                
            default:
                return [
                    'name' => $this->faker->words(2, true),
                    'status' => $this->faker->randomElement(['active', 'inactive']),
                ];
        }
    }

    /**
     * Generate description based on action and model type
     */
    private function generateDescription(string $action, string $modelType): string
    {
        $modelName = class_basename($modelType);
        
        $descriptions = [
            'created' => "Tạo mới {$modelName}",
            'updated' => "Cập nhật {$modelName}",
            'deleted' => "Xóa {$modelName}",
            'viewed' => "Xem {$modelName}",
            'login' => "Đăng nhập vào hệ thống",
            'logout' => "Đăng xuất khỏi hệ thống",
            'exported' => "Xuất dữ liệu {$modelName}",
            'imported' => "Nhập dữ liệu {$modelName}",
        ];

        return $descriptions[$action] ?? "Thao tác {$action} trên {$modelName}";
    }

    /**
     * Create login audit log
     */
    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => null,
            'description' => 'Đăng nhập vào hệ thống',
            'method' => 'POST',
        ]);
    }

    /**
     * Create logout audit log
     */
    public function logout(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'logout',
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => null,
            'description' => 'Đăng xuất khỏi hệ thống',
            'method' => 'POST',
        ]);
    }

    /**
     * Create product-related audit log
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => 'App\Models\Product',
            'model_id' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Create order-related audit log
     */
    public function order(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => 'App\Models\Order',
            'model_id' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Create customer-related audit log
     */
    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => 'App\Models\Customer',
            'model_id' => $this->faker->numberBetween(1, 50),
        ]);
    }

    /**
     * Create recent audit log (within last 7 days)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create audit log for specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
