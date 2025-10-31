<?php

namespace Database\Factories\Analytics;

use App\Models\Analytics\CustomerDailyStats;
use App\Models\Tenant;
use App\Models\BranchShop;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerDailyStatsFactory extends Factory
{
    protected $model = CustomerDailyStats::class;

    public function definition()
    {
        $totalCustomers = $this->faker->numberBetween(10, 100);
        $newCustomers = $this->faker->numberBetween(1, 20);
        $returningCustomers = $totalCustomers - $newCustomers;

        return [
            'tenant_id' => Tenant::factory(),
            'branch_shop_id' => BranchShop::factory(),
            'stats_date' => $this->faker->date(),
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'walkin_customers' => $this->faker->numberBetween(0, 10),
            'vip_customers' => $this->faker->numberBetween(0, 5),
            'new_customer_revenue' => $this->faker->numberBetween(100000, 1000000),
            'returning_customer_revenue' => $this->faker->numberBetween(500000, 5000000),
            'walkin_revenue' => $this->faker->numberBetween(50000, 500000),
            'vip_revenue' => $this->faker->numberBetween(100000, 2000000),
            'avg_order_value' => $this->faker->numberBetween(50000, 500000),
            'avg_customer_lifetime_value' => $this->faker->numberBetween(100000, 1000000),
        ];
    }
}

