<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderFactoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required dependencies
        Customer::factory()->create();
        Branch::factory()->create();
    }

    /** @test */
    public function it_can_create_basic_order()
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertNotNull($order->order_code);
        $this->assertNotNull($order->customer_id);
        $this->assertNotNull($order->branch_id);
        $this->assertGreaterThan(0, $order->total_amount);
        $this->assertGreaterThanOrEqual(0, $order->discount_amount);
        $this->assertGreaterThan(0, $order->final_amount);
    }

    /** @test */
    public function it_generates_unique_order_codes()
    {
        $orders = Order::factory()->count(10)->create();
        
        $orderCodes = $orders->pluck('order_code')->toArray();
        $uniqueCodes = array_unique($orderCodes);
        
        $this->assertCount(10, $uniqueCodes, 'All order codes should be unique');
    }

    /** @test */
    public function it_generates_order_code_with_correct_format()
    {
        $order = Order::factory()->create();

        // Order code should be in format: DH + YYYYMMDD + 4 digits
        $this->assertMatchesRegularExpression('/^DH\d{8}\d{4}$/', $order->order_code);

        // Extract date part and verify it's a valid date
        $datePart = substr($order->order_code, 2, 8);
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);

        $this->assertTrue(checkdate($month, $day, $year), 'Date part should be valid');
    }

    /** @test */
    public function it_can_create_completed_order()
    {
        $order = Order::factory()->completed()->create();

        $this->assertEquals('completed', $order->status);
        $this->assertContains($order->delivery_status, ['delivered', 'returned']);
        $this->assertEquals($order->final_amount, $order->amount_paid);
    }

    /** @test */
    public function it_can_create_processing_order()
    {
        $order = Order::factory()->processing()->create();

        $this->assertEquals('processing', $order->status);
        $this->assertContains($order->delivery_status, ['pending', 'picking', 'delivering']);
        $this->assertLessThanOrEqual($order->final_amount, $order->amount_paid);
    }

    /** @test */
    public function it_can_create_cancelled_order()
    {
        $order = Order::factory()->cancelled()->create();

        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('pending', $order->delivery_status);
        $this->assertEquals(0, $order->amount_paid);
        $this->assertNotNull($order->internal_notes);
    }

    /** @test */
    public function it_can_create_online_order()
    {
        $order = Order::factory()->online()->create();

        $this->assertEquals('online', $order->channel);
        $this->assertContains($order->payment_method, ['card', 'transfer', 'cod']);
    }

    /** @test */
    public function it_can_create_direct_order()
    {
        $order = Order::factory()->direct()->create();

        $this->assertEquals('direct', $order->channel);
        $this->assertContains($order->payment_method, ['cash', 'card']);
    }

    /** @test */
    public function it_can_create_pos_order()
    {
        $order = Order::factory()->pos()->create();

        $this->assertEquals('pos', $order->channel);
        $this->assertContains($order->payment_method, ['cash', 'card']);
    }

    /** @test */
    public function it_can_create_high_value_order()
    {
        $order = Order::factory()->highValue()->create();

        $this->assertGreaterThanOrEqual(2000000, $order->total_amount);
        $this->assertLessThanOrEqual(10000000, $order->total_amount);
        $this->assertGreaterThanOrEqual(5, $order->total_quantity);
        $this->assertLessThanOrEqual(20, $order->total_quantity);
    }

    /** @test */
    public function it_can_create_recent_order()
    {
        $order = Order::factory()->recent()->create();

        $oneMonthAgo = now()->subMonth();
        $this->assertGreaterThanOrEqual($oneMonthAgo, $order->created_at);
        $this->assertLessThanOrEqual(now(), $order->created_at);
    }

    /** @test */
    public function it_can_combine_multiple_states()
    {
        $order = Order::factory()
            ->online()
            ->completed()
            ->highValue()
            ->create();

        $this->assertEquals('online', $order->channel);
        $this->assertEquals('completed', $order->status);
        $this->assertGreaterThanOrEqual(2000000, $order->total_amount);
        $this->assertEquals($order->final_amount, $order->amount_paid);
    }

    /** @test */
    public function it_calculates_final_amount_correctly()
    {
        $order = Order::factory()->create([
            'total_amount' => 1000000,
            'discount_amount' => 100000,
        ]);

        $this->assertEquals(900000, $order->final_amount);
    }

    /** @test */
    public function it_handles_zero_discount()
    {
        $order = Order::factory()->create([
            'total_amount' => 1000000,
            'discount_amount' => 0,
        ]);

        $this->assertEquals(1000000, $order->final_amount);
    }

    /** @test */
    public function it_creates_order_with_valid_timestamps()
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->updated_at);
    }

    /** @test */
    public function it_creates_order_with_valid_relationships()
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(Customer::class, $order->customer);
        $this->assertInstanceOf(Branch::class, $order->branch);
    }

    /** @test */
    public function it_handles_datetime_objects_in_order_code_generation()
    {
        $specificDate = now()->subDays(5);
        
        $order = Order::factory()->create([
            'created_at' => $specificDate,
        ]);

        // The order code should contain the date from the created_at timestamp
        $expectedDateStr = $specificDate->format('Ymd');
        $this->assertStringContains($expectedDateStr, $order->order_code);
    }

    /** @test */
    public function it_creates_multiple_orders_efficiently()
    {
        $startTime = microtime(true);
        
        $orders = Order::factory()->count(100)->create();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(100, $orders);
        $this->assertLessThan(10, $executionTime, 'Creating 100 orders should take less than 10 seconds');
    }
}
