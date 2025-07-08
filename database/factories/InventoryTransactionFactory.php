<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryTransaction>
 */
class InventoryTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InventoryTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactionTypes = [
            InventoryTransaction::TYPE_PURCHASE,
            InventoryTransaction::TYPE_SALE,
            InventoryTransaction::TYPE_ADJUSTMENT,
            InventoryTransaction::TYPE_RETURN,
            InventoryTransaction::TYPE_DAMAGE,
            InventoryTransaction::TYPE_TRANSFER,
            InventoryTransaction::TYPE_RESERVATION,
            InventoryTransaction::TYPE_RELEASE,
            InventoryTransaction::TYPE_INITIAL,
        ];

        $quantityChange = $this->faker->numberBetween(-100, 100);
        $quantityBefore = $this->faker->numberBetween(0, 500);
        $quantityAfter = max(0, $quantityBefore + $quantityChange);
        $unitCost = $this->faker->numberBetween(10000, 500000);

        return [
            'product_id' => Product::factory(),
            'transaction_type' => $this->faker->randomElement($transactionTypes),
            'quantity_before' => $quantityBefore,
            'quantity' => $quantityChange,
            'new_quantity' => $quantityAfter,
            'unit_cost' => $unitCost,
            'total_value' => abs($quantityChange) * $unitCost,
            'reference_type' => null,
            'reference_id' => null,
            'notes' => $this->faker->optional(0.7)->sentence(),
            'location_from' => $this->faker->optional(0.3)->randomElement(['Warehouse A', 'Warehouse B', 'Store Front']),
            'location_to' => $this->faker->optional(0.3)->randomElement(['Warehouse A', 'Warehouse B', 'Store Front']),
            'created_by_user' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that this is a purchase transaction.
     */
    public function purchase(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(10, 100);
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween(0, 200);
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_PURCHASE,
                'quantity' => $quantity,
                'new_quantity' => $quantityBefore + $quantity,
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Purchase from supplier - ' . $this->faker->company,
                'location_to' => $this->faker->randomElement(['Warehouse A', 'Warehouse B']),
            ];
        });
    }

    /**
     * Indicate that this is a sale transaction.
     */
    public function sale(): static
    {
        return $this->state(function (array $attributes) {
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween(10, 200);
            $quantity = $this->faker->numberBetween(1, min(10, $quantityBefore));
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_SALE,
                'quantity' => -$quantity,
                'new_quantity' => max(0, $quantityBefore - $quantity),
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Sale to customer - Order #' . $this->faker->randomNumber(6),
                'location_from' => $this->faker->randomElement(['Warehouse A', 'Store Front']),
            ];
        });
    }

    /**
     * Indicate that this is an adjustment transaction.
     */
    public function adjustment(): static
    {
        return $this->state(function (array $attributes) {
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween(0, 200);
            $adjustment = $this->faker->numberBetween(-50, 50);
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_ADJUSTMENT,
                'quantity' => $adjustment,
                'new_quantity' => max(0, $quantityBefore + $adjustment),
                'unit_cost' => $unitCost,
                'total_value' => abs($adjustment) * $unitCost,
                'notes' => $this->faker->randomElement([
                    'Inventory count adjustment',
                    'Stock correction after audit',
                    'System adjustment',
                    'Manual correction'
                ]),
            ];
        });
    }

    /**
     * Indicate that this is a return transaction.
     */
    public function return(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(1, 10);
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween(0, 200);
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_RETURN,
                'quantity' => $quantity,
                'new_quantity' => $quantityBefore + $quantity,
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Customer return - ' . $this->faker->randomElement([
                    'Defective item',
                    'Wrong size',
                    'Customer changed mind',
                    'Damaged in shipping'
                ]),
                'location_to' => $this->faker->randomElement(['Warehouse A', 'Returns Area']),
            ];
        });
    }

    /**
     * Indicate that this is a damage transaction.
     */
    public function damage(): static
    {
        return $this->state(function (array $attributes) {
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween(5, 200);
            $quantity = $this->faker->numberBetween(1, min(5, $quantityBefore));
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_DAMAGE,
                'quantity' => -$quantity,
                'new_quantity' => max(0, $quantityBefore - $quantity),
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Damaged goods - ' . $this->faker->randomElement([
                    'Water damage',
                    'Dropped during handling',
                    'Manufacturing defect',
                    'Expired product'
                ]),
                'location_from' => $this->faker->randomElement(['Warehouse A', 'Store Front']),
            ];
        });
    }

    /**
     * Indicate that this is a transfer transaction.
     */
    public function transfer(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(1, 20);
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween($quantity, 200);
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER,
                'quantity' => 0, // Transfer doesn't change total quantity
                'new_quantity' => $quantityBefore,
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Transfer between locations',
                'location_from' => $this->faker->randomElement(['Warehouse A', 'Warehouse B']),
                'location_to' => $this->faker->randomElement(['Warehouse B', 'Store Front']),
            ];
        });
    }

    /**
     * Indicate that this is a reservation transaction.
     */
    public function reservation(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(1, 10);
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween($quantity, 200);
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_RESERVATION,
                'quantity' => -$quantity,
                'new_quantity' => max(0, $quantityBefore - $quantity),
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Stock reserved for order #' . $this->faker->randomNumber(6),
            ];
        });
    }

    /**
     * Indicate that this is a release transaction.
     */
    public function release(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(1, 10);
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween(0, 200);
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_RELEASE,
                'quantity' => $quantity,
                'new_quantity' => $quantityBefore + $quantity,
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Released reserved stock for order #' . $this->faker->randomNumber(6),
            ];
        });
    }

    /**
     * Indicate that this is an initial stock transaction.
     */
    public function initial(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(10, 500);
            $unitCost = $this->faker->numberBetween(50000, 500000);

            return [
                'transaction_type' => InventoryTransaction::TYPE_INITIAL,
                'quantity_before' => 0,
                'quantity' => $quantity,
                'new_quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_value' => $quantity * $unitCost,
                'notes' => 'Initial stock setup',
                'location_to' => $this->faker->randomElement(['Warehouse A', 'Warehouse B']),
            ];
        });
    }

    /**
     * Set specific quantity change.
     */
    public function quantityChange(int $change): static
    {
        return $this->state(function (array $attributes) use ($change) {
            $quantityBefore = $attributes['quantity_before'] ?? $this->faker->numberBetween(abs($change), 200);
            $unitCost = $attributes['unit_cost'] ?? $this->faker->numberBetween(50000, 500000);

            return [
                'quantity' => $change,
                'new_quantity' => max(0, $quantityBefore + $change),
                'total_value' => abs($change) * $unitCost,
            ];
        });
    }

    /**
     * Set specific unit cost.
     */
    public function unitCost(int $cost): static
    {
        return $this->state(function (array $attributes) use ($cost) {
            $quantityChange = $attributes['quantity'] ?? $this->faker->numberBetween(-10, 10);

            return [
                'unit_cost' => $cost,
                'total_value' => abs($quantityChange) * $cost,
            ];
        });
    }

    /**
     * For existing product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Set specific notes.
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }

    /**
     * Set reference to another model.
     */
    public function withReference($model): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => get_class($model),
            'reference_id' => $model->id,
        ]);
    }

    /**
     * Set specific locations.
     */
    public function withLocations(?string $from = null, ?string $to = null): static
    {
        return $this->state(fn (array $attributes) => [
            'location_from' => $from,
            'location_to' => $to,
        ]);
    }
}
