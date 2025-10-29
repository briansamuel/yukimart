<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend permissions table for Spatie compatibility
        Schema::table('permissions', function (Blueprint $table) {
            // Add guard_name for Spatie (required)
            if (!Schema::hasColumn('permissions', 'guard_name')) {
                $table->string('guard_name')->default('web')->after('name');
            }

            // Add sub_module for better categorization
            if (!Schema::hasColumn('permissions', 'sub_module')) {
                $table->string('sub_module')->nullable()->after('module');
            }
        });

        // Drop old unique constraint and add new one
        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropUnique('permissions_name_unique');
            });
        } catch (\Exception $e) {
            // Index might not exist, continue
        }

        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->unique(['name', 'guard_name'], 'permissions_name_guard_unique');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        // Extend roles table for Spatie compatibility
        Schema::table('roles', function (Blueprint $table) {
            // Add guard_name for Spatie (required)
            if (!Schema::hasColumn('roles', 'guard_name')) {
                $table->string('guard_name')->default('web')->after('name');
            }

            // Add tenant_id if not exists (for multi-tenant support)
            if (!Schema::hasColumn('roles', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
            }
        });

        // Add index if not exists
        try {
            Schema::table('roles', function (Blueprint $table) {
                $table->index(['tenant_id', 'name'], 'idx_roles_tenant_name');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        // Drop old unique constraint and add new one
        try {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique('roles_name_unique');
            });
        } catch (\Exception $e) {
            // Index might not exist, continue
        }

        try {
            Schema::table('roles', function (Blueprint $table) {
                $table->unique(['tenant_id', 'name', 'guard_name'], 'roles_tenant_name_guard_unique');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        // Create model_has_permissions table for Spatie (direct user permissions)
        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->unsignedBigInteger('tenant_id')->nullable(); // For multi-tenant support

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');

                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->primary(['tenant_id', 'permission_id', 'model_id', 'model_type'], 'model_has_permissions_tenant_permission_model_type_primary');
            });
        }

        // Create model_has_roles table for Spatie (user roles)
        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->unsignedBigInteger('tenant_id')->nullable(); // For multi-tenant support

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');

                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->primary(['tenant_id', 'role_id', 'model_id', 'model_type'], 'model_has_roles_tenant_role_model_type_primary');
            });
        }

        // Create role_has_permissions table for Spatie (role permissions) - CRITICAL!
        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Spatie tables
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');

        // Revert permissions table
        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropUnique('permissions_name_guard_unique');
            });
        } catch (\Exception) {
            // Index might not exist
        }

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'guard_name')) {
                $table->dropColumn('guard_name');
            }

            if (Schema::hasColumn('permissions', 'sub_module')) {
                $table->dropColumn('sub_module');
            }

            // Restore original unique constraint
            $table->unique('name', 'permissions_name_unique');
        });

        // Revert roles table
        try {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique('roles_tenant_name_guard_unique');
            });
        } catch (\Exception) {
            // Index might not exist
        }

        try {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex('idx_roles_tenant_name');
            });
        } catch (\Exception) {
            // Index might not exist
        }

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'guard_name')) {
                $table->dropColumn('guard_name');
            }

            if (Schema::hasColumn('roles', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }

            // Restore original unique constraint
            $table->unique('name', 'roles_name_unique');
        });
    }
};

