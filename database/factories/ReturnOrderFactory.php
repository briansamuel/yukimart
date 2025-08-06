<?php

namespace Database\Factories;

use App\Models\ReturnOrder;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Customer;
use App\Services\PrefixGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnOrder>
 */
class ReturnOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReturnOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::where('status', 'active')->pluck('id')->toArray();
        $customers = Customer::pluck('id')->toArray();
        $returnDate = $this->faker->dateTimeBetween('-3 months', 'now');
        
        $returnAmount = $this->faker->numberBetween(50000, 1000000);
        $refundAmount = $this->faker->numberBetween(0, $returnAmount);
        
        $subtotal = $this->faker->numberBetween(50000, 1000000);
        $taxRate = $this->faker->randomElement([0, 10]); // 0% or 10% tax
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        return [
            'return_number' => 'TH' . date('Ymd') . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'invoice_id' => null, // Will be set by forInvoice state
            'customer_id' => $this->faker->randomElement($customers),
            'return_date' => $returnDate,
            'reason' => $this->faker->randomElement([
                'defective',
                'wrong_item',
                'customer_request',
                'damaged',
                'expired',
                'other'
            ]),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'refund_method' => $this->faker->randomElement(['cash', 'card', 'transfer', 'store_credit', 'exchange']),
            'notes' => $this->faker->optional(0.4)->sentence(),
            'reason_detail' => $this->faker->optional(0.3)->sentence(),
            'approved_by' => $this->faker->optional(0.6)->randomElement($users),
            'approved_at' => $this->faker->optional(0.6)->dateTimeBetween($returnDate, '+1 month'),
            'created_by' => $this->faker->randomElement($users),
            'updated_by' => $this->faker->randomElement($users),
            'created_at' => $returnDate,
            'updated_at' => $returnDate,
        ];
    }

    /**
     * Create return order for an invoice.
     */
    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'subtotal' => $this->faker->numberBetween(
                (int)($invoice->total_amount * 0.1),
                (int)($invoice->total_amount * 0.8)
            ),
            'return_date' => $this->faker->dateTimeBetween($invoice->created_at, '+3 months'),
        ]);
    }

    /**
     * Create approved return order.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => $this->faker->dateTimeBetween($attributes['return_date'], '+1 month'),
            'approved_by' => User::where('status', 'active')->inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Create completed return order.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'approved_at' => $this->faker->dateTimeBetween($attributes['return_date'], '+1 month'),
            'approved_by' => User::where('status', 'active')->inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Create pending return order.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Create return order for current month.
     */
    public function currentMonth(): static
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        return $this->state(fn (array $attributes) => [
            'return_date' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
            'created_at' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
            'updated_at' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
        ]);
    }
}
