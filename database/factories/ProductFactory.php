<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productName = $this->faker->words(3, true);
        $productSlug = Str::slug($productName);
        $costPrice = $this->faker->numberBetween(50000, 500000);
        $salePrice = $costPrice + $this->faker->numberBetween(20000, 200000);
        
        return [
            'product_name' => ucwords($productName),
            'product_slug' => $productSlug,
            'product_description' => $this->faker->paragraph(3),
            'product_content' => $this->faker->paragraphs(5, true),
            'sku' => 'SKU-' . strtoupper($this->faker->unique()->bothify('??###')),
            'barcode' => $this->faker->unique()->ean13(),
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'product_status' => $this->faker->randomElement(['publish', 'draft', 'pending']),
            'product_type' => $this->faker->randomElement(['simple', 'variable', 'grouped']),
            'brand' => $this->faker->randomElement([
                'Samsung', 'Apple', 'Sony', 'LG', 'Xiaomi', 
                'Canon', 'Nike', 'Adidas', 'Uniqlo', 'Zara'
            ]),
            'weight' => $this->faker->numberBetween(100, 5000), // grams
            'points' => $this->faker->numberBetween(10, 100),
            'reorder_point' => $this->faker->numberBetween(5, 50),
            'product_feature' => $this->faker->boolean(30), // 30% chance of being featured
            'product_thumbnail' => $this->faker->imageUrl(400, 400, 'products', true),

            // User tracking
            'created_by_user' => User::factory(),
            'updated_by_user' => User::factory(),
            
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the product is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_status' => 'publish',
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_feature' => true,
        ]);
    }

    /**
     * Indicate that the product is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_status' => 'draft',
        ]);
    }

    /**
     * Indicate that the product is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the product is a simple product.
     */
    public function simple(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'simple',
        ]);
    }

    /**
     * Indicate that the product is a variable product.
     */
    public function variable(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'variable',
        ]);
    }



    /**
     * Set specific price range.
     */
    public function priceRange(int $minCost, int $maxCost): static
    {
        return $this->state(function (array $attributes) use ($minCost, $maxCost) {
            $costPrice = $this->faker->numberBetween($minCost, $maxCost);
            $salePrice = $costPrice + $this->faker->numberBetween(10000, 100000);

            return [
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
            ];
        });
    }

    /**
     * Set specific brand.
     */
    public function brand(string $brand): static
    {
        return $this->state(fn (array $attributes) => [
            'brand' => $brand,
        ]);
    }

    /**
     * Electronics category products.
     */
    public function electronics(): static
    {
        return $this->state(function (array $attributes) {
            $products = [
                'Smartphone Pro Max', 'Wireless Headphones', 'Smart Watch Series',
                'Tablet Ultra', 'Gaming Laptop', 'Bluetooth Speaker',
                'Digital Camera', 'Smart TV 4K', 'Wireless Charger'
            ];
            
            $productName = $this->faker->randomElement($products);
            
            return [
                'product_name' => $productName,
                'product_slug' => Str::slug($productName),
                'brand' => $this->faker->randomElement(['Samsung', 'Apple', 'Sony', 'LG', 'Xiaomi']),
                'product_type' => 'simple',
            ];
        });
    }

    /**
     * Fashion category products.
     */
    public function fashion(): static
    {
        return $this->state(function (array $attributes) {
            $products = [
                'Premium T-Shirt', 'Designer Jeans', 'Running Shoes',
                'Casual Jacket', 'Summer Dress', 'Sports Sneakers',
                'Formal Shirt', 'Winter Coat', 'Fashion Handbag'
            ];
            
            $productName = $this->faker->randomElement($products);
            
            return [
                'product_name' => $productName,
                'product_slug' => Str::slug($productName),
                'brand' => $this->faker->randomElement(['Nike', 'Adidas', 'Uniqlo', 'Zara', 'H&M']),
                'product_type' => 'variable',
            ];
        });
    }
}
