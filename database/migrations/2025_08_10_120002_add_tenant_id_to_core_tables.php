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
        // Add tenant_id to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add index for performance
                $table->index(['tenant_id', 'email'], 'idx_users_tenant_email');
                $table->index(['tenant_id', 'status'], 'idx_users_tenant_status');
            });
            
            // Update unique constraint for email to include tenant_id
            DB::statement('ALTER TABLE users DROP INDEX users_email_unique');
            DB::statement('ALTER TABLE users ADD UNIQUE KEY unique_tenant_email (tenant_id, email)');
        }

        // Add tenant_id to roles table
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add index for performance
                $table->index(['tenant_id', 'name'], 'idx_roles_tenant_name');
                $table->index(['tenant_id', 'is_active'], 'idx_roles_tenant_active');
            });
            
            // Update unique constraint for name to include tenant_id
            if (Schema::hasColumn('roles', 'name')) {
                DB::statement('ALTER TABLE roles ADD UNIQUE KEY unique_tenant_role_name (tenant_id, name)');
            }
        }

        // Add tenant_id to permissions table
        if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'tenant_id')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add index for performance
                $table->index(['tenant_id', 'name'], 'idx_permissions_tenant_name');
                $table->index(['tenant_id', 'module'], 'idx_permissions_tenant_module');
                $table->index(['tenant_id', 'action'], 'idx_permissions_tenant_action');
            });
            
            // Update unique constraint for name to include tenant_id
            if (Schema::hasColumn('permissions', 'name')) {
                DB::statement('ALTER TABLE permissions ADD UNIQUE KEY unique_tenant_permission_name (tenant_id, name)');
            }
        }

        // Add tenant_id to branch_shops table
        if (Schema::hasTable('branch_shops') && !Schema::hasColumn('branch_shops', 'tenant_id')) {
            Schema::table('branch_shops', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'status'], 'idx_branch_shops_tenant_status');
                $table->index(['tenant_id', 'shop_type'], 'idx_branch_shops_tenant_type');
                $table->index(['tenant_id', 'created_at'], 'idx_branch_shops_tenant_created');
            });
            
            // Update unique constraint for shop_code to include tenant_id
            if (Schema::hasColumn('branch_shops', 'shop_code')) {
                DB::statement('ALTER TABLE branch_shops ADD UNIQUE KEY unique_tenant_shop_code (tenant_id, shop_code)');
            }
        }

        // Add tenant_id to warehouses table
        if (Schema::hasTable('warehouses') && !Schema::hasColumn('warehouses', 'tenant_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'status'], 'idx_warehouses_tenant_status');
                $table->index(['tenant_id', 'type'], 'idx_warehouses_tenant_type');
            });
            
            // Update unique constraint for code to include tenant_id
            if (Schema::hasColumn('warehouses', 'code')) {
                DB::statement('ALTER TABLE warehouses ADD UNIQUE KEY unique_tenant_warehouse_code (tenant_id, code)');
            }
        }

        // Add tenant_id to settings table
        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'tenant_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add index for performance (only if key column exists)
                if (Schema::hasColumn('settings', 'key')) {
                    $table->index(['tenant_id', 'key'], 'idx_settings_tenant_key');
                }
            });
            
            // Update unique constraint for key to include tenant_id
            if (Schema::hasColumn('settings', 'key')) {
                DB::statement('ALTER TABLE settings ADD UNIQUE KEY unique_tenant_setting_key (tenant_id, key)');
            }
        }

        // Add tenant_id to audit_logs table
        if (Schema::hasTable('audit_logs') && !Schema::hasColumn('audit_logs', 'tenant_id')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->foreignId('tenant_id')->after('id')->default(1)->constrained('tenants')->onDelete('cascade');
                
                // Add indexes for performance
                $table->index(['tenant_id', 'created_at'], 'idx_audit_logs_tenant_created');
                $table->index(['tenant_id', 'user_id'], 'idx_audit_logs_tenant_user');
                $table->index(['tenant_id', 'action'], 'idx_audit_logs_tenant_action');
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
        // Remove tenant_id from audit_logs table
        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'tenant_id')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_audit_logs_tenant_created');
                $table->dropIndex('idx_audit_logs_tenant_user');
                $table->dropIndex('idx_audit_logs_tenant_action');
                $table->dropColumn('tenant_id');
            });
        }

        // Remove tenant_id from settings table
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'tenant_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_settings_tenant_key');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            if (Schema::hasColumn('settings', 'key')) {
                DB::statement('ALTER TABLE settings DROP KEY unique_tenant_setting_key');
                DB::statement('ALTER TABLE settings ADD UNIQUE KEY settings_key_unique (key)');
            }
        }

        // Remove tenant_id from warehouses table
        if (Schema::hasTable('warehouses') && Schema::hasColumn('warehouses', 'tenant_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_warehouses_tenant_status');
                $table->dropIndex('idx_warehouses_tenant_type');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            if (Schema::hasColumn('warehouses', 'code')) {
                DB::statement('ALTER TABLE warehouses DROP KEY unique_tenant_warehouse_code');
                DB::statement('ALTER TABLE warehouses ADD UNIQUE KEY warehouses_code_unique (code)');
            }
        }

        // Remove tenant_id from branch_shops table
        if (Schema::hasTable('branch_shops') && Schema::hasColumn('branch_shops', 'tenant_id')) {
            Schema::table('branch_shops', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_branch_shops_tenant_status');
                $table->dropIndex('idx_branch_shops_tenant_type');
                $table->dropIndex('idx_branch_shops_tenant_created');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            if (Schema::hasColumn('branch_shops', 'shop_code')) {
                DB::statement('ALTER TABLE branch_shops DROP KEY unique_tenant_shop_code');
                DB::statement('ALTER TABLE branch_shops ADD UNIQUE KEY branch_shops_shop_code_unique (shop_code)');
            }
        }

        // Remove tenant_id from permissions table
        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'tenant_id')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_permissions_tenant_name');
                $table->dropIndex('idx_permissions_tenant_module');
                $table->dropIndex('idx_permissions_tenant_action');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            if (Schema::hasColumn('permissions', 'name')) {
                DB::statement('ALTER TABLE permissions DROP KEY unique_tenant_permission_name');
                DB::statement('ALTER TABLE permissions ADD UNIQUE KEY permissions_name_unique (name)');
            }
        }

        // Remove tenant_id from roles table
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_roles_tenant_name');
                $table->dropIndex('idx_roles_tenant_active');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            if (Schema::hasColumn('roles', 'name')) {
                DB::statement('ALTER TABLE roles DROP KEY unique_tenant_role_name');
                DB::statement('ALTER TABLE roles ADD UNIQUE KEY roles_name_unique (name)');
            }
        }

        // Remove tenant_id from users table
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex('idx_users_tenant_email');
                $table->dropIndex('idx_users_tenant_status');
                $table->dropColumn('tenant_id');
            });
            
            // Restore original unique constraint
            DB::statement('ALTER TABLE users DROP KEY unique_tenant_email');
            DB::statement('ALTER TABLE users ADD UNIQUE KEY users_email_unique (email)');
        }
    }
};
