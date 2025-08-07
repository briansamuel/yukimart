<?php

namespace Tests\Feature\Api\V1;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUser = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_get_customers_list()
    {
        Sanctum::actingAs($this->testUser);

        Customer::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'phone',
                            'email',
                            'customer_type',
                            'points',
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
                'message' => 'Customers retrieved successfully',
            ]);

        $this->storeApiResponse('customers_list_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_search_customers()
    {
        Sanctum::actingAs($this->testUser);

        Customer::factory()->create([
            'name' => 'Nguyễn Văn A',
            'phone' => '0123456789',
            'email' => 'nguyenvana@example.com',
        ]);

        Customer::factory()->create([
            'name' => 'Trần Thị B',
            'phone' => '0987654321',
            'email' => 'tranthib@example.com',
        ]);

        $response = $this->getJson('/api/v1/customers/search?q=Nguyễn');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'phone',
                        'email',
                    ]
                ],
                'meta'
            ])
            ->assertJsonFragment([
                'name' => 'Nguyễn Văn A',
            ]);

        $this->storeApiResponse('customers_search_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_create_customer()
    {
        Sanctum::actingAs($this->testUser);

        $customerData = [
            'name' => 'Lê Văn C',
            'phone' => '0111222333',
            'email' => 'levanc@example.com',
            'address' => '123 Đường ABC, Quận 1, TP.HCM',
            'area' => 'Quận 1',
            'customer_type' => 'retail',
            'customer_group' => 'vip',
            'birthday' => '1985-05-15',
            'points' => 100,
        ];

        $response = $this->postJson('/api/v1/customers', $customerData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'customer_type',
                    'points',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => [
                    'name' => 'Lê Văn C',
                    'phone' => '0111222333',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Lê Văn C',
            'phone' => '0111222333',
        ]);

        $this->storeApiResponse('customers_create_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_get_customer_details()
    {
        Sanctum::actingAs($this->testUser);

        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'address',
                    'customer_type',
                    'customer_group',
                    'points',
                    'birthday',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $customer->id,
                ],
            ]);

        $this->storeApiResponse('customers_detail_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_update_customer()
    {
        Sanctum::actingAs($this->testUser);

        $customer = Customer::factory()->create();

        $updateData = [
            'name' => 'Updated Customer Name',
            'phone' => '0999888777',
            'points' => 200,
        ];

        $response = $this->putJson("/api/v1/customers/{$customer->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => [
                    'name' => 'Updated Customer Name',
                    'phone' => '0999888777',
                    'points' => 200,
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer Name',
            'phone' => '0999888777',
        ]);

        $this->storeApiResponse('customers_update_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_delete_customer()
    {
        Sanctum::actingAs($this->testUser);

        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Customer deleted successfully',
            ]);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);

        $this->storeApiResponse('customers_delete_success', $response->json());
    }

    /** @test */
    public function can_filter_customers_by_type()
    {
        Sanctum::actingAs($this->testUser);

        Customer::factory()->create(['customer_type' => 'retail']);
        Customer::factory()->create(['customer_type' => 'wholesale']);

        $response = $this->getJson('/api/v1/customers?customer_type=retail');

        $response->assertStatus(200);
        
        $customers = $response->json('data.data');
        foreach ($customers as $customer) {
            $this->assertEquals('retail', $customer['customer_type']);
        }

        $this->storeApiResponse('customers_filter_type_success', $response->json());
    }

    /** @test */
    public function returns_404_for_non_existent_customer()
    {
        Sanctum::actingAs($this->testUser);

        $response = $this->getJson('/api/v1/customers/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Customer not found',
            ]);

        $this->storeApiResponse('customers_not_found_error', $response->json());
    }

    /** @test */
    public function unauthenticated_user_cannot_access_customers()
    {
        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(401);

        $this->storeApiResponse('customers_unauthorized_error', $response->json());
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
