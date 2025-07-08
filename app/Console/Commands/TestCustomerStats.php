<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Http\Controllers\Admin\CMS\CustomerController;

class TestCustomerStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:customer-stats {customer_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test customer statistics calculation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customerId = $this->argument('customer_id');
        
        $this->info("Testing customer statistics for customer ID: {$customerId}");
        
        try {
            $customer = Customer::findOrFail($customerId);
            
            $this->info("Customer: {$customer->name} ({$customer->customer_code})");
            $this->info("Phone: {$customer->phone}");
            $this->info("Points: {$customer->points}");
            
            $this->line('');
            $this->info('=== Customer Statistics ===');
            
            $stats = $customer->getStats();
            
            foreach ($stats as $key => $value) {
                if ($value instanceof \Carbon\Carbon) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $this->line("{$key}: {$value}");
            }
            
            $this->line('');
            $this->info('=== Order Breakdown ===');

            // Detailed order breakdown
            $allOrders = $customer->orders()->orderBy('created_at', 'desc')->get();

            $completedOrders = $allOrders->where('status', 'completed');
            $returnedOrders = $allOrders->where('status', 'returned');
            $unpaidOrders = $allOrders->whereIn('payment_status', ['unpaid', 'partial'])
                                     ->whereNotIn('status', ['cancelled', 'returned']);

            $this->line("📊 Completed Orders ({$completedOrders->count()}):");
            foreach ($completedOrders as $order) {
                $this->line("  ✅ {$order->order_code}: {$order->final_amount} VND - {$order->payment_status}");
            }

            $this->line("↩️ Returned Orders ({$returnedOrders->count()}):");
            foreach ($returnedOrders as $order) {
                $this->line("  🔄 {$order->order_code}: {$order->final_amount} VND - {$order->payment_status}");
            }

            $this->line("💳 Unpaid/Partial Orders ({$unpaidOrders->count()}):");
            foreach ($unpaidOrders as $order) {
                $remaining = $order->final_amount - ($order->amount_paid ?? 0);
                $this->line("  💰 {$order->order_code}: {$order->final_amount} VND (paid: {$order->amount_paid}, remaining: {$remaining})");
            }

            $this->line('');
            $this->info('=== Calculations Verification ===');
            $totalCompleted = $completedOrders->sum('final_amount');
            $totalReturned = $returnedOrders->sum('final_amount');
            $netSalesCalc = $totalCompleted - $totalReturned;

            $this->line("Total Completed: {$totalCompleted} VND");
            $this->line("Total Returned: {$totalReturned} VND");
            $this->line("Net Sales: {$totalCompleted} - {$totalReturned} = {$netSalesCalc} VND");
            $this->line("Purchase Count: {$completedOrders->count()} orders");

            $this->line('');
            $this->info('=== Order History ===');

            $orderHistory = $customer->getOrderHistory();

            if (empty($orderHistory)) {
                $this->warn('No order history found');
            } else {
                foreach ($orderHistory as $order) {
                    $this->line("Order: {$order['order_code']} - {$order['date']} - {$order['seller']} - {$order['total']} - {$order['status']}");
                }
            }
            
            $this->line('');
            $this->info('=== Testing Controller Method ===');
            
            $controller = new CustomerController();
            $response = $controller->statistics($customer);
            $responseData = $response->getData(true);
            
            if ($responseData['success']) {
                $this->info('Controller method successful!');
                $data = $responseData['data'];
                
                $this->line("Total Debt: {$data['total_debt']}");
                $this->line("Total Points: {$data['total_points']}");
                $this->line("Total Spent: {$data['total_spent']}");
                $this->line("Purchase Count: {$data['purchase_count']}");
                $this->line("Net Sales: {$data['net_sales']}");
                $this->line("Order History Count: " . count($data['order_history']));
                $this->line("Debt Details Count: " . count($data['debt_details']));
                
            } else {
                $this->error('Controller method failed: ' . $responseData['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
