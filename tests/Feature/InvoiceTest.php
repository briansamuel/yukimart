<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\Payment;

class InvoiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $branchShop;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->branchShop = BranchShop::factory()->create();
        $this->customer = Customer::factory()->create();
        
        $this->user->branchShops()->attach($this->branchShop->id);
    }

    /** @test */
    public function it_can_access_invoices_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/invoices');

        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.index');
    }

    /** @test */
    public function it_can_list_invoices_with_pagination()
    {
        // Create test invoices
        Invoice::factory()->count(15)->create([
            'branch_shop_id' => $this->branchShop->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/invoices/data');

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
                    'status',
                    'created_at'
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_filter_invoices_by_status()
    {
        // Create invoices with different statuses
        Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'processing'
        ]);
        
        Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/invoices/data?status[]=processing');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        foreach ($data as $invoice) {
            $this->assertEquals('processing', $invoice['status']);
        }
    }

    /** @test */
    public function it_can_filter_invoices_by_time_range()
    {
        $startDate = now()->subDays(7)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'created_at' => now()->subDays(5)
        ]);

        Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'created_at' => now()->subDays(10) // Outside range
        ]);

        $response = $this->actingAs($this->user)
            ->get("/admin/invoices/data?date_from={$startDate}&date_to={$endDate}");

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /** @test */
    public function it_can_search_invoices()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'invoice_code' => 'HD202508060001'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/invoices/data?search=HD202508060001');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('HD202508060001', $data[0]['invoice_code']);
    }

    /** @test */
    public function it_can_show_invoice_details()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/admin/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'invoice_code',
            'customer',
            'items',
            'total_amount',
            'status'
        ]);
    }

    /** @test */
    public function it_can_update_invoice_status()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'processing'
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/admin/invoices/{$invoice->id}", [
                'status' => 'completed'
            ]);

        $response->assertStatus(200);
        
        $invoice->refresh();
        $this->assertEquals('completed', $invoice->status);
    }

    /** @test */
    public function it_can_delete_invoice()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/admin/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function it_can_bulk_update_invoice_status()
    {
        $invoices = Invoice::factory()->count(3)->create([
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'processing'
        ]);

        $invoiceIds = $invoices->pluck('id')->toArray();

        $response = $this->actingAs($this->user)
            ->postJson('/admin/invoices/bulk-update', [
                'ids' => $invoiceIds,
                'status' => 'completed'
            ]);

        $response->assertStatus(200);

        foreach ($invoices as $invoice) {
            $invoice->refresh();
            $this->assertEquals('completed', $invoice->status);
        }
    }

    /** @test */
    public function it_can_export_invoices()
    {
        Invoice::factory()->count(5)->create([
            'branch_shop_id' => $this->branchShop->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/invoices/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function it_creates_payment_when_invoice_is_paid()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'processing',
            'total_amount' => 100000
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/admin/invoices/{$invoice->id}", [
                'status' => 'paid',
                'payment_method' => 'cash'
            ]);

        $response->assertStatus(200);

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'type' => 'receipt',
            'amount' => 100000
        ]);
    }

    /** @test */
    public function it_filters_invoices_by_user_branch_shops()
    {
        // Create invoice in user's branch
        $userInvoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id
        ]);

        // Create invoice in different branch
        $otherBranch = BranchShop::factory()->create();
        $otherInvoice = Invoice::factory()->create([
            'branch_shop_id' => $otherBranch->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/invoices/data');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $invoiceIds = collect($data)->pluck('id')->toArray();
        
        $this->assertContains($userInvoice->id, $invoiceIds);
        $this->assertNotContains($otherInvoice->id, $invoiceIds);
    }

    /** @test */
    public function it_validates_invoice_update_data()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/admin/invoices/{$invoice->id}", [
                'status' => 'invalid_status'
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_get_customer_modal_data()
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'customer_id' => $customer->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/admin/customers/{$customer->id}/modal-data");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'customer' => [
                'id',
                'name',
                'phone',
                'email'
            ],
            'statistics' => [
                'total_orders',
                'total_spent',
                'loyalty_points'
            ],
            'recent_orders'
        ]);
    }

    /** @test */
    public function it_handles_walk_in_customer_invoices()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'customer_id' => 0 // Walk-in customer
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/invoices/data');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $walkInInvoice = collect($data)->firstWhere('id', $invoice->id);
        
        $this->assertEquals('Khách lẻ', $walkInInvoice['customer_name']);
    }

    /** @test */
    public function it_generates_unique_invoice_codes()
    {
        $invoice1 = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id
        ]);
        
        $invoice2 = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id
        ]);

        $this->assertNotEquals($invoice1->invoice_code, $invoice2->invoice_code);
        $this->assertStringStartsWith('HD', $invoice1->invoice_code);
        $this->assertStringStartsWith('HD', $invoice2->invoice_code);
    }

    /** @test */
    public function it_can_cancel_invoice()
    {
        $invoice = Invoice::factory()->create([
            'branch_shop_id' => $this->branchShop->id,
            'status' => 'processing'
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/admin/invoices/{$invoice->id}", [
                'status' => 'cancelled'
            ]);

        $response->assertStatus(200);
        
        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);
    }
}
