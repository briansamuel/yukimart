<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->numberBetween(10000, 1000000); // 10k to 1M VND
        $discount = $this->faker->optional(0.3)->numberBetween(0, $unitPrice * $quantity * 0.1); // Up to 10% discount
        $totalPrice = ($quantity * $unitPrice) - ($discount ?? 0);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount ?? 0,
            'total_price' => $totalPrice,
            // 'notes' => $this->faker->optional(0.2)->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the order item has no discount.
     */
    public function withoutDiscount(): static
    {
        return $this->state(function (array $attributes) {
            $totalPrice = $attributes['quantity'] * $attributes['unit_price'];
            return [
                'discount' => 0,
                'total_price' => $totalPrice,
            ];
        });
    }

    /**
     * Indicate that the order item has a high discount.
     */
    public function withHighDiscount(): static
    {
        return $this->state(function (array $attributes) {
            $subtotal = $attributes['quantity'] * $attributes['unit_price'];
            $discount = $this->faker->numberBetween($subtotal * 0.15, $subtotal * 0.3); // 15-30% discount
            $totalPrice = $subtotal - $discount;
            
            return [
                'discount' => $discount,
                'total_price' => $totalPrice,
                // 'notes' => 'Giảm giá đặc biệt cho khách hàng VIP',
            ];
        });
    }

    /**
     * Indicate that the order item has high quantity.
     */
    public function highQuantity(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(10, 50);
            $totalPrice = ($quantity * $attributes['unit_price']) - $attributes['discount'];
            
            return [
                'quantity' => $quantity,
                'total_price' => $totalPrice,
            ];
        });
    }

    /**
     * Indicate that the order item is for a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            // Use product's actual price if available
            $unitPrice = $product->product_price ?? $attributes['unit_price'];
            $totalPrice = ($attributes['quantity'] * $unitPrice) - $attributes['discount'];
            
            return [
                'product_id' => $product->id,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        });
    }

    /**
     * Indicate that the order item is for a specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Indicate that the order item has premium pricing.
     */
    public function premium(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $this->faker->numberBetween(500000, 2000000); // 500k to 2M VND
            $totalPrice = ($attributes['quantity'] * $unitPrice) - $attributes['discount'];
            
            return [
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        });
    }

    /**
     * Indicate that the order item has budget pricing.
     */
    public function budget(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $this->faker->numberBetween(5000, 100000); // 5k to 100k VND
            $totalPrice = ($attributes['quantity'] * $unitPrice) - $attributes['discount'];
            
            return [
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        });
    }
}
