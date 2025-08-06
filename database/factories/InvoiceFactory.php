<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Customer;
use App\Models\BranchShop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $users = User::where('status', 'active')->pluck('id')->toArray();
        $customers = Customer::pluck('id')->toArray();
        $branchShops = BranchShop::pluck('id')->toArray();
        
        $subtotal = $this->faker->numberBetween(100000, 5000000);
        $taxRate = $this->faker->randomElement([0, 0.1]); // 0% or 10% tax
        $taxAmount = $subtotal * $taxRate;
        $discountAmount = $this->faker->numberBetween(0, $subtotal * 0.2); // Up to 20% discount
        $totalAmount = $subtotal + $taxAmount - $discountAmount;
        
        return [
            'invoice_number' => 'HD' . date('Ymd') . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_id' => $this->faker->randomElement($customers),
            'branch_shop_id' => $this->faker->randomElement($branchShops),
            'invoice_type' => 'sale',
            'sales_channel' => $this->faker->randomElement(['offline', 'online', 'phone', 'social']),
            'status' => $this->faker->randomElement(['processing', 'completed', 'cancelled']),
            'invoice_date' => $createdAt,
            'due_date' => Carbon::instance($createdAt)->addDays($this->faker->numberBetween(7, 30)),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate * 100, // Convert to percentage
            'tax_amount' => $taxAmount,
            'discount_rate' => ($discountAmount / $subtotal) * 100, // Convert to percentage
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'notes' => $this->faker->optional(0.3)->sentence(),
            'created_by' => $this->faker->randomElement($users),
            'sold_by' => $this->faker->randomElement($users),
            'updated_by' => $this->faker->randomElement($users),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * Indicate that the invoice is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the invoice is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
            'cancelled_by' => User::where('status', 'active')->inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Indicate that the invoice is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    /**
     * Create invoice with specific date range.
     */
    public function createdBetween($startDate, $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween($startDate, $endDate),
            'updated_at' => $this->faker->dateTimeBetween($startDate, $endDate),
            'invoice_date' => $this->faker->dateTimeBetween($startDate, $endDate),
        ]);
    }

    /**
     * Create invoice for current month.
     */
    public function currentMonth(): static
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        return $this->createdBetween($startOfMonth, $endOfMonth);
    }
}
