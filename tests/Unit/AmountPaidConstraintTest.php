<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\User;

class AmountPaidConstraintTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_order_without_amount_paid()
    {
        $customer = Customer::factory()->create();
        $branchShop = BranchShop::factory()->create();
        $user = User::factory()->create();

        $order = Order::create([
            'order_code' => 'TEST001',
            'customer_id' => $customer->id,
            'branch_shop_id' => $branchShop->id,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'processing',
            'created_by' => $user->id,
            'sold_by' => $user->id,
        ]);

        $this->assertNotNull($order->id);
        $this->assertEquals(0, $order->amount_paid);
    }

    /** @test */
    public function it_sets_default_amount_paid_to_zero()
    {
        $order = Order::factory()->create([
            'amount_paid' => null, // Try to set null explicitly
        ]);

        // Should default to 0
        $this->assertEquals(0, $order->amount_paid);
    }

    /** @test */
    public function it_can_create_order_with_explicit_amount_paid()
    {
        $order = Order::factory()->create([
            'final_amount' => 100000,
            'amount_paid' => 50000,
        ]);

        $this->assertEquals(50000, $order->amount_paid);
    }

    /** @test */
    public function it_handles_amount_paid_in_payment_calculations()
    {
        $order = Order::factory()->create([
            'final_amount' => 100000,
            'amount_paid' => 0,
        ]);

        // Record a payment
        $order->recordPayment(30000, 'cash', 'REF123');

        $this->assertEquals(30000, $order->amount_paid);
        $this->assertEquals('partial', $order->payment_status);
    }

    /** @test */
    public function it_can_mark_order_as_paid()
    {
        $order = Order::factory()->create([
            'final_amount' => 100000,
            'amount_paid' => 0,
        ]);

        $order->markAsPaid('card', 'CARD123', 'Full payment');

        $this->assertEquals(100000, $order->amount_paid);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('card', $order->payment_method);
    }

    /** @test */
    public function it_updates_payment_status_correctly()
    {
        $order = Order::factory()->create([
            'final_amount' => 100000,
            'amount_paid' => 0,
        ]);

        // Test unpaid
        $order->updatePaymentStatus();
        $this->assertEquals('unpaid', $order->payment_status);

        // Test partial payment
        $order->amount_paid = 50000;
        $order->save();
        $order->updatePaymentStatus();
        $this->assertEquals('partial', $order->payment_status);

        // Test full payment
        $order->amount_paid = 100000;
        $order->save();
        $order->updatePaymentStatus();
        $this->assertEquals('paid', $order->payment_status);

        // Test overpayment
        $order->amount_paid = 120000;
        $order->save();
        $order->updatePaymentStatus();
        $this->assertEquals('overpaid', $order->payment_status);
    }

    /** @test */
    public function it_checks_if_order_is_fully_paid()
    {
        $order = Order::factory()->create([
            'final_amount' => 100000,
            'amount_paid' => 0,
        ]);

        $this->assertFalse($order->is_fully_paid);

        $order->amount_paid = 100000;
        $order->save();

        $this->assertTrue($order->fresh()->is_fully_paid);
    }

    /** @test */
    public function it_can_create_multiple_orders_with_factory()
    {
        $orders = Order::factory(10)->create();

        $this->assertCount(10, $orders);

        foreach ($orders as $order) {
            $this->assertNotNull($order->amount_paid);
            $this->assertGreaterThanOrEqual(0, $order->amount_paid);
        }
    }

    /** @test */
    public function it_handles_different_payment_states_in_factory()
    {
        // Test unpaid orders
        $unpaidOrder = Order::factory()->unpaid()->create();
        $this->assertEquals(0, $unpaidOrder->amount_paid);
        $this->assertEquals('unpaid', $unpaidOrder->payment_status);

        // Test paid orders
        $paidOrder = Order::factory()->paid()->create();
        $this->assertEquals($paidOrder->final_amount, $paidOrder->amount_paid);
        $this->assertEquals('paid', $paidOrder->payment_status);

        // Test completed orders
        $completedOrder = Order::factory()->completed()->create();
        $this->assertEquals($completedOrder->final_amount, $completedOrder->amount_paid);
        $this->assertEquals('paid', $completedOrder->payment_status);

        // Test cancelled orders
        $cancelledOrder = Order::factory()->cancelled()->create();
        $this->assertEquals(0, $cancelledOrder->amount_paid);
        $this->assertEquals('unpaid', $cancelledOrder->payment_status);
    }

    /** @test */
    public function it_handles_bulk_insert_without_amount_paid()
    {
        $customer = Customer::factory()->create();
        $branchShop = BranchShop::factory()->create();
        $user = User::factory()->create();

        $orderData = [];
        for ($i = 1; $i <= 5; $i++) {
            $orderData[] = [
                'order_code' => 'BULK' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'total_amount' => 100000 * $i,
                'final_amount' => 100000 * $i,
                'status' => 'processing',
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // This should not fail due to null constraint
        Order::insert($orderData);

        $orders = Order::where('order_code', 'like', 'BULK%')->get();
        $this->assertCount(5, $orders);

        foreach ($orders as $order) {
            $this->assertEquals(0, $order->amount_paid);
        }
    }

    /** @test */
    public function it_validates_amount_paid_is_numeric()
    {
        $order = Order::factory()->create();

        // Test setting valid numeric values
        $order->amount_paid = 50000;
        $order->save();
        $this->assertEquals(50000, $order->amount_paid);

        $order->amount_paid = 0;
        $order->save();
        $this->assertEquals(0, $order->amount_paid);

        $order->amount_paid = 99.99;
        $order->save();
        $this->assertEquals(99.99, $order->amount_paid);
    }
}
