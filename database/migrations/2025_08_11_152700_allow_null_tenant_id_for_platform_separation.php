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
        // Modify users table to allow NULL tenant_id for platform users
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            // Drop the foreign key constraint first
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_tenant_id_foreign');
            });
            
            // Modify the column to allow NULL
            DB::statement('ALTER TABLE users MODIFY tenant_id BIGINT UNSIGNED NULL');
            
            // Re-add the foreign key constraint with NULL allowed
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
            
            // Update unique constraint to handle NULL tenant_id properly
            DB::statement('ALTER TABLE users DROP KEY unique_tenant_email');
            
            // Create a new unique constraint that allows multiple NULL values
            // This ensures that platform users (tenant_id = NULL) can have unique emails
            // and tenant users must have unique emails within their tenant
            DB::statement('
                CREATE UNIQUE INDEX unique_tenant_email 
                ON users (tenant_id, email)
            ');
        }

        // Modify roles table to allow NULL tenant_id for platform roles
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'tenant_id')) {
            // Roles table doesn't have foreign key constraint, just modify the column
            DB::statement('ALTER TABLE roles MODIFY tenant_id BIGINT UNSIGNED NULL');

            // Add foreign key constraint with NULL allowed
            Schema::table('roles', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });

            // Update unique constraint to handle NULL tenant_id properly
            try {
                DB::statement('ALTER TABLE roles DROP KEY unique_tenant_role_name');
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }

            // Create a new unique constraint that allows multiple NULL values
            DB::statement('
                CREATE UNIQUE INDEX unique_tenant_role_name
                ON roles (tenant_id, name)
            ');
        }

        // Modify permissions table to allow NULL tenant_id for platform permissions
        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'tenant_id')) {
            // Drop the foreign key constraint first
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign('permissions_tenant_id_foreign');
            });
            
            // Modify the column to allow NULL
            DB::statement('ALTER TABLE permissions MODIFY tenant_id BIGINT UNSIGNED NULL');
            
            // Re-add the foreign key constraint with NULL allowed
            Schema::table('permissions', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
            
            // Update unique constraint to handle NULL tenant_id properly
            DB::statement('ALTER TABLE permissions DROP KEY unique_tenant_permission_name');
            
            // Create a new unique constraint that allows multiple NULL values
            DB::statement('
                CREATE UNIQUE INDEX unique_tenant_permission_name 
                ON permissions (tenant_id, name)
            ');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert users table changes
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            // Drop the foreign key constraint
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
            
            // Remove the unique constraint
            DB::statement('ALTER TABLE users DROP KEY unique_tenant_email');
            
            // Modify the column back to NOT NULL with default
            DB::statement('ALTER TABLE users MODIFY tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1');
            
            // Re-add the foreign key constraint
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
            
            // Re-add the original unique constraint
            DB::statement('ALTER TABLE users ADD UNIQUE KEY unique_tenant_email (tenant_id, email)');
        }

        // Revert roles table changes
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'tenant_id')) {
            // Drop the foreign key constraint
            Schema::table('roles', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
            
            // Remove the unique constraint
            DB::statement('ALTER TABLE roles DROP KEY unique_tenant_role_name');
            
            // Modify the column back to NOT NULL with default
            DB::statement('ALTER TABLE roles MODIFY tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1');
            
            // Re-add the foreign key constraint
            Schema::table('roles', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
            
            // Re-add the original unique constraint
            DB::statement('ALTER TABLE roles ADD UNIQUE KEY unique_tenant_role_name (tenant_id, name)');
        }

        // Revert permissions table changes
        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'tenant_id')) {
            // Drop the foreign key constraint
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
            
            // Remove the unique constraint
            DB::statement('ALTER TABLE permissions DROP KEY unique_tenant_permission_name');
            
            // Modify the column back to NOT NULL with default
            DB::statement('ALTER TABLE permissions MODIFY tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1');
            
            // Re-add the foreign key constraint
            Schema::table('permissions', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
            
            // Re-add the original unique constraint
            DB::statement('ALTER TABLE permissions ADD UNIQUE KEY unique_tenant_permission_name (tenant_id, name)');
        }
    }
};
