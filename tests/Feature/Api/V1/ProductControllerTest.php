<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $testUser;
    protected ProductCategory $testCategory;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testUser = User::factory()->create();
        $this->testCategory = ProductCategory::factory()->create([
            'category_name' => 'Electronics',
            'category_description' => 'Electronic devices and accessories',
        ]);
    }

    /** @test */
    public function can_get_products_list()
    {
        // Create test products
        Product::factory()->count(5)->create([
            'category_id' => $this->testCategory->id,
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'product_name',
                            'sku',
                            'barcode',
                            'sale_price',
                            'stock_quantity',
                            'category' => [
                                'id',
                                'category_name',
                            ],
                            'is_active',
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Products retrieved successfully',
            ]);

        $this->storeApiResponse('products_list_success', $response->json());
    }

    /** @test */
    public function can_search_products()
    {
        Product::factory()->create([
            'product_name' => 'iPhone 15 Pro Max',
            'sku' => 'IPH15PM001',
            'category_id' => $this->testCategory->id,
        ]);

        Product::factory()->create([
            'product_name' => 'Samsung Galaxy S24',
            'sku' => 'SAM24001',
            'category_id' => $this->testCategory->id,
        ]);

        $response = $this->getJson('/api/v1/products/search?q=iPhone');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'product_name',
                        'sku',
                        'sale_price',
                    ]
                ],
                'meta'
            ])
            ->assertJsonFragment([
                'product_name' => 'iPhone 15 Pro Max',
            ]);

        $this->storeApiResponse('products_search_success', $response->json());
    }

    /** @test */
    public function can_find_product_by_barcode()
    {
        $product = Product::factory()->create([
            'product_name' => 'Test Product',
            'barcode' => '1234567890123',
            'category_id' => $this->testCategory->id,
        ]);

        $response = $this->getJson('/api/v1/products/barcode/1234567890123');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'type',
                    'product' => [
                        'id',
                        'product_name',
                        'barcode',
                        'sale_price',
                        'stock_quantity',
                    ],
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'product',
                    'product' => [
                        'barcode' => '1234567890123',
                    ],
                ],
            ]);

        $this->storeApiResponse('products_barcode_success', $response->json());
    }

    /** @test */
    public function can_get_product_details()
    {
        $product = Product::factory()->create([
            'category_id' => $this->testCategory->id,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'product_name',
                    'product_description',
                    'sku',
                    'barcode',
                    'cost_price',
                    'sale_price',
                    'regular_price',
                    'stock_quantity',
                    'reorder_point',
                    'category',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                ],
            ]);

        $this->storeApiResponse('products_detail_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_create_product()
    {
        Sanctum::actingAs($this->testUser);

        $productData = [
            'product_name' => 'New Test Product',
            'product_description' => 'A test product for API testing',
            'sku' => 'TEST001',
            'barcode' => '9876543210987',
            'category_id' => $this->testCategory->id,
            'brand' => 'Test Brand',
            'cost_price' => 100000,
            'sale_price' => 150000,
            'regular_price' => 180000,
            'reorder_point' => 10,
            'weight' => 500,
            'points' => 15,
            'initial_stock' => 100,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'product_name',
                    'sku',
                    'barcode',
                    'sale_price',
                    'stock_quantity',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => [
                    'product_name' => 'New Test Product',
                    'sku' => 'TEST001',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'product_name' => 'New Test Product',
            'sku' => 'TEST001',
        ]);

        $this->storeApiResponse('products_create_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_update_product()
    {
        Sanctum::actingAs($this->testUser);

        $product = Product::factory()->create([
            'category_id' => $this->testCategory->id,
        ]);

        $updateData = [
            'product_name' => 'Updated Product Name',
            'sale_price' => 200000,
            'stock_quantity' => 50,
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'product_name' => 'Updated Product Name',
                    'sale_price' => 200000,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'product_name' => 'Updated Product Name',
            'sale_price' => 200000,
        ]);

        $this->storeApiResponse('products_update_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_delete_product()
    {
        Sanctum::actingAs($this->testUser);

        $product = Product::factory()->create([
            'category_id' => $this->testCategory->id,
        ]);

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);

        $this->storeApiResponse('products_delete_success', $response->json());
    }

    /** @test */
    public function can_get_product_inventory()
    {
        $product = Product::factory()->create([
            'category_id' => $this->testCategory->id,
            'stock_quantity' => 100,
            'reorder_point' => 20,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}/inventory");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'product_id',
                    'current_stock',
                    'reorder_point',
                    'stock_status',
                    'last_updated',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'current_stock' => 100,
                    'reorder_point' => 20,
                    'stock_status' => 'in_stock',
                ],
            ]);

        $this->storeApiResponse('products_inventory_success', $response->json());
    }

    /** @test */
    public function can_filter_products_by_category()
    {
        $category2 = ProductCategory::factory()->create(['category_name' => 'Books']);
        
        Product::factory()->count(3)->create(['category_id' => $this->testCategory->id]);
        Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->getJson("/api/v1/products?category_id={$this->testCategory->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');

        $this->storeApiResponse('products_filter_category_success', $response->json());
    }

    /** @test */
    public function can_filter_products_by_stock_status()
    {
        Product::factory()->create([
            'category_id' => $this->testCategory->id,
            'stock_quantity' => 0,
        ]);

        Product::factory()->create([
            'category_id' => $this->testCategory->id,
            'stock_quantity' => 50,
        ]);

        $response = $this->getJson('/api/v1/products?in_stock=true');

        $response->assertStatus(200);
        
        $products = $response->json('data.data');
        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product['stock_quantity']);
        }

        $this->storeApiResponse('products_filter_stock_success', $response->json());
    }

    /** @test */
    public function returns_404_for_non_existent_product()
    {
        $response = $this->getJson('/api/v1/products/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Product not found',
            ]);

        $this->storeApiResponse('products_not_found_error', $response->json());
    }

    /** @test */
    public function returns_404_for_non_existent_barcode()
    {
        $response = $this->getJson('/api/v1/products/barcode/nonexistentbarcode');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Product not found with barcode: nonexistentbarcode',
            ]);

        $this->storeApiResponse('products_barcode_not_found_error', $response->json());
    }

    /** @test */
    public function unauthenticated_user_cannot_create_product()
    {
        $productData = [
            'product_name' => 'Unauthorized Product',
            'sku' => 'UNAUTH001',
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(401);

        $this->storeApiResponse('products_unauthorized_error', $response->json());
    }

    /**
     * Store API response for Postman collection update
     */
    protected function storeApiResponse(string $key, array $response): void
    {
        $responsesFile = storage_path('testing/api_responses.json');
        
        if (!file_exists(dirname($responsesFile))) {
            mkdir(dirname($responsesFile), 0755, true);
        }

        $responses = [];
        if (file_exists($responsesFile)) {
            $responses = json_decode(file_get_contents($responsesFile), true) ?? [];
        }

        $responses[$key] = [
            'response' => $response,
            'timestamp' => now()->toISOString(),
            'test_class' => static::class,
        ];

        file_put_contents($responsesFile, json_encode($responses, JSON_PRETTY_PRINT));
    }
}
