<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ReturnOrder;

class QuickOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $branchShop;
    protected $customer;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@yukimart.com',
            'is_root' => 0
        ]);
        
        // Create branch shop
        $this->branchShop = BranchShop::factory()->create();
        
        // Assign user to branch shop
        $this->user->branchShops()->attach($this->branchShop->id);
        
        // Create test customer
        $this->customer = Customer::factory()->create();
        
        // Create test product
        $this->product = Product::factory()->create([
            'stock_quantity' => 100,
            'price' => 50000,
            'barcode' => '1234567890'
        ]);
    }

    /** @test */
    public function it_can_access_quick_order_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quick-order');

        $response->assertStatus(200);
        $response->assertViewIs('admin.quick-order.index');
        $response->assertSee('Đặt hàng nhanh');
    }

    /** @test */
    public function it_can_access_quick_order_with_return_type()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quick-order?type=return');

        $response->assertStatus(200);
        $response->assertViewIs('admin.quick-order.index');
        $response->assertSee('Trả hàng');
    }

    /** @test */
    public function it_can_search_products()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quick-order/search-product?search=test');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'sku',
                    'barcode',
                    'price',
                    'stock_quantity'
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_search_products_by_barcode()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quick-order/search-product?search=1234567890');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('1234567890', $data[0]['barcode']);
    }

    /** @test */
    public function it_can_create_order_via_quick_order()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => $this->product->price
                ]
            ],
            'payment_method' => 'cash',
            'notes' => 'Test order from QuickOrder'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/quick-order', $orderData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'order_code',
                'total_amount'
            ]
        ]);

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'notes' => 'Test order from QuickOrder'
        ]);
    }

    /** @test */
    public function it_can_create_invoice_via_quick_order()
    {
        $invoiceData = [
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => $this->product->price
                ]
            ],
            'payment_method' => 'cash',
            'payment_amount' => $this->product->price,
            'notes' => 'Test invoice from QuickOrder'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/quick-invoice', $invoiceData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'invoice_code',
                'total_amount'
            ]
        ]);

        // Verify invoice was created
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'paid'
        ]);

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'reference_type' => 'invoice',
            'type' => 'receipt',
            'amount' => $this->product->price
        ]);
    }

    /** @test */
    public function it_can_get_invoices_for_return_selection()
    {
        // Create a paid invoice first
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'paid',
            'total_amount' => 100000
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/quick-order/get-invoices');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'id',
                    'invoice_code',
                    'customer_name',
                    'total_amount',
                    'created_at'
                ]
            ]
        ]);

        $data = $response->json();
        $this->assertGreaterThan(0, $data['recordsTotal']);
    }

    /** @test */
    public function it_filters_invoices_by_user_branch_shops()
    {
        // Create invoice in user's branch
        $userInvoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'paid'
        ]);

        // Create invoice in different branch
        $otherBranch = BranchShop::factory()->create();
        $otherInvoice = Invoice::factory()->create([
            'branch_shop_id' => $otherBranch->id,
            'status' => 'paid'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/quick-order/get-invoices');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $invoiceIds = collect($data)->pluck('id')->toArray();
        
        // Should include user's branch invoice
        $this->assertContains($userInvoice->id, $invoiceIds);
        
        // Should NOT include other branch invoice
        $this->assertNotContains($otherInvoice->id, $invoiceIds);
    }

    /** @test */
    public function it_can_create_return_order_via_quick_order()
    {
        // Create a paid invoice with items first
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'paid',
            'total_amount' => 100000
        ]);

        $returnData = [
            'invoice_id' => $invoice->id,
            'return_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => $this->product->price,
                    'return_reason' => 'Defective product'
                ]
            ],
            'exchange_items' => [],
            'refund_amount' => $this->product->price,
            'payment_method' => 'cash',
            'notes' => 'Test return from QuickOrder'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/return-orders', $returnData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'return_code',
                'refund_amount'
            ]
        ]);

        // Verify return order was created
        $this->assertDatabaseHas('return_orders', [
            'invoice_id' => $invoice->id,
            'customer_id' => $this->customer->id,
            'notes' => 'Test return from QuickOrder'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_order_creation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/admin/quick-order', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function it_validates_stock_availability()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 200, // More than available stock (100)
                    'price' => $this->product->price
                ]
            ],
            'payment_method' => 'cash'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/quick-order', $orderData);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    /** @test */
    public function it_handles_walk_in_customer()
    {
        $orderData = [
            'customer_id' => 0, // Walk-in customer
            'branch_shop_id' => $this->branchShop->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => $this->product->price
                ]
            ],
            'payment_method' => 'cash'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/quick-order', $orderData);

        $response->assertStatus(200);
        
        // Verify order was created with customer_id = 0
        $this->assertDatabaseHas('orders', [
            'customer_id' => 0,
            'branch_shop_id' => $this->branchShop->id
        ]);
    }

    /** @test */
    public function it_updates_inventory_after_order_creation()
    {
        $initialStock = $this->product->stock_quantity;
        $orderQuantity = 5;

        $orderData = [
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $orderQuantity,
                    'price' => $this->product->price
                ]
            ],
            'payment_method' => 'cash'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/admin/quick-order', $orderData);

        $response->assertStatus(200);

        // Verify inventory was updated
        $this->product->refresh();
        $this->assertEquals($initialStock - $orderQuantity, $this->product->stock_quantity);
    }

    /** @test */
    public function it_generates_unique_order_codes()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => $this->product->price
                ]
            ],
            'payment_method' => 'cash'
        ];

        // Create first order
        $response1 = $this->actingAs($this->user)
            ->postJson('/admin/quick-order', $orderData);

        // Create second order
        $response2 = $this->actingAs($this->user)
            ->postJson('/admin/quick-order', $orderData);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $orderCode1 = $response1->json('data.order_code');
        $orderCode2 = $response2->json('data.order_code');

        $this->assertNotEquals($orderCode1, $orderCode2);
        $this->assertStringStartsWith('ORD', $orderCode1);
        $this->assertStringStartsWith('ORD', $orderCode2);
    }
}
