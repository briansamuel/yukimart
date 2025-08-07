<?php

namespace Tests\Feature\Api\V1;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $testUser;
    protected Customer $testCustomer;
    protected Invoice $testInvoice;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testUser = User::factory()->create();
        $this->testCustomer = Customer::factory()->create();
        $this->testInvoice = Invoice::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'final_amount' => 500000,
        ]);
    }

    /** @test */
    public function authenticated_user_can_get_payments_list()
    {
        Sanctum::actingAs($this->testUser);

        Payment::factory()->count(5)->create([
            'customer_id' => $this->testCustomer->id,
        ]);

        $response = $this->getJson('/api/v1/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'payment_code',
                            'payment_type',
                            'customer_id',
                            'amount',
                            'payment_method',
                            'payment_date',
                            'status',
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
                'message' => 'Payments retrieved successfully',
            ]);

        $this->storeApiResponse('payments_list_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_create_payment()
    {
        Sanctum::actingAs($this->testUser);

        $paymentData = [
            'payment_type' => 'receipt',
            'reference_type' => 'invoice',
            'reference_id' => $this->testInvoice->id,
            'customer_id' => $this->testCustomer->id,
            'payment_date' => now()->format('Y-m-d'),
            'amount' => 500000,
            'payment_method' => 'cash',
            'description' => 'Thanh toán hóa đơn HD001',
            'notes' => 'Khách hàng thanh toán đầy đủ',
        ];

        $response = $this->postJson('/api/v1/payments', $paymentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'payment_code',
                    'payment_type',
                    'amount',
                    'payment_method',
                    'status',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'payment_type' => 'receipt',
                    'amount' => 500000,
                    'payment_method' => 'cash',
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'payment_type' => 'receipt',
            'amount' => 500000,
            'customer_id' => $this->testCustomer->id,
        ]);

        $this->storeApiResponse('payments_create_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_get_payment_details()
    {
        Sanctum::actingAs($this->testUser);

        $payment = Payment::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'reference_type' => 'invoice',
            'reference_id' => $this->testInvoice->id,
        ]);

        $response = $this->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'payment_code',
                    'payment_type',
                    'customer' => [
                        'id',
                        'name',
                        'phone',
                    ],
                    'reference_type',
                    'reference_id',
                    'amount',
                    'payment_method',
                    'payment_date',
                    'description',
                    'notes',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $payment->id,
                ],
            ]);

        $this->storeApiResponse('payments_detail_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_update_payment()
    {
        Sanctum::actingAs($this->testUser);

        $payment = Payment::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'pending',
        ]);

        $updateData = [
            'amount' => 600000,
            'payment_method' => 'bank_transfer',
            'notes' => 'Updated payment notes',
        ];

        $response = $this->putJson("/api/v1/payments/{$payment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Payment updated successfully',
                'data' => [
                    'amount' => 600000,
                    'payment_method' => 'bank_transfer',
                    'notes' => 'Updated payment notes',
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'amount' => 600000,
            'payment_method' => 'bank_transfer',
        ]);

        $this->storeApiResponse('payments_update_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_approve_payment()
    {
        Sanctum::actingAs($this->testUser);

        $payment = Payment::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/payments/{$payment->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Payment approved successfully',
                'data' => [
                    'status' => 'approved',
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'approved',
        ]);

        $this->storeApiResponse('payments_approve_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_get_payment_summary()
    {
        Sanctum::actingAs($this->testUser);

        // Create test payments
        Payment::factory()->create([
            'payment_type' => 'receipt',
            'amount' => 1000000,
            'payment_date' => now(),
            'status' => 'approved',
        ]);

        Payment::factory()->create([
            'payment_type' => 'disbursement',
            'amount' => 300000,
            'payment_date' => now(),
            'status' => 'approved',
        ]);

        $response = $this->getJson('/api/v1/payments/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'period' => [
                        'from',
                        'to',
                    ],
                    'opening_balance',
                    'total_receipts',
                    'total_disbursements',
                    'net_amount',
                    'closing_balance',
                    'receipts_count',
                    'disbursements_count',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Payment summary retrieved successfully',
            ]);

        $this->storeApiResponse('payments_summary_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_bulk_create_payments()
    {
        Sanctum::actingAs($this->testUser);

        $bulkData = [
            'payments' => [
                [
                    'payment_type' => 'receipt',
                    'customer_id' => $this->testCustomer->id,
                    'amount' => 100000,
                    'payment_method' => 'cash',
                    'payment_date' => now()->format('Y-m-d'),
                    'description' => 'Bulk payment 1',
                ],
                [
                    'payment_type' => 'receipt',
                    'customer_id' => $this->testCustomer->id,
                    'amount' => 200000,
                    'payment_method' => 'bank_transfer',
                    'payment_date' => now()->format('Y-m-d'),
                    'description' => 'Bulk payment 2',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/payments/bulk-create', $bulkData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'created_count',
                    'payments' => [
                        '*' => [
                            'id',
                            'payment_code',
                            'amount',
                            'payment_method',
                        ]
                    ],
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Bulk payments created successfully',
                'data' => [
                    'created_count' => 2,
                ],
            ]);

        $this->assertDatabaseCount('payments', 2);

        $this->storeApiResponse('payments_bulk_create_success', $response->json());
    }

    /** @test */
    public function can_filter_payments_by_type()
    {
        Sanctum::actingAs($this->testUser);

        Payment::factory()->create(['payment_type' => 'receipt']);
        Payment::factory()->create(['payment_type' => 'disbursement']);

        $response = $this->getJson('/api/v1/payments?payment_type=receipt');

        $response->assertStatus(200);
        
        $payments = $response->json('data.data');
        foreach ($payments as $payment) {
            $this->assertEquals('receipt', $payment['payment_type']);
        }

        $this->storeApiResponse('payments_filter_type_success', $response->json());
    }

    /** @test */
    public function can_filter_payments_by_date_range()
    {
        Sanctum::actingAs($this->testUser);

        Payment::factory()->create([
            'payment_date' => now()->subDays(5),
        ]);

        Payment::factory()->create([
            'payment_date' => now(),
        ]);

        $dateFrom = now()->subDays(2)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $response = $this->getJson("/api/v1/payments?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);

        $this->storeApiResponse('payments_filter_date_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_delete_payment()
    {
        Sanctum::actingAs($this->testUser);

        $payment = Payment::factory()->create([
            'customer_id' => $this->testCustomer->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Payment deleted successfully',
            ]);

        $this->assertSoftDeleted('payments', [
            'id' => $payment->id,
        ]);

        $this->storeApiResponse('payments_delete_success', $response->json());
    }

    /** @test */
    public function returns_404_for_non_existent_payment()
    {
        Sanctum::actingAs($this->testUser);

        $response = $this->getJson('/api/v1/payments/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Payment not found',
            ]);

        $this->storeApiResponse('payments_not_found_error', $response->json());
    }

    /** @test */
    public function unauthenticated_user_cannot_access_payments()
    {
        $response = $this->getJson('/api/v1/payments');

        $response->assertStatus(401);

        $this->storeApiResponse('payments_unauthorized_error', $response->json());
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
