<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\BranchShop;
use App\Models\Analytics\SalesDailySummary;
use App\Models\Analytics\CustomerDailyStats;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tenant;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->branch = BranchShop::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /** @test */
    public function it_returns_business_overview_data()
    {
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'total_revenue' => 1000000,
            'total_profit' => 200000,
            'total_orders' => 10,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/analytics/business/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metrics' => [
                        'total_revenue',
                        'total_profit',
                        'total_orders',
                        'unique_customers',
                        'profit_margin',
                    ],
                    'chart_data',
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(1000000, $response->json('data.metrics.total_revenue'));
    }

    /** @test */
    public function it_returns_customer_overview_data()
    {
        CustomerDailyStats::factory()->create([
            'tenant_id' => $this->tenant->id,
            'total_customers' => 50,
            'new_customers' => 10,
            'returning_customers' => 40,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/analytics/customer/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metrics' => [
                        'total_customers',
                        'new_customers',
                        'returning_customers',
                        'vip_customers',
                        'total_revenue',
                    ],
                ],
            ]);

        $this->assertTrue($response->json('success'));
    }

    /** @test */
    public function it_filters_by_date_range()
    {
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

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/analytics/business/overview?from_date=2025-01-01&to_date=2025-01-31');

        $response->assertStatus(200);
        $this->assertEquals(500000, $response->json('data.metrics.total_revenue'));
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

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/analytics/business/overview?branch_shop_id=' . $this->branch->id);

        $response->assertStatus(200);
        $this->assertEquals(500000, $response->json('data.metrics.total_revenue'));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/analytics/business/overview');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_isolates_tenant_data()
    {
        $otherTenant = Tenant::factory()->create();
        
        SalesDailySummary::factory()->create([
            'tenant_id' => $this->tenant->id,
            'total_revenue' => 500000,
        ]);
        
        SalesDailySummary::factory()->create([
            'tenant_id' => $otherTenant->id,
            'total_revenue' => 1000000,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/analytics/business/overview');

        $response->assertStatus(200);
        $this->assertEquals(500000, $response->json('data.metrics.total_revenue'));
    }

    /** @test */
    public function it_returns_chart_data_in_correct_format()
    {
        SalesDailySummary::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/analytics/business/overview');

        $response->assertStatus(200);
        
        $chartData = $response->json('data.chart_data');
        $this->assertIsArray($chartData);
        $this->assertCount(3, $chartData);
        
        // Check first item structure
        $this->assertArrayHasKey('date', $chartData[0]);
        $this->assertArrayHasKey('revenue', $chartData[0]);
        $this->assertArrayHasKey('profit', $chartData[0]);
    }
}

