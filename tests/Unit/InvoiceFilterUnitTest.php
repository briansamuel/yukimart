<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Admin\CMS\InvoiceController;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceFilterUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new InvoiceController();
        $this->user = User::factory()->create();
        Auth::login($this->user);
    }

    /** @test */
    public function it_can_apply_time_filter_correctly()
    {
        // Create test data
        $customer = Customer::factory()->create();
        $branchShop = BranchShop::factory()->create();
        
        // Create invoices with different dates
        $todayInvoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'branch_shop_id' => $branchShop->id,
            'created_by' => $this->user->id,
            'created_at' => now(),
        ]);

        $yesterdayInvoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'branch_shop_id' => $branchShop->id,
            'created_by' => $this->user->id,
            'created_at' => now()->subDay(),
        ]);

        // Test today filter
        $request = new Request(['time_filter' => 'today']);
        $response = $this->controller->getInvoicesAjax($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);

        // Test yesterday filter
        $request = new Request(['time_filter' => 'yesterday']);
        $response = $this->controller->getInvoicesAjax($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
    }

    /** @test */
    public function it_returns_correct_column_structure()
    {
        // Create test data
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '0123456789',
            'address' => 'Test Address'
        ]);
        
        $branchShop = BranchShop::factory()->create(['name' => 'Test Branch']);
        
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'branch_shop_id' => $branchShop->id,
            'created_by' => $this->user->id,
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'payment_status' => 'partial',
            'payment_method' => 'cash',
            'discount_amount' => 10000,
            'notes' => 'Test invoice notes',
        ]);

        $request = new Request();
        $response = $this->controller->getInvoicesAjax($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);

        $invoiceData = $data['data'][0];
        
        // Check required columns exist
        $requiredColumns = [
            'checkbox', 'invoice_number', 'customer_display', 'total_amount', 
            'amount_paid', 'payment_status', 'payment_method', 'channel', 
            'created_at', 'seller', 'creator', 'discount', 'email', 
            'phone', 'address', 'branch_shop', 'notes'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertArrayHasKey($column, $invoiceData, "Missing column: {$column}");
        }

        // Check data format
        $this->assertStringContains('₫', $invoiceData['total_amount']);
        $this->assertStringContains('₫', $invoiceData['amount_paid']);
        $this->assertStringContains('₫', $invoiceData['discount']);
        $this->assertEquals('Test Customer', $invoiceData['customer_display']);
        $this->assertEquals('test@example.com', $invoiceData['email']);
        $this->assertEquals('0123456789', $invoiceData['phone']);
        $this->assertEquals('Test Address', $invoiceData['address']);
        $this->assertEquals('Test Branch', $invoiceData['branch_shop']);
    }

    /** @test */
    public function it_handles_walk_in_customers_correctly()
    {
        // Create test data without customer
        $branchShop = BranchShop::factory()->create(['name' => 'Test Branch']);
        
        $invoice = Invoice::factory()->create([
            'customer_id' => null,
            'branch_shop_id' => $branchShop->id,
            'created_by' => $this->user->id,
        ]);

        $request = new Request();
        $response = $this->controller->getInvoicesAjax($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);

        $invoiceData = $data['data'][0];
        
        // Check walk-in customer handling
        $this->assertEquals('Khách lẻ', $invoiceData['customer_display']);
        $this->assertEquals('N/A', $invoiceData['email']);
        $this->assertEquals('N/A', $invoiceData['phone']);
        $this->assertEquals('N/A', $invoiceData['address']);
    }

    /** @test */
    public function it_can_search_by_invoice_number()
    {
        // Create test data
        $customer = Customer::factory()->create();
        $branchShop = BranchShop::factory()->create();
        
        $invoice1 = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'branch_shop_id' => $branchShop->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-TEST-001',
        ]);

        $invoice2 = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'branch_shop_id' => $branchShop->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-OTHER-002',
        ]);

        // Test search
        $request = new Request(['search_term' => 'TEST']);
        $response = $this->controller->getInvoicesAjax($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
        $this->assertStringContains('INV-TEST-001', $data['data'][0]['invoice_number']);
    }
}
