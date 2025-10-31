<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add tenant_id to orders table
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'tenant_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('orders', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_orders_tenant_status');
                }
                if (Schema::hasColumn('orders', 'customer_id')) {
                    $table->index(['tenant_id', 'customer_id'], 'idx_orders_tenant_customer');
                }
                if (Schema::hasColumn('orders', 'branch_shop_id')) {
                    $table->index(['tenant_id', 'branch_shop_id'], 'idx_orders_tenant_branch');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_orders_tenant_created');
                if (Schema::hasColumn('orders', 'order_date')) {
                    $table->index(['tenant_id', 'order_date'], 'idx_orders_tenant_date');
                }
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $table->index(['tenant_id', 'payment_status'], 'idx_orders_tenant_payment');
                }
            });
            
            // Update unique constraint for order_number to include tenant_id (if exists)
            if (Schema::hasColumn('orders', 'order_number')) {
                try {
                    DB::statement('ALTER TABLE orders DROP INDEX orders_order_number_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE orders ADD UNIQUE KEY unique_tenant_order_number (tenant_id, order_number)');
            }
        }

        // Add tenant_id to invoices table
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'tenant_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('invoices', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_invoices_tenant_status');
                }
                if (Schema::hasColumn('invoices', 'customer_id')) {
                    $table->index(['tenant_id', 'customer_id'], 'idx_invoices_tenant_customer');
                }
                if (Schema::hasColumn('invoices', 'branch_shop_id')) {
                    $table->index(['tenant_id', 'branch_shop_id'], 'idx_invoices_tenant_branch');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_invoices_tenant_created');
                if (Schema::hasColumn('invoices', 'invoice_date')) {
                    $table->index(['tenant_id', 'invoice_date'], 'idx_invoices_tenant_date');
                }
                if (Schema::hasColumn('invoices', 'sales_channel')) {
                    $table->index(['tenant_id', 'sales_channel'], 'idx_invoices_tenant_channel');
                }
            });
            
            // Update unique constraint for invoice_number to include tenant_id (if exists)
            if (Schema::hasColumn('invoices', 'invoice_number')) {
                try {
                    DB::statement('ALTER TABLE invoices DROP INDEX invoices_invoice_number_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE invoices ADD UNIQUE KEY unique_tenant_invoice_number (tenant_id, invoice_number)');
            }
        }

        // Add tenant_id to return_orders table
        if (Schema::hasTable('return_orders') && !Schema::hasColumn('return_orders', 'tenant_id')) {
            Schema::table('return_orders', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'status'], 'idx_return_orders_tenant_status');
                $table->index(['tenant_id', 'invoice_id'], 'idx_return_orders_tenant_invoice');
                $table->index(['tenant_id', 'customer_id'], 'idx_return_orders_tenant_customer');
                $table->index(['tenant_id', 'created_at'], 'idx_return_orders_tenant_created');
                $table->index(['tenant_id', 'return_date'], 'idx_return_orders_tenant_date');
            });
            
            // Update unique constraint for return_number to include tenant_id (if exists)
            if (Schema::hasColumn('return_orders', 'return_number')) {
                try {
                    DB::statement('ALTER TABLE return_orders DROP INDEX return_orders_return_number_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE return_orders ADD UNIQUE KEY unique_tenant_return_number (tenant_id, return_number)');
            }
        }

        // Add tenant_id to payments table
        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'tenant_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('payments', 'type')) {
                    $table->index(['tenant_id', 'type'], 'idx_payments_tenant_type');
                }
                if (Schema::hasColumn('payments', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_payments_tenant_status');
                }
                if (Schema::hasColumn('payments', 'reference_type') && Schema::hasColumn('payments', 'reference_id')) {
                    $table->index(['tenant_id', 'reference_type', 'reference_id'], 'idx_payments_tenant_reference');
                }
                if (Schema::hasColumn('payments', 'bank_account_id')) {
                    $table->index(['tenant_id', 'bank_account_id'], 'idx_payments_tenant_bank');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_payments_tenant_created');
                $table->index(['tenant_id', 'payment_date'], 'idx_payments_tenant_date');
            });
            
            // Update unique constraint for payment_code to include tenant_id (if exists)
            if (Schema::hasColumn('payments', 'payment_code')) {
                try {
                    DB::statement('ALTER TABLE payments DROP INDEX payments_payment_code_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE payments ADD UNIQUE KEY unique_tenant_payment_code (tenant_id, payment_code)');
            }
        }

        // Add tenant_id to bank_accounts table
        if (Schema::hasTable('bank_accounts') && !Schema::hasColumn('bank_accounts', 'tenant_id')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('bank_accounts', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_bank_accounts_tenant_status');
                }
                if (Schema::hasColumn('bank_accounts', 'type')) {
                    $table->index(['tenant_id', 'type'], 'idx_bank_accounts_tenant_type');
                }
                if (Schema::hasColumn('bank_accounts', 'is_default')) {
                    $table->index(['tenant_id', 'is_default'], 'idx_bank_accounts_tenant_default');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_bank_accounts_tenant_created');
            });
            
            // Update unique constraint for account_number to include tenant_id (if exists)
            if (Schema::hasColumn('bank_accounts', 'account_number')) {
                try {
                    DB::statement('ALTER TABLE bank_accounts DROP INDEX bank_accounts_account_number_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE bank_accounts ADD UNIQUE KEY unique_tenant_account_number (tenant_id, account_number)');
            }
        }

        // Add tenant_id to notifications table
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'tenant_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('notifications', 'user_id')) {
                    $table->index(['tenant_id', 'user_id'], 'idx_notifications_tenant_user');
                }
                if (Schema::hasColumn('notifications', 'type')) {
                    $table->index(['tenant_id', 'type'], 'idx_notifications_tenant_type');
                }
                if (Schema::hasColumn('notifications', 'read_at')) {
                    $table->index(['tenant_id', 'is_read'], 'idx_notifications_tenant_read');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_notifications_tenant_created');
                $table->index(['tenant_id', 'priority'], 'idx_notifications_tenant_priority');
            });
        }

        // Add tenant_id to notification_templates table
        if (Schema::hasTable('notification_templates') && !Schema::hasColumn('notification_templates', 'tenant_id')) {
            Schema::table('notification_templates', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'type'], 'idx_notification_templates_tenant_type');
                $table->index(['tenant_id', 'channel'], 'idx_notification_templates_tenant_channel');
                $table->index(['tenant_id', 'is_active'], 'idx_notification_templates_tenant_active');
            });
            
            // Update unique constraint for type+channel to include tenant_id (if exists)
            if (Schema::hasColumn('notification_templates', 'type') && Schema::hasColumn('notification_templates', 'channel')) {
                try {
                    DB::statement('ALTER TABLE notification_templates DROP INDEX notification_templates_type_channel_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE notification_templates ADD UNIQUE KEY unique_tenant_template (tenant_id, type, channel)');
            }
        }

        // Add tenant_id to shopee_tokens table (if exists)
        if (Schema::hasTable('shopee_tokens') && !Schema::hasColumn('shopee_tokens', 'tenant_id')) {
            Schema::table('shopee_tokens', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'shop_id'], 'idx_shopee_tokens_tenant_shop');
                $table->index(['tenant_id', 'is_active'], 'idx_shopee_tokens_tenant_active');
                $table->index(['tenant_id', 'expires_at'], 'idx_shopee_tokens_tenant_expires');
            });
            
            // Update unique constraint for shop_id to include tenant_id (if exists)
            if (Schema::hasColumn('shopee_tokens', 'shop_id')) {
                try {
                    DB::statement('ALTER TABLE shopee_tokens DROP INDEX shopee_tokens_shop_id_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE shopee_tokens ADD UNIQUE KEY unique_tenant_shopee_shop (tenant_id, shop_id)');
            }
        }

        // Add tenant_id to backups table (if exists)
        if (Schema::hasTable('backups') && !Schema::hasColumn('backups', 'tenant_id')) {
            Schema::table('backups', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'status'], 'idx_backups_tenant_status');
                $table->index(['tenant_id', 'type'], 'idx_backups_tenant_type');
                $table->index(['tenant_id', 'created_at'], 'idx_backups_tenant_created');
            });
        }

        // Add tenant_id to backup_schedules table (if exists)
        if (Schema::hasTable('backup_schedules') && !Schema::hasColumn('backup_schedules', 'tenant_id')) {
            Schema::table('backup_schedules', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'is_active'], 'idx_backup_schedules_tenant_active');
                $table->index(['tenant_id', 'frequency'], 'idx_backup_schedules_tenant_frequency');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove tenant_id from backup_schedules table
        if (Schema::hasTable('backup_schedules') && Schema::hasColumn('backup_schedules', 'tenant_id')) {
            Schema::table('backup_schedules', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_backup_schedules_tenant_active');
                $table->dropIndex('idx_backup_schedules_tenant_frequency');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from backups table
        if (Schema::hasTable('backups') && Schema::hasColumn('backups', 'tenant_id')) {
            Schema::table('backups', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_backups_tenant_status');
                $table->dropIndex('idx_backups_tenant_type');
                $table->dropIndex('idx_backups_tenant_created');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from shopee_tokens table
        if (Schema::hasTable('shopee_tokens') && Schema::hasColumn('shopee_tokens', 'tenant_id')) {
            Schema::table('shopee_tokens', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_shopee_tokens_tenant_shop');
                $table->dropIndex('idx_shopee_tokens_tenant_active');
                $table->dropIndex('idx_shopee_tokens_tenant_expires');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            try {
                DB::statement('ALTER TABLE shopee_tokens DROP KEY unique_tenant_shopee_shop');
                DB::statement('ALTER TABLE shopee_tokens ADD UNIQUE KEY shopee_tokens_shop_id_unique (shop_id)');
            } catch (Exception $e) {
                // Constraint might not exist
            }
        }

        // Remove tenant_id from notification_templates table
        if (Schema::hasTable('notification_templates') && Schema::hasColumn('notification_templates', 'tenant_id')) {
            Schema::table('notification_templates', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_notification_templates_tenant_type');
                $table->dropIndex('idx_notification_templates_tenant_channel');
                $table->dropIndex('idx_notification_templates_tenant_active');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from notifications table
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'tenant_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_notifications_tenant_user');
                $table->dropIndex('idx_notifications_tenant_type');
                $table->dropIndex('idx_notifications_tenant_read');
                $table->dropIndex('idx_notifications_tenant_created');
                $table->dropIndex('idx_notifications_tenant_priority');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from bank_accounts table
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('bank_accounts', 'tenant_id')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_bank_accounts_tenant_status');
                $table->dropIndex('idx_bank_accounts_tenant_type');
                $table->dropIndex('idx_bank_accounts_tenant_default');
                $table->dropIndex('idx_bank_accounts_tenant_created');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from payments table
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'tenant_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_payments_tenant_type');
                $table->dropIndex('idx_payments_tenant_status');
                $table->dropIndex('idx_payments_tenant_reference');
                $table->dropIndex('idx_payments_tenant_bank');
                $table->dropIndex('idx_payments_tenant_created');
                $table->dropIndex('idx_payments_tenant_date');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from return_orders table
        if (Schema::hasTable('return_orders') && Schema::hasColumn('return_orders', 'tenant_id')) {
            Schema::table('return_orders', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_return_orders_tenant_status');
                $table->dropIndex('idx_return_orders_tenant_invoice');
                $table->dropIndex('idx_return_orders_tenant_customer');
                $table->dropIndex('idx_return_orders_tenant_created');
                $table->dropIndex('idx_return_orders_tenant_date');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from invoices table
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'tenant_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_invoices_tenant_status');
                $table->dropIndex('idx_invoices_tenant_customer');
                $table->dropIndex('idx_invoices_tenant_branch');
                $table->dropIndex('idx_invoices_tenant_created');
                $table->dropIndex('idx_invoices_tenant_date');
                $table->dropIndex('idx_invoices_tenant_channel');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from orders table
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'tenant_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_orders_tenant_status');
                $table->dropIndex('idx_orders_tenant_customer');
                $table->dropIndex('idx_orders_tenant_branch');
                $table->dropIndex('idx_orders_tenant_created');
                $table->dropIndex('idx_orders_tenant_date');
                $table->dropIndex('idx_orders_tenant_payment');
                $table->dropColumn('tenant_id');
            });
        }
    }
};
