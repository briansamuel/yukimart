<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;

class SetupOrderPaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:setup-payment 
                            {--migrate : Run migration only}
                            {--seed : Run seeder only}
                            {--force : Force run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup payment method columns for orders table and seed sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up Order Payment System...');
        $this->newLine();

        // Check if migration is needed
        $needsMigration = !Schema::hasColumn('orders', 'payment_method');
        
        if ($needsMigration) {
            $this->warn('⚠️  Payment method columns not found in orders table.');
        } else {
            $this->info('✅ Payment method columns already exist in orders table.');
        }

        // Get options
        $migrateOnly = $this->option('migrate');
        $seedOnly = $this->option('seed');
        $force = $this->option('force');

        // Confirm action if not forced
        if (!$force && !$migrateOnly && !$seedOnly) {
            if (!$this->confirm('Do you want to proceed with setup?')) {
                $this->info('Setup cancelled.');
                return 0;
            }
        }

        // Run migration
        if (!$seedOnly && $needsMigration) {
            $this->info('📦 Running migration...');
            
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/2025_06_18_120003_add_payment_method_to_orders_table.php',
                    '--force' => true
                ]);
                
                $this->info('✅ Migration completed successfully!');
                $this->line(Artisan::output());
            } catch (\Exception $e) {
                $this->error('❌ Migration failed: ' . $e->getMessage());
                return 1;
            }
        }

        // Stop here if migrate only
        if ($migrateOnly) {
            $this->info('🎉 Migration completed!');
            return 0;
        }

        // Check if we have existing orders
        $existingOrdersCount = Order::count();
        
        if ($existingOrdersCount > 0 && !$force) {
            $this->warn("⚠️  Found {$existingOrdersCount} existing orders in database.");
            
            if (!$this->confirm('Do you want to create additional sample orders with payment data?')) {
                $this->info('Seeding cancelled.');
                return 0;
            }
        }

        // Run seeder
        if (!$migrateOnly) {
            $this->info('🌱 Seeding sample orders with payment data...');
            
            try {
                Artisan::call('db:seed', [
                    '--class' => 'OrderWithPaymentSeeder',
                    '--force' => true
                ]);
                
                $this->info('✅ Seeding completed successfully!');
                $this->line(Artisan::output());
            } catch (\Exception $e) {
                $this->error('❌ Seeding failed: ' . $e->getMessage());
                return 1;
            }
        }

        // Show statistics
        $this->showStatistics();

        $this->newLine();
        $this->info('🎉 Order Payment System setup completed successfully!');
        
        return 0;
    }

    /**
     * Show order statistics.
     */
    protected function showStatistics()
    {
        $this->newLine();
        $this->info('📊 Order Statistics:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        try {
            $stats = Order::getStatistics();
            
            // General statistics
            $this->line(sprintf('📦 Total Orders: %d', $stats['total_orders']));
            $this->line(sprintf('✅ Completed Orders: %d', $stats['completed_orders']));
            $this->line(sprintf('⏳ Processing Orders: %d', $stats['processing_orders']));
            $this->line(sprintf('❌ Cancelled Orders: %d', $stats['cancelled_orders']));
            
            $this->newLine();
            
            // Payment statistics
            $this->line(sprintf('💰 Paid Orders: %d', $stats['paid_orders']));
            $this->line(sprintf('💸 Unpaid Orders: %d', $stats['unpaid_orders']));
            $this->line(sprintf('💳 Partially Paid Orders: %d', $stats['partial_paid_orders']));
            $this->line(sprintf('⏰ Overdue Orders: %d', $stats['overdue_orders']));
            
            $this->newLine();
            
            // Financial statistics
            $this->line(sprintf('💵 Total Revenue: %s VND', number_format($stats['total_revenue'])));
            $this->line(sprintf('💰 Total Paid Amount: %s VND', number_format($stats['total_paid_amount'])));
            $this->line(sprintf('📊 Average Order Value: %s VND', number_format($stats['average_order_value'])));
            $this->line(sprintf('🔴 Outstanding Amount: %s VND', number_format($stats['total_outstanding_amount'])));
            
            $this->newLine();
            
            // Payment method breakdown
            if (!empty($stats['payment_methods'])) {
                $this->line('💳 Payment Methods:');
                foreach ($stats['payment_methods'] as $method => $data) {
                    $methodDisplay = $this->getPaymentMethodDisplay($method);
                    $this->line(sprintf('   %s: %d orders (%s VND)', 
                        $methodDisplay, 
                        $data['count'], 
                        number_format($data['total_amount'])
                    ));
                }
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to get statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get payment method display name.
     */
    protected function getPaymentMethodDisplay($method)
    {
        return match($method) {
            'cash' => '💵 Cash',
            'card' => '💳 Card',
            'transfer' => '🏦 Transfer',
            'cod' => '📦 COD',
            'e_wallet' => '📱 E-Wallet',
            'installment' => '📅 Installment',
            'credit' => '🏷️  Credit',
            'voucher' => '🎫 Voucher',
            'points' => '⭐ Points',
            'mixed' => '🔄 Mixed',
            default => '❓ ' . ucfirst($method),
        };
    }
}
