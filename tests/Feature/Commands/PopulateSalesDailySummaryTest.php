<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\BranchShop;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\InvoiceItem;
use App\Models\Analytics\SalesDailySummary;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PopulateSalesDailySummaryTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::factory()->create();
        $this->branch = BranchShop::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /** @test */
    public function it_can_run_command_successfully()
    {
        $this->artisan('analytics:populate-sales-summary')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_populates_sales_summary_from_invoices()
    {
        // Create test invoices
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'cost_price' => 50000,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'invoice_type' => 'sale',
            'invoice_date' => now()->toDateString(),
            'total_amount' => 100000,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50000,
        ]);

        $this->artisan('analytics:populate-sales-summary');

        $this->assertDatabaseHas('sales_daily_summary', [
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'summary_date' => now()->toDateString(),
        ]);

        $summary = SalesDailySummary::where('tenant_id', $this->tenant->id)
            ->where('summary_date', now()->toDateString())
            ->first();

        $this->assertNotNull($summary);
        $this->assertEquals(1, $summary->total_orders);
    }

    /** @test */
    public function it_can_populate_for_specific_date()
    {
        $specificDate = '2025-01-15';

        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'invoice_type' => 'sale',
            'invoice_date' => $specificDate,
            'total_amount' => 100000,
        ]);

        $this->artisan('analytics:populate-sales-summary', [
            '--date' => $specificDate,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('sales_daily_summary', [
            'tenant_id' => $this->tenant->id,
            'summary_date' => $specificDate,
        ]);
    }

    /** @test */
    public function it_updates_existing_summary()
    {
        $date = now()->toDateString();

        // Create initial summary
        SalesDailySummary::create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'summary_date' => $date,
            'total_orders' => 5,
            'total_revenue' => 500000,
        ]);

        // Create new invoice
        Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'invoice_type' => 'sale',
            'invoice_date' => $date,
            'total_amount' => 100000,
        ]);

        $this->artisan('analytics:populate-sales-summary');

        // Should update, not create new
        $count = SalesDailySummary::where('tenant_id', $this->tenant->id)
            ->where('summary_date', $date)
            ->count();

        $this->assertEquals(1, $count);
    }

    /** @test */
    public function it_isolates_tenant_data()
    {
        $otherTenant = Tenant::factory()->create();
        $otherBranch = BranchShop::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        // Create invoices for both tenants
        Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'invoice_type' => 'sale',
            'total_amount' => 100000,
        ]);

        Invoice::factory()->create([
            'tenant_id' => $otherTenant->id,
            'branch_shop_id' => $otherBranch->id,
            'invoice_type' => 'sale',
            'total_amount' => 200000,
        ]);

        $this->artisan('analytics:populate-sales-summary');

        // Check tenant 1 summary
        $summary1 = SalesDailySummary::where('tenant_id', $this->tenant->id)->first();
        $this->assertNotNull($summary1);

        // Check tenant 2 summary
        $summary2 = SalesDailySummary::where('tenant_id', $otherTenant->id)->first();
        $this->assertNotNull($summary2);

        // Ensure data is isolated
        $this->assertNotEquals($summary1->total_revenue, $summary2->total_revenue);
    }

    /** @test */
    public function it_handles_return_orders()
    {
        $date = now()->toDateString();

        // Create sale invoice
        Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'invoice_type' => 'sale',
            'invoice_date' => $date,
            'total_amount' => 100000,
        ]);

        // Create return invoice
        Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'invoice_type' => 'return',
            'invoice_date' => $date,
            'total_amount' => 20000,
        ]);

        $this->artisan('analytics:populate-sales-summary');

        $summary = SalesDailySummary::where('tenant_id', $this->tenant->id)
            ->where('summary_date', $date)
            ->first();

        $this->assertEquals(1, $summary->total_orders);
        $this->assertEquals(1, $summary->total_return_orders);
        $this->assertEquals(100000, $summary->total_revenue);
        $this->assertEquals(20000, $summary->total_return_amount);
    }
}

