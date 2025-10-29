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
        // Add tenant_id to customers table
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'tenant_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('customers', 'email')) {
                    $table->index(['tenant_id', 'email'], 'idx_customers_tenant_email');
                }
                if (Schema::hasColumn('customers', 'phone')) {
                    $table->index(['tenant_id', 'phone'], 'idx_customers_tenant_phone');
                }
                if (Schema::hasColumn('customers', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_customers_tenant_status');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_customers_tenant_created');
            });
            
            // Update unique constraint for email to include tenant_id (if exists)
            if (Schema::hasColumn('customers', 'email')) {
                try {
                    DB::statement('ALTER TABLE customers ADD UNIQUE KEY unique_tenant_customer_email (tenant_id, email)');
                } catch (Exception $e) {
                    // Email might not have unique constraint originally
                }
            }
        }

        // Add tenant_id to suppliers table
        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'tenant_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('suppliers', 'email')) {
                    $table->index(['tenant_id', 'email'], 'idx_suppliers_tenant_email');
                }
                if (Schema::hasColumn('suppliers', 'phone')) {
                    $table->index(['tenant_id', 'phone'], 'idx_suppliers_tenant_phone');
                }
                if (Schema::hasColumn('suppliers', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_suppliers_tenant_status');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_suppliers_tenant_created');
            });
            
            // Update unique constraint for email to include tenant_id (if exists)
            if (Schema::hasColumn('suppliers', 'email')) {
                try {
                    DB::statement('ALTER TABLE suppliers ADD UNIQUE KEY unique_tenant_supplier_email (tenant_id, email)');
                } catch (Exception $e) {
                    // Email might not have unique constraint originally
                }
            }
        }

        // Add tenant_id to categories table
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'tenant_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('categories', 'name')) {
                    $table->index(['tenant_id', 'name'], 'idx_categories_tenant_name');
                }
                if (Schema::hasColumn('categories', 'parent_id')) {
                    $table->index(['tenant_id', 'parent_id'], 'idx_categories_tenant_parent');
                }
                if (Schema::hasColumn('categories', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_categories_tenant_status');
                }
            });
            
            // Update unique constraint for name to include tenant_id (if exists)
            if (Schema::hasColumn('categories', 'name')) {
                try {
                    DB::statement('ALTER TABLE categories ADD UNIQUE KEY unique_tenant_category_name (tenant_id, name)');
                } catch (Exception $e) {
                    // Name might not have unique constraint originally
                }
            }
        }

        // Add tenant_id to products table
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'tenant_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('products', 'sku')) {
                    $table->index(['tenant_id', 'sku'], 'idx_products_tenant_sku');
                }
                if (Schema::hasColumn('products', 'barcode')) {
                    $table->index(['tenant_id', 'barcode'], 'idx_products_tenant_barcode');
                }
                if (Schema::hasColumn('products', 'category_id')) {
                    $table->index(['tenant_id', 'category_id'], 'idx_products_tenant_category');
                }
                if (Schema::hasColumn('products', 'status')) {
                    $table->index(['tenant_id', 'status'], 'idx_products_tenant_status');
                }
                $table->index(['tenant_id', 'created_at'], 'idx_products_tenant_created');
                if (Schema::hasColumn('products', 'name')) {
                    $table->index(['tenant_id', 'name'], 'idx_products_tenant_name');
                }
            });
            
            // Update unique constraint for SKU to include tenant_id
            if (Schema::hasColumn('products', 'sku')) {
                try {
                    DB::statement('ALTER TABLE products DROP INDEX products_sku_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE products ADD UNIQUE KEY unique_tenant_product_sku (tenant_id, sku)');
            }
            
            // Update unique constraint for barcode to include tenant_id (if exists)
            if (Schema::hasColumn('products', 'barcode')) {
                try {
                    DB::statement('ALTER TABLE products DROP INDEX products_barcode_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE products ADD UNIQUE KEY unique_tenant_product_barcode (tenant_id, barcode)');
            }
        }

        // Add tenant_id to product_attributes table
        if (Schema::hasTable('product_attributes') && !Schema::hasColumn('product_attributes', 'tenant_id')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('product_attributes', 'name')) {
                    $table->index(['tenant_id', 'name'], 'idx_product_attributes_tenant_name');
                }
                if (Schema::hasColumn('product_attributes', 'type')) {
                    $table->index(['tenant_id', 'type'], 'idx_product_attributes_tenant_type');
                }
                if (Schema::hasColumn('product_attributes', 'is_required')) {
                    $table->index(['tenant_id', 'is_required'], 'idx_product_attributes_tenant_required');
                }
            });
            
            // Update unique constraint for name to include tenant_id
            if (Schema::hasColumn('product_attributes', 'name')) {
                try {
                    DB::statement('ALTER TABLE product_attributes DROP INDEX product_attributes_name_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE product_attributes ADD UNIQUE KEY unique_tenant_attribute_name (tenant_id, name)');
            }
        }

        // Add tenant_id to inventories table
        if (Schema::hasTable('inventories') && !Schema::hasColumn('inventories', 'tenant_id')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance (only if columns exist)
                if (Schema::hasColumn('inventories', 'product_id')) {
                    $table->index(['tenant_id', 'product_id'], 'idx_inventories_tenant_product');
                }
                if (Schema::hasColumn('inventories', 'warehouse_id')) {
                    $table->index(['tenant_id', 'warehouse_id'], 'idx_inventories_tenant_warehouse');
                }
                if (Schema::hasColumn('inventories', 'quantity')) {
                    $table->index(['tenant_id', 'quantity'], 'idx_inventories_tenant_quantity');
                }
                $table->index(['tenant_id', 'updated_at'], 'idx_inventories_tenant_updated');
            });
            
            // Update unique constraint for product-warehouse combination to include tenant_id
            if (Schema::hasColumn('inventories', 'product_id') && Schema::hasColumn('inventories', 'warehouse_id')) {
                try {
                    DB::statement('ALTER TABLE inventories DROP INDEX inventories_product_warehouse_unique');
                } catch (Exception $e) {
                    // Index might not exist
                }
                DB::statement('ALTER TABLE inventories ADD UNIQUE KEY unique_tenant_inventory (tenant_id, product_id, warehouse_id)');
            }
        }

        // Add tenant_id to inventory_transactions table
        if (Schema::hasTable('inventory_transactions') && !Schema::hasColumn('inventory_transactions', 'tenant_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'product_id'], 'idx_inventory_trans_tenant_product');
                $table->index(['tenant_id', 'warehouse_id'], 'idx_inventory_trans_tenant_warehouse');
                $table->index(['tenant_id', 'transaction_type'], 'idx_inventory_trans_tenant_type');
                $table->index(['tenant_id', 'created_at'], 'idx_inventory_trans_tenant_created');
                $table->index(['tenant_id', 'reference_type', 'reference_id'], 'idx_inventory_trans_tenant_ref');
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
        // Remove tenant_id from inventory_transactions table
        if (Schema::hasTable('inventory_transactions') && Schema::hasColumn('inventory_transactions', 'tenant_id')) {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_inventory_trans_tenant_product');
                $table->dropIndex('idx_inventory_trans_tenant_warehouse');
                $table->dropIndex('idx_inventory_trans_tenant_type');
                $table->dropIndex('idx_inventory_trans_tenant_created');
                $table->dropIndex('idx_inventory_trans_tenant_ref');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from inventories table
        if (Schema::hasTable('inventories') && Schema::hasColumn('inventories', 'tenant_id')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_inventories_tenant_product');
                $table->dropIndex('idx_inventories_tenant_warehouse');
                $table->dropIndex('idx_inventories_tenant_quantity');
                $table->dropIndex('idx_inventories_tenant_updated');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            try {
                DB::statement('ALTER TABLE inventories DROP KEY unique_tenant_inventory');
                DB::statement('ALTER TABLE inventories ADD UNIQUE KEY inventories_product_warehouse_unique (product_id, warehouse_id)');
            } catch (Exception $e) {
                // Constraint might not exist
            }
        }

        // Remove tenant_id from product_attributes table
        if (Schema::hasTable('product_attributes') && Schema::hasColumn('product_attributes', 'tenant_id')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_product_attributes_tenant_name');
                $table->dropIndex('idx_product_attributes_tenant_type');
                $table->dropIndex('idx_product_attributes_tenant_required');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            try {
                DB::statement('ALTER TABLE product_attributes DROP KEY unique_tenant_attribute_name');
                DB::statement('ALTER TABLE product_attributes ADD UNIQUE KEY product_attributes_name_unique (name)');
            } catch (Exception $e) {
                // Constraint might not exist
            }
        }

        // Remove tenant_id from products table
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'tenant_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_products_tenant_sku');
                $table->dropIndex('idx_products_tenant_barcode');
                $table->dropIndex('idx_products_tenant_category');
                $table->dropIndex('idx_products_tenant_status');
                $table->dropIndex('idx_products_tenant_created');
                $table->dropIndex('idx_products_tenant_name');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraints
            try {
                DB::statement('ALTER TABLE products DROP KEY unique_tenant_product_sku');
                DB::statement('ALTER TABLE products ADD UNIQUE KEY products_sku_unique (sku)');
            } catch (Exception $e) {
                // Constraint might not exist
            }
            
            try {
                DB::statement('ALTER TABLE products DROP KEY unique_tenant_product_barcode');
                DB::statement('ALTER TABLE products ADD UNIQUE KEY products_barcode_unique (barcode)');
            } catch (Exception $e) {
                // Constraint might not exist
            }
        }

        // Remove tenant_id from categories table
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'tenant_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_categories_tenant_name');
                $table->dropIndex('idx_categories_tenant_parent');
                $table->dropIndex('idx_categories_tenant_status');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from suppliers table
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'tenant_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_suppliers_tenant_email');
                $table->dropIndex('idx_suppliers_tenant_phone');
                $table->dropIndex('idx_suppliers_tenant_status');
                $table->dropIndex('idx_suppliers_tenant_created');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from customers table
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'tenant_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_customers_tenant_email');
                $table->dropIndex('idx_customers_tenant_phone');
                $table->dropIndex('idx_customers_tenant_status');
                $table->dropIndex('idx_customers_tenant_created');
                $table->dropColumn('tenant_id');
            });
        }
    }
};
