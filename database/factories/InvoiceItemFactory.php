<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = Product::pluck('id')->toArray();
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->numberBetween(10000, 500000);
        $subtotal = $quantity * $unitPrice;
        $discountRate = $this->faker->numberBetween(0, 10); // 0-10% discount
        $discountAmount = $subtotal * ($discountRate / 100);
        $taxRate = $this->faker->randomElement([0, 10]); // 0% or 10% tax
        $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
        $lineTotal = $subtotal - $discountAmount + $taxAmount;

        return [
            'invoice_id' => null, // Will be set when creating
            'product_id' => $this->faker->randomElement($products),
            'product_name' => $this->faker->words(3, true),
            'product_sku' => 'SKU' . $this->faker->unique()->numberBetween(10000, 99999),
            'product_description' => $this->faker->optional(0.3)->sentence(),
            'quantity' => $quantity,
            'unit' => $this->faker->randomElement(['cái', 'kg', 'lít', 'hộp', 'gói']),
            'unit_price' => $unitPrice,
            'discount_rate' => $discountRate,
            'discount_amount' => $discountAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'notes' => $this->faker->optional(0.2)->sentence(),
            'sort_order' => 0,
        ];
    }

    /**
     * Create item for specific invoice.
     */
    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoice->id,
        ]);
    }
}
