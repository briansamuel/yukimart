<?php

namespace Tests\Feature\Analytics;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\BranchShop;
use App\Models\Analytics\SalesDailySummary;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessOverviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test tenant
        $this->tenant = Tenant::factory()->create();
        
        // Create test user
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        // Create test branch
        $this->branch = BranchShop::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /** @test */
    public function it_can_access_business_overview_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.analytics.business.overview'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.modules.analytics.business.overview');
    }

    /** @test */
    public function it_displays_metrics_correctly()
    {
        // Create test data
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'summary_date' => now()->toDateString(),
            'total_revenue' => 1000000,
            'total_profit' => 200000,
            'total_orders' => 10,
            'unique_customers' => 8,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.analytics.business.overview'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');
        $this->assertEquals(1000000, $metrics['total_revenue']);
        $this->assertEquals(200000, $metrics['total_profit']);
        $this->assertEquals(10, $metrics['total_orders']);
    }

    /** @test */
    public function it_filters_by_date_range()
    {
        // Create data for different dates
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'summary_date' => '2025-01-01',
            'total_revenue' => 500000,
        ]);
        
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'summary_date' => '2025-02-01',
            'total_revenue' => 300000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.analytics.business.overview', [
                'from_date' => '2025-01-01',
                'to_date' => '2025-01-31',
            ]));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        $this->assertEquals(500000, $metrics['total_revenue']);
    }

    /** @test */
    public function it_filters_by_branch()
    {
        $otherBranch = BranchShop::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $this->branch->id,
            'total_revenue' => 500000,
        ]);
        
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_shop_id' => $otherBranch->id,
            'total_revenue' => 300000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.analytics.business.overview', [
                'branch_shop_id' => $this->branch->id,
            ]));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        $this->assertEquals(500000, $metrics['total_revenue']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get(route('admin.analytics.business.overview'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_isolates_tenant_data()
    {
        $otherTenant = Tenant::factory()->create();
        
        // Create data for current tenant
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'total_revenue' => 500000,
        ]);
        
        // Create data for other tenant
        SalesDailySummary::factory()->create([
            'tenant_id' => $otherTenant->id,
            'total_revenue' => 1000000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.analytics.business.overview'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        // Should only see current tenant's data
        $this->assertEquals(500000, $metrics['total_revenue']);
    }

    /** @test */
    public function it_provides_chart_data()
    {
        SalesDailySummary::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.analytics.business.overview'));

        $response->assertStatus(200);
        $response->assertViewHas('chartData');
        
        $chartData = $response->viewData('chartData');
        $this->assertIsArray($chartData);
        $this->assertCount(5, $chartData);
    }
}

