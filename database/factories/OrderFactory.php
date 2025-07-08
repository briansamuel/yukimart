<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['processing', 'completed', 'cancelled', 'failed'];
        $deliveryStatuses = ['pending', 'picking', 'delivering', 'delivered', 'returning', 'returned'];
        $channels = ['direct', 'online', 'pos', 'other'];
        $paymentMethods = ['cash', 'card', 'transfer', 'cod', 'e_wallet', 'installment', 'credit', 'voucher', 'points', 'mixed'];

        $status = $this->faker->randomElement($statuses);
        $deliveryStatus = $this->faker->randomElement($deliveryStatuses);
        
        // Adjust delivery status based on order status
        if ($status === 'cancelled' || $status === 'failed') {
            $deliveryStatus = $this->faker->randomElement(['pending', 'returned']);
        } elseif ($status === 'completed') {
            $deliveryStatus = $this->faker->randomElement(['delivered', 'returned']);
        }

        $totalAmount = $this->faker->numberBetween(50000, 5000000); // 50k to 5M VND
        $discountAmount = $this->faker->optional(0.3)->numberBetween(0, $totalAmount * 0.2); // Up to 20% discount
        $finalAmount = $totalAmount - ($discountAmount ?? 0);

        $paymentMethod = $this->faker->randomElement($paymentMethods);

        // Amount paid depends on status
        $amountPaid = 0;
        if ($status === 'completed') {
            $amountPaid = $finalAmount; // Fully paid
        } elseif ($status === 'processing') {
            $amountPaid = $this->faker->optional(0.6)->numberBetween(0, $finalAmount) ?? 0; // Partially paid or not paid
        }

        // Determine payment status based on amount paid
        $paymentStatus = 'unpaid';
        if ($amountPaid > 0) {
            if ($amountPaid < $finalAmount) {
                $paymentStatus = 'partial';
            } elseif ($amountPaid == $finalAmount) {
                $paymentStatus = 'paid';
            } else {
                $paymentStatus = 'overpaid';
            }
        }

        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'order_code' => $this->generateOrderCode($createdAt),
            'customer_id' => Customer::factory(),
            'branch_shop_id' => BranchShop::inRandomOrder()->first()?->id ?? 1,
            'status' => $status,
            'delivery_status' => $deliveryStatus,
            'channel' => $this->faker->randomElement($channels),
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'payment_reference' => $paymentStatus !== 'unpaid' ? $this->faker->optional(0.6)->regexify('[A-Z0-9]{10,15}') : null,
            'payment_date' => $paymentStatus !== 'unpaid' ? $this->faker->dateTimeBetween($createdAt, 'now') : null,
            'payment_notes' => $this->faker->optional(0.3)->sentence(),
            'due_date' => $paymentMethod === 'credit' ? $this->faker->dateTimeBetween('now', '+30 days') : null,
            'total_quantity' => $this->faker->numberBetween(1, 10),
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount ?? 0,
            'final_amount' => $finalAmount,
            'amount_paid' => $amountPaid ?: 0,
            'note' => $this->faker->optional(0.4)->sentence(),
            'internal_notes' => $this->faker->optional(0.2)->sentence(),
            'created_by' => User::inRandomOrder()->first()?->id ?? 1,
            'sold_by' => User::inRandomOrder()->first()?->id ?? 1,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * Generate order code based on date.
     */
    private function generateOrderCode($date)
    {
        $prefix = 'ORD';

        // Handle both DateTime objects and string dates
        if ($date instanceof \DateTime) {
            $dateStr = $date->format('Ymd');
        } elseif (is_string($date)) {
            $dateStr = date('Ymd', strtotime($date));
        } else {
            $dateStr = date('Ymd'); // Fallback to current date
        }

        $randomNumber = str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . $dateStr . $randomNumber;
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'delivery_status' => $this->faker->randomElement(['delivered', 'returned']),
                'amount_paid' => $attributes['final_amount'], // Fully paid
                'payment_status' => 'paid',
                'payment_date' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
            ];
        });
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(function (array $attributes) {
            $amountPaid = $this->faker->optional(0.5)->numberBetween(0, $attributes['final_amount']);
            $paymentStatus = $amountPaid > 0 ? ($amountPaid < $attributes['final_amount'] ? 'partial' : 'paid') : 'unpaid';

            return [
                'status' => 'processing',
                'delivery_status' => $this->faker->randomElement(['pending', 'picking', 'delivering']),
                'amount_paid' => $amountPaid,
                'payment_status' => $paymentStatus,
                'payment_date' => $amountPaid > 0 ? $this->faker->dateTimeBetween($attributes['created_at'], 'now') : null,
            ];
        });
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'delivery_status' => 'pending',
            'amount_paid' => 0,
            'payment_status' => 'unpaid',
            'payment_date' => null,
            'internal_notes' => 'Đơn hàng bị hủy do khách hàng yêu cầu',
        ]);
    }

    /**
     * Indicate that the order is from online channel.
     */
    public function online(): static
    {
        return $this->state(fn () => [
            'channel' => 'online',
            'payment_method' => $this->faker->randomElement(['card', 'transfer', 'cod']),
        ]);
    }

    /**
     * Indicate that the order is from direct channel.
     */
    public function direct(): static
    {
        return $this->state(fn () => [
            'channel' => 'direct',
            'payment_method' => $this->faker->randomElement(['cash', 'card']),
        ]);
    }

    /**
     * Indicate that the order is from POS.
     */
    public function pos(): static
    {
        return $this->state(fn () => [
            'channel' => 'pos',
            'payment_method' => $this->faker->randomElement(['cash', 'card']),
        ]);
    }

    /**
     * Indicate that the order uses credit payment.
     */
    public function credit(): static
    {
        return $this->state(fn () => [
            'payment_method' => 'credit',
            'payment_status' => $this->faker->randomElement(['unpaid', 'partial']),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'amount_paid' => 0,
        ]);
    }

    /**
     * Indicate that the order is fully paid.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'paid',
                'amount_paid' => $attributes['final_amount'],
                'payment_date' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
                'payment_reference' => $this->faker->regexify('[A-Z0-9]{10,15}'),
            ];
        });
    }

    /**
     * Indicate that the order is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn () => [
            'payment_status' => 'unpaid',
            'amount_paid' => 0,
            'payment_date' => null,
            'payment_reference' => null,
        ]);
    }

    /**
     * Indicate that the order has high value.
     */
    public function highValue(): static
    {
        return $this->state(function () {
            $totalAmount = $this->faker->numberBetween(2000000, 10000000); // 2M to 10M VND
            $discountAmount = $this->faker->optional(0.5)->numberBetween(0, $totalAmount * 0.15);
            $finalAmount = $totalAmount - ($discountAmount ?? 0);

            return [
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount ?? 0,
                'final_amount' => $finalAmount,
                'total_quantity' => $this->faker->numberBetween(5, 20),
            ];
        });
    }

    /**
     * Indicate that the order is recent (within last month).
     */
    public function recent(): static
    {
        return $this->state(function () {
            $createdAt = $this->faker->dateTimeBetween('-1 month', 'now');
            return [
                'order_code' => $this->generateOrderCode($createdAt),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        });
    }
}
