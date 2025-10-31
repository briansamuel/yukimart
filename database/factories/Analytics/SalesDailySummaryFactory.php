<?php

namespace Database\Factories\Analytics;

use App\Models\Analytics\SalesDailySummary;
use App\Models\Tenant;
use App\Models\BranchShop;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesDailySummaryFactory extends Factory
{
    protected $model = SalesDailySummary::class;

    public function definition()
    {
        $totalRevenue = $this->faker->numberBetween(100000, 10000000);
        $totalCogs = $totalRevenue * 0.6;
        $totalProfit = $totalRevenue - $totalCogs;

        return [
            'tenant_id' => Tenant::factory(),
            'branch_shop_id' => BranchShop::factory(),
            'summary_date' => $this->faker->date(),
            'total_orders' => $this->faker->numberBetween(1, 100),
            'total_return_orders' => $this->faker->numberBetween(0, 10),
            'total_revenue' => $totalRevenue,
            'total_return_amount' => $this->faker->numberBetween(0, 100000),
            'net_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalProfit,
            'unique_customers' => $this->faker->numberBetween(1, 80),
            'new_customers' => $this->faker->numberBetween(0, 20),
            'returning_customers' => $this->faker->numberBetween(0, 60),
            'walkin_customers' => $this->faker->numberBetween(0, 20),
            'offline_revenue' => $totalRevenue * 0.7,
            'online_revenue' => $totalRevenue * 0.2,
            'marketplace_revenue' => $totalRevenue * 0.1,
        ];
    }
}

