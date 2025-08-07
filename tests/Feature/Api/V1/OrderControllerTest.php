<?php

namespace Tests\Feature\Api\V1;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $testUser;
    protected Customer $testCustomer;
    protected Product $testProduct;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testUser = User::factory()->create();
        $this->testCustomer = Customer::factory()->create();
        
        $category = ProductCategory::factory()->create();
        $this->testProduct = Product::factory()->create([
            'category_id' => $category->id,
            'sale_price' => 100000,
            'stock_quantity' => 50,
        ]);
    }

    /** @test */
    public function authenticated_user_can_get_orders_list()
    {
        Sanctum::actingAs($this->testUser);

        // Create test orders
        Order::factory()->count(3)->create([
            'customer_id' => $this->testCustomer->id,
        ]);

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'order_code',
                            'customer_id',
                            'status',
                            'delivery_status',
                            'payment_status',
                            'total_amount',
                            'final_amount',
                            'created_at',
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
                'message' => 'Orders retrieved successfully',
            ]);

        $this->storeApiResponse('orders_list_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_create_order()
    {
        Sanctum::actingAs($this->testUser);

        $orderData = [
            'customer_id' => $this->testCustomer->id,
            'note' => 'Test order for API testing',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'discount_amount' => 10000,
            'items' => [
                [
                    'product_id' => $this->testProduct->id,
                    'quantity' => 2,
                    'unit_price' => 100000,
                    'discount' => 5000,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'order_code',
                    'customer_id',
                    'status',
                    'total_amount',
                    'final_amount',
                    'items' => [
                        '*' => [
                            'product_id',
                            'quantity',
                            'unit_price',
                            'total_price',
                        ]
                    ],
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'customer_id' => $this->testCustomer->id,
                    'status' => 'draft',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->testCustomer->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->testProduct->id,
            'quantity' => 2,
            'unit_price' => 100000,
        ]);

        $this->storeApiResponse('orders_create_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_get_order_details()
    {
        Sanctum::actingAs($this->testUser);

        $order = Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'order_code',
                    'customer' => [
                        'id',
                        'name',
                        'phone',
                        'email',
                    ],
                    'status',
                    'delivery_status',
                    'payment_status',
                    'total_amount',
                    'final_amount',
                    'items' => [
                        '*' => [
                            'product' => [
                                'id',
                                'product_name',
                                'sku',
                            ],
                            'quantity',
                            'unit_price',
                            'total_price',
                        ]
                    ],
                    'created_at',
                    'updated_at',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                ],
            ]);

        $this->storeApiResponse('orders_detail_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_update_order()
    {
        Sanctum::actingAs($this->testUser);

        $order = Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'draft',
        ]);

        $updateData = [
            'note' => 'Updated order note',
            'discount_amount' => 15000,
            'items' => [
                [
                    'product_id' => $this->testProduct->id,
                    'quantity' => 3,
                    'unit_price' => 100000,
                ],
            ],
        ];

        $response = $this->putJson("/api/v1/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => [
                    'note' => 'Updated order note',
                    'discount_amount' => 15000,
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'note' => 'Updated order note',
            'discount_amount' => 15000,
        ]);

        $this->storeApiResponse('orders_update_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_update_order_status()
    {
        Sanctum::actingAs($this->testUser);

        $order = Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'draft',
        ]);

        $response = $this->putJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'processing',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => [
                    'status' => 'processing',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $this->storeApiResponse('orders_update_status_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_record_order_payment()
    {
        Sanctum::actingAs($this->testUser);

        $order = Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'processing',
            'final_amount' => 200000,
        ]);

        $paymentData = [
            'amount' => 200000,
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'notes' => 'Full payment received',
        ];

        $response = $this->postJson("/api/v1/orders/{$order->id}/payment", $paymentData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payment_id',
                    'order_id',
                    'amount',
                    'payment_method',
                    'payment_status',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'amount' => 200000,
                    'payment_method' => 'cash',
                ],
            ]);

        $this->storeApiResponse('orders_record_payment_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_get_order_items()
    {
        Sanctum::actingAs($this->testUser);

        $order = Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
        ]);

        OrderItem::factory()->count(2)->create([
            'order_id' => $order->id,
            'product_id' => $this->testProduct->id,
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->id}/items");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'product' => [
                            'id',
                            'product_name',
                            'sku',
                            'sale_price',
                        ],
                        'quantity',
                        'unit_price',
                        'discount',
                        'total_price',
                    ]
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Order items retrieved successfully',
            ]);

        $this->storeApiResponse('orders_items_success', $response->json());
    }

    /** @test */
    public function can_filter_orders_by_status()
    {
        Sanctum::actingAs($this->testUser);

        Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'draft',
        ]);

        Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'processing',
        ]);

        $response = $this->getJson('/api/v1/orders?status=processing');

        $response->assertStatus(200);
        
        $orders = $response->json('data.data');
        foreach ($orders as $order) {
            $this->assertEquals('processing', $order['status']);
        }

        $this->storeApiResponse('orders_filter_status_success', $response->json());
    }

    /** @test */
    public function can_filter_orders_by_customer()
    {
        Sanctum::actingAs($this->testUser);

        $customer2 = Customer::factory()->create();

        Order::factory()->create(['customer_id' => $this->testCustomer->id]);
        Order::factory()->create(['customer_id' => $customer2->id]);

        $response = $this->getJson("/api/v1/orders?customer_id={$this->testCustomer->id}");

        $response->assertStatus(200);
        
        $orders = $response->json('data.data');
        foreach ($orders as $order) {
            $this->assertEquals($this->testCustomer->id, $order['customer_id']);
        }

        $this->storeApiResponse('orders_filter_customer_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_delete_order()
    {
        Sanctum::actingAs($this->testUser);

        $order = Order::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'draft',
        ]);

        $response = $this->deleteJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order deleted successfully',
            ]);

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);

        $this->storeApiResponse('orders_delete_success', $response->json());
    }

    /** @test */
    public function returns_404_for_non_existent_order()
    {
        Sanctum::actingAs($this->testUser);

        $response = $this->getJson('/api/v1/orders/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Order not found',
            ]);

        $this->storeApiResponse('orders_not_found_error', $response->json());
    }

    /** @test */
    public function unauthenticated_user_cannot_access_orders()
    {
        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(401);

        $this->storeApiResponse('orders_unauthorized_error', $response->json());
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
