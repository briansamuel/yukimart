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
        // Add performance indexes for orders table
        Schema::table('orders', function (Blueprint $table) {
            // Check if index doesn't exist before adding
            if (!$this->indexExists('orders', 'idx_orders_status')) {
                $table->index('status', 'idx_orders_status');
            }
            if (!$this->indexExists('orders', 'idx_orders_created_at')) {
                $table->index('created_at', 'idx_orders_created_at');
            }
        });

        // Add performance indexes for products table
        Schema::table('products', function (Blueprint $table) {
            if (!$this->indexExists('products', 'idx_products_name')) {
                $table->index('product_name', 'idx_products_name');
            }
            if (!$this->indexExists('products', 'idx_products_status')) {
                $table->index('product_status', 'idx_products_status');
            }
        });

        // Add performance indexes for customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!$this->indexExists('customers', 'idx_customers_phone')) {
                $table->index('phone', 'idx_customers_phone');
            }
            if (!$this->indexExists('customers', 'idx_customers_email')) {
                $table->index('email', 'idx_customers_email');
            }
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_created_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_status');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_phone');
            $table->dropIndex('idx_customers_email');
        });
    }
};
