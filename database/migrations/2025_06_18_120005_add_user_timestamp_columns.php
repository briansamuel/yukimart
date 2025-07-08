<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add user timestamp columns to products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'created_by')) {
                    // Người tạo
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('products', 'updated_by')) {
                    // Người cập nhật cuối
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('products', 'deleted_by')) {
                    // Người xóa
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }

        // Add user timestamp columns to orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'updated_by')) {
                    // Người cập nhật cuối
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('orders', 'deleted_by')) {
                    // Người xóa
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }

        // Add user timestamp columns to invoices table
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'deleted_by')) {
                    // Người xóa
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }

        // Add user timestamp columns to inventory_transactions table
        if (Schema::hasTable('inventory_transactions')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_transactions', 'updated_by')) {
                    // Người cập nhật cuối
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('inventory_transactions', 'deleted_by')) {
                    // Người xóa
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }

        // Add user timestamp columns to suppliers table
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'created_by')) {
                    // Người tạo
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('suppliers', 'updated_by')) {
                    // Người cập nhật cuối
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('suppliers', 'deleted_by')) {
                    // Người xóa
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }

        // Add user timestamp columns to customers table
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'created_by')) {
                    // Người tạo
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('customers', 'updated_by')) {
                    // Người cập nhật cuối
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('customers', 'deleted_by')) {
                    // Người xóa
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }

        // Add user timestamp columns to branches table
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (!Schema::hasColumn('branches', 'created_by')) {
                    // Người tạo
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('branches', 'updated_by')) {
                    // Người cập nhật cuối
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('branches', 'deleted_by')) {
                    // Người xóa
                    $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                }
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
        $tables = ['products', 'orders', 'invoices', 'inventory_transactions', 'suppliers', 'customers', 'branches'];
        $columns = ['created_by', 'updated_by', 'deleted_by'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName) {
                    foreach ($columns as $column) {
                        if (Schema::hasColumn($tableName, $column)) {
                            $table->dropForeign([$column]);
                            $table->dropColumn($column);
                        }
                    }
                });
            }
        }
    }
};
