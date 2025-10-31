<?php

namespace Tests\Unit\Models\Analytics;

use Tests\TestCase;
use App\Models\Analytics\SalesDailySummary;
use App\Models\Tenant;
use App\Models\BranchShop;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesDailySummaryTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test tenant
        $this->tenant = Tenant::factory()->create();
        
        // Create test branch
        $this->branch = BranchShop::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /** @test */
    public function it_can_create_sales_daily_summary()
    {
        $summary = SalesDailySummary::create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'summary_date' => now()->toDateString(),
            'total_orders' => 10,
            'total_revenue' => 1000000,
            'total_profit' => 200000,
            'unique_customers' => 8,
        ]);

        $this->assertDatabaseHas('sales_daily_summary', [
            'tenant_id' => $this->tenant->id,
            'total_orders' => 10,
        ]);
    }

    /** @test */
    public function it_belongs_to_tenant()
    {
        $summary = SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertInstanceOf(Tenant::class, $summary->tenant);
        $this->assertEquals($this->tenant->id, $summary->tenant->id);
    }

    /** @test */
    public function it_belongs_to_branch_shop()
    {
        $summary = SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
        ]);

        $this->assertInstanceOf(BranchShop::class, $summary->branchShop);
        $this->assertEquals($this->branch->id, $summary->branchShop->id);
    }

    /** @test */
    public function it_can_filter_by_tenant()
    {
        $otherTenant = Tenant::factory()->create();
        
        SalesDailySummary::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        SalesDailySummary::factory()->count(2)->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $results = SalesDailySummary::byTenant($this->tenant->id)->get();

        $this->assertCount(3, $results);
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'summary_date' => '2025-01-01',
        ]);
        
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'summary_date' => '2025-01-15',
        ]);
        
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'summary_date' => '2025-02-01',
        ]);

        $results = SalesDailySummary::byTenant($this->tenant->id)
            ->byDateRange('2025-01-01', '2025-01-31')
            ->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_can_filter_by_branch()
    {
        $otherBranch = BranchShop::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        SalesDailySummary::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
        ]);
        
        SalesDailySummary::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $otherBranch->id,
        ]);

        $results = SalesDailySummary::byTenant($this->tenant->id)
            ->byBranch($this->branch->id)
            ->get();

        $this->assertCount(3, $results);
    }

    /** @test */
    public function it_casts_summary_date_to_date()
    {
        $summary = SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'summary_date' => '2025-01-15',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $summary->summary_date);
    }

    /** @test */
    public function it_casts_decimal_fields_correctly()
    {
        $summary = SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'total_revenue' => 1000000.50,
            'total_profit' => 200000.25,
        ]);

        $this->assertEquals('1000000.50', $summary->total_revenue);
        $this->assertEquals('200000.25', $summary->total_profit);
    }
}

