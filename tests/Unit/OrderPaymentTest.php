<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\BranchShop;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required dependencies
        Customer::factory()->create();
        BranchShop::factory()->create();
    }

    /** @test */
    public function it_can_create_order_with_payment_method()
    {
        $order = Order::factory()->create([
            'payment_method' => 'card',
            'payment_status' => 'paid'
        ]);

        $this->assertEquals('card', $order->payment_method);
        $this->assertEquals('paid', $order->payment_status);
    }

    /** @test */
    public function it_displays_payment_method_correctly()
    {
        $order = Order::factory()->create(['payment_method' => 'card']);
        
        $this->assertEquals('Thẻ tín dụng/ghi nợ', $order->payment_method_display);
    }

    /** @test */
    public function it_displays_payment_status_correctly()
    {
        $order = Order::factory()->create(['payment_status' => 'paid']);
        
        $this->assertEquals('Đã thanh toán', $order->payment_status_display);
        $this->assertStringContains('badge-success', $order->payment_status_badge);
    }

    /** @test */
    public function it_can_record_payment()
    {
        $order = Order::factory()->create([
            'final_amount' => 1000000,
            'amount_paid' => 0,
            'payment_status' => 'unpaid'
        ]);

        $order->recordPayment(500000, 'card', 'REF123', 'Partial payment');

        $this->assertEquals(500000, $order->amount_paid);
        $this->assertEquals('card', $order->payment_method);
        $this->assertEquals('REF123', $order->payment_reference);
        $this->assertEquals('Partial payment', $order->payment_notes);
        $this->assertEquals('partial', $order->payment_status);
        $this->assertNotNull($order->payment_date);
    }

    /** @test */
    public function it_updates_payment_status_automatically()
    {
        $order = Order::factory()->create([
            'final_amount' => 1000000,
            'amount_paid' => 0
        ]);

        // Test unpaid
        $order->updatePaymentStatus();
        $this->assertEquals('unpaid', $order->payment_status);

        // Test partial payment
        $order->amount_paid = 500000;
        $order->updatePaymentStatus();
        $this->assertEquals('partial', $order->payment_status);

        // Test full payment
        $order->amount_paid = 1000000;
        $order->updatePaymentStatus();
        $this->assertEquals('paid', $order->payment_status);

        // Test overpayment
        $order->amount_paid = 1100000;
        $order->updatePaymentStatus();
        $this->assertEquals('overpaid', $order->payment_status);
    }

    /** @test */
    public function it_can_mark_order_as_paid()
    {
        $order = Order::factory()->create([
            'final_amount' => 1000000,
            'amount_paid' => 0,
            'payment_status' => 'unpaid'
        ]);

        $order->markAsPaid('card', 'REF456', 'Full payment received');

        $this->assertEquals(1000000, $order->amount_paid);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('card', $order->payment_method);
        $this->assertEquals('REF456', $order->payment_reference);
        $this->assertEquals('Full payment received', $order->payment_notes);
        $this->assertNotNull($order->payment_date);
    }

    /** @test */
    public function it_can_check_if_order_is_overdue()
    {
        // Order with future due date
        $futureOrder = Order::factory()->create([
            'payment_method' => 'credit',
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(5)
        ]);

        $this->assertFalse($futureOrder->isOverdue());

        // Order with past due date
        $overdueOrder = Order::factory()->create([
            'payment_method' => 'credit',
            'payment_status' => 'unpaid',
            'due_date' => now()->subDays(5)
        ]);

        $this->assertTrue($overdueOrder->isOverdue());

        // Paid order should not be overdue even if past due date
        $paidOrder = Order::factory()->create([
            'payment_method' => 'credit',
            'payment_status' => 'paid',
            'due_date' => now()->subDays(5)
        ]);

        $this->assertFalse($paidOrder->isOverdue());
    }

    /** @test */
    public function it_can_get_days_until_due()
    {
        $order = Order::factory()->create([
            'due_date' => now()->addDays(10)
        ]);

        $this->assertEquals(10, $order->getDaysUntilDue());

        // Past due date should return negative number
        $overdueOrder = Order::factory()->create([
            'due_date' => now()->subDays(5)
        ]);

        $this->assertEquals(-5, $overdueOrder->getDaysUntilDue());

        // No due date should return null
        $noDueDateOrder = Order::factory()->create([
            'due_date' => null
        ]);

        $this->assertNull($noDueDateOrder->getDaysUntilDue());
    }

    /** @test */
    public function it_can_scope_orders_by_payment_method()
    {
        Order::factory()->create(['payment_method' => 'cash']);
        Order::factory()->create(['payment_method' => 'card']);
        Order::factory()->create(['payment_method' => 'cash']);

        $cashOrders = Order::byPaymentMethod('cash')->get();
        $cardOrders = Order::byPaymentMethod('card')->get();

        $this->assertCount(2, $cashOrders);
        $this->assertCount(1, $cardOrders);
    }

    /** @test */
    public function it_can_scope_orders_by_payment_status()
    {
        Order::factory()->create(['payment_status' => 'paid']);
        Order::factory()->create(['payment_status' => 'unpaid']);
        Order::factory()->create(['payment_status' => 'paid']);

        $paidOrders = Order::paid()->get();
        $unpaidOrders = Order::unpaid()->get();

        $this->assertCount(2, $paidOrders);
        $this->assertCount(1, $unpaidOrders);
    }

    /** @test */
    public function it_can_scope_overdue_orders()
    {
        // Create overdue order
        Order::factory()->create([
            'payment_status' => 'unpaid',
            'due_date' => now()->subDays(5)
        ]);

        // Create future due order
        Order::factory()->create([
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(5)
        ]);

        // Create paid order with past due date (should not be overdue)
        Order::factory()->create([
            'payment_status' => 'paid',
            'due_date' => now()->subDays(5)
        ]);

        $overdueOrders = Order::overdue()->get();
        $this->assertCount(1, $overdueOrders);
    }

    /** @test */
    public function it_includes_payment_statistics()
    {
        // Create test orders
        Order::factory()->create(['payment_status' => 'paid', 'final_amount' => 1000000, 'amount_paid' => 1000000]);
        Order::factory()->create(['payment_status' => 'unpaid', 'final_amount' => 500000, 'amount_paid' => 0]);
        Order::factory()->create(['payment_status' => 'partial', 'final_amount' => 800000, 'amount_paid' => 400000]);

        $stats = Order::getStatistics();

        $this->assertEquals(1, $stats['paid_orders']);
        $this->assertEquals(1, $stats['unpaid_orders']);
        $this->assertEquals(1, $stats['partial_paid_orders']);
        $this->assertEquals(1400000, $stats['total_paid_amount']);
        $this->assertEquals(900000, $stats['total_outstanding_amount']); // 500000 + 400000
    }

    /** @test */
    public function it_can_create_orders_with_different_payment_states()
    {
        $creditOrder = Order::factory()->credit()->create();
        $this->assertEquals('credit', $creditOrder->payment_method);
        $this->assertNotNull($creditOrder->due_date);

        $paidOrder = Order::factory()->paid()->create();
        $this->assertEquals('paid', $paidOrder->payment_status);
        $this->assertEquals($paidOrder->final_amount, $paidOrder->amount_paid);

        $unpaidOrder = Order::factory()->unpaid()->create();
        $this->assertEquals('unpaid', $unpaidOrder->payment_status);
        $this->assertEquals(0, $unpaidOrder->amount_paid);
    }

    /** @test */
    public function it_calculates_totals_and_updates_payment_status()
    {
        $order = Order::factory()->create([
            'final_amount' => 1000000,
            'amount_paid' => 500000
        ]);

        $order->calculateTotals();

        // Should update payment status based on amount paid
        $this->assertEquals('partial', $order->payment_status);
    }
}
