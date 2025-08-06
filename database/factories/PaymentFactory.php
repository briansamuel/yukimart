<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use App\Models\BankAccount;
use App\Services\PrefixGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::where('status', 'active')->pluck('id')->toArray();
        $bankAccounts = BankAccount::pluck('id')->toArray();
        $paymentDate = $this->faker->dateTimeBetween('-6 months', 'now');
        
        return [
            'payment_number' => 'TTH' . $paymentDate->format('Ymd') . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'payment_type' => $this->faker->randomElement(['receipt', 'payment']),
            'reference_type' => 'manual', // Default reference type
            'amount' => $this->faker->numberBetween(50000, 2000000),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'transfer', 'check', 'points', 'other']),
            'payment_date' => $paymentDate,
            'status' => 'completed',
            'actual_amount' => null, // Will be set same as amount
            'description' => $this->faker->sentence(),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'bank_account_id' => $this->faker->optional(0.7)->randomElement($bankAccounts),
            'created_by' => $this->faker->randomElement($users),
            'updated_by' => $this->faker->randomElement($users),
            'created_at' => $paymentDate,
            'updated_at' => $paymentDate,
        ];
    }

    /**
     * Create payment for an invoice.
     */
    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_number' => 'TTHD' . $invoice->id . '_' . $this->faker->unique()->numberBetween(1, 999),
            'payment_type' => 'receipt',
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'amount' => $this->faker->numberBetween(
                (int)($invoice->total_amount * 0.1),
                (int)$invoice->total_amount
            ),
            'payment_date' => $this->faker->dateTimeBetween($invoice->created_at, '+1 month'),
            'description' => "Thanh toán cho hóa đơn {$invoice->invoice_number}",
            'actual_amount' => null, // Will be set same as amount
        ]);
    }

    /**
     * Create receipt payment.
     */
    public function receipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'receipt',
        ]);
    }

    /**
     * Create disbursement payment.
     */
    public function disbursement(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'payment',
        ]);
    }

    /**
     * Create payment with cash method.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
            'bank_account_id' => null,
        ]);
    }

    /**
     * Create payment with bank transfer method.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'transfer',
        ]);
    }

    /**
     * Create payment for current month.
     */
    public function currentMonth(): static
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        return $this->state(fn (array $attributes) => [
            'payment_date' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
            'created_at' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
            'updated_at' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
        ]);
    }
}
