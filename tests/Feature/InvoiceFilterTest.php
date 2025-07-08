<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class InvoiceFilterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $customer;
    protected $branchShop;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test customer
        $this->customer = Customer::factory()->create();
        
        // Create test branch shop
        $this->branchShop = BranchShop::factory()->create();
    }

    /** @test */
    public function it_can_filter_invoices_by_time_filter()
    {
        // Create invoices with different dates
        $todayInvoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
            'created_at' => Carbon::today(),
        ]);

        $yesterdayInvoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
            'created_at' => Carbon::yesterday(),
        ]);

        $lastWeekInvoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
            'created_at' => Carbon::now()->subWeek(),
        ]);

        // Test today filter
        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax', ['time_filter' => 'today']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);

        // Test yesterday filter
        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax', ['time_filter' => 'yesterday']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);

        // Test this week filter
        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax', ['time_filter' => 'this_week']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data); // Only today's invoice should be in this week
    }

    /** @test */
    public function it_can_filter_invoices_by_payment_status()
    {
        // Create invoices with different payment statuses
        $paidInvoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
            'payment_status' => 'paid',
        ]);

        $unpaidInvoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
            'payment_status' => 'unpaid',
        ]);

        // Test paid filter
        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax', ['payment_status' => 'paid']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);

        // Test unpaid filter
        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax', ['payment_status' => 'unpaid']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /** @test */
    public function it_can_search_invoices_by_invoice_number()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-TEST-001',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax', ['search_term' => 'INV-TEST']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('INV-TEST-001', $data[0]['invoice_number']);
    }

    /** @test */
    public function it_can_search_invoices_by_customer_name()
    {
        $customer = Customer::factory()->create(['name' => 'John Doe Test']);
        
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax', ['search_term' => 'John Doe']));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /** @test */
    public function it_returns_correct_column_data()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'payment_status' => 'partial',
            'payment_method' => 'cash',
            'discount_amount' => 10000,
            'notes' => 'Test invoice notes',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);

        $invoiceData = $data[0];
        
        // Check required columns exist
        $this->assertArrayHasKey('invoice_number', $invoiceData);
        $this->assertArrayHasKey('customer_display', $invoiceData);
        $this->assertArrayHasKey('total_amount', $invoiceData);
        $this->assertArrayHasKey('amount_paid', $invoiceData);
        $this->assertArrayHasKey('payment_status', $invoiceData);
        $this->assertArrayHasKey('payment_method', $invoiceData);
        $this->assertArrayHasKey('channel', $invoiceData);
        $this->assertArrayHasKey('created_at', $invoiceData);
        
        // Check new columns exist
        $this->assertArrayHasKey('seller', $invoiceData);
        $this->assertArrayHasKey('creator', $invoiceData);
        $this->assertArrayHasKey('discount', $invoiceData);
        $this->assertArrayHasKey('email', $invoiceData);
        $this->assertArrayHasKey('phone', $invoiceData);
        $this->assertArrayHasKey('address', $invoiceData);
        $this->assertArrayHasKey('branch_shop', $invoiceData);
        $this->assertArrayHasKey('notes', $invoiceData);
    }

    /** @test */
    public function it_handles_walk_in_customers_correctly()
    {
        // Create invoice without customer (walk-in)
        $invoice = Invoice::factory()->create([
            'customer_id' => null,
            'branch_shop_id' => $this->branchShop->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.invoice.ajax'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);

        $invoiceData = $data[0];
        $this->assertEquals('Khách lẻ', $invoiceData['customer_display']);
        $this->assertEquals('N/A', $invoiceData['email']);
        $this->assertEquals('N/A', $invoiceData['phone']);
        $this->assertEquals('N/A', $invoiceData['address']);
    }
}
