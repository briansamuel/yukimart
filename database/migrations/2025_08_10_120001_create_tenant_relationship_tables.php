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
        // Tenant Users relationship table (many-to-many)
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Role in tenant
            $table->enum('role', [
                'owner',        // Chủ sở hữu tenant
                'admin',        // Quản trị viên
                'manager',      // Quản lý
                'staff',        // Nhân viên
                'viewer'        // Chỉ xem
            ])->default('staff');
            
            // Permissions and access
            $table->json('permissions')->nullable(); // Specific permissions for this tenant
            $table->json('restrictions')->nullable(); // Access restrictions
            
            // Status and dates
            $table->boolean('is_active')->default(true); // Active status
            $table->boolean('is_primary')->default(false); // Primary tenant for user
            $table->timestamp('joined_at')->nullable(); // When user joined this tenant
            $table->timestamp('last_access_at')->nullable(); // Last access to this tenant
            $table->date('access_expires_at')->nullable(); // Access expiration date
            
            // Invitation and approval
            $table->enum('invitation_status', [
                'pending', 'accepted', 'declined', 'expired'
            ])->nullable(); // Invitation status
            $table->string('invitation_token', 100)->nullable(); // Invitation token
            $table->timestamp('invitation_sent_at')->nullable(); // When invitation was sent
            $table->timestamp('invitation_expires_at')->nullable(); // Invitation expiration
            
            // Audit fields
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null'); // Who invited this user
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Who approved this user
            $table->timestamp('approved_at')->nullable(); // When was approved
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['tenant_id', 'user_id'], 'unique_tenant_user');
            
            // Indexes
            $table->index(['tenant_id', 'role'], 'idx_tenant_user_role');
            $table->index(['user_id', 'is_active'], 'idx_user_active_tenants');
            $table->index(['invitation_status', 'invitation_expires_at'], 'idx_invitation_status');
        });

        // Tenant Settings table
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            
            // Foreign key
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Setting details
            $table->string('category', 100)->default('general'); // Setting category (general, billing, features, etc.)
            $table->string('key', 255); // Setting key
            $table->text('value')->nullable(); // Setting value
            $table->enum('type', [
                'string', 'integer', 'boolean', 'json', 'array', 'decimal'
            ])->default('string'); // Value type
            
            // Metadata
            $table->string('label')->nullable(); // Human-readable label
            $table->text('description')->nullable(); // Setting description
            $table->text('validation_rules')->nullable(); // Validation rules
            $table->json('options')->nullable(); // Available options (for select/radio)
            
            // Access control
            $table->boolean('is_public')->default(false); // Can be accessed by non-admin users
            $table->boolean('is_readonly')->default(false); // Cannot be modified
            $table->boolean('is_system')->default(false); // System setting (cannot be deleted)
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['tenant_id', 'key'], 'unique_tenant_setting');
            
            // Indexes
            $table->index(['tenant_id', 'category'], 'idx_tenant_setting_category');
            $table->index(['key', 'type'], 'idx_setting_key_type');
        });

        // Tenant Invitations table
        Schema::create('tenant_invitations', function (Blueprint $table) {
            $table->id();
            
            // Foreign key
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Invitation details
            $table->string('email'); // Email to invite
            $table->string('token', 100)->unique(); // Invitation token
            $table->enum('role', [
                'admin', 'manager', 'staff', 'viewer'
            ])->default('staff'); // Proposed role
            
            // Permissions
            $table->json('permissions')->nullable(); // Specific permissions
            $table->text('message')->nullable(); // Personal message
            
            // Status and dates
            $table->enum('status', [
                'pending', 'accepted', 'declined', 'expired', 'cancelled'
            ])->default('pending');
            $table->timestamp('expires_at'); // Expiration date
            $table->timestamp('accepted_at')->nullable(); // When accepted
            $table->timestamp('declined_at')->nullable(); // When declined
            
            // Audit
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade'); // Who sent invitation
            $table->foreignId('accepted_by')->nullable()->constrained('users')->onDelete('set null'); // Who accepted
            
            // Tracking
            $table->integer('email_sent_count')->default(0); // How many emails sent
            $table->timestamp('last_email_sent_at')->nullable(); // Last email sent
            $table->ipAddress('accepted_from_ip')->nullable(); // IP when accepted
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'status'], 'idx_tenant_invitation_status');
            $table->index(['email', 'status'], 'idx_invitation_email_status');
            $table->index(['token'], 'idx_invitation_token');
            $table->index(['expires_at'], 'idx_invitation_expires');
        });

        // Tenant Activity Log table
        Schema::create('tenant_activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Activity details
            $table->string('action', 100); // Action performed (login, create_order, etc.)
            $table->string('entity_type', 100)->nullable(); // Entity type (order, product, etc.)
            $table->unsignedBigInteger('entity_id')->nullable(); // Entity ID
            $table->text('description')->nullable(); // Human-readable description
            
            // Request details
            $table->string('ip_address', 45)->nullable(); // IP address
            $table->string('user_agent')->nullable(); // User agent
            $table->string('method', 10)->nullable(); // HTTP method
            $table->string('url')->nullable(); // Request URL
            
            // Additional data
            $table->json('properties')->nullable(); // Additional properties
            $table->json('old_values')->nullable(); // Old values (for updates)
            $table->json('new_values')->nullable(); // New values (for updates)
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'created_at'], 'idx_tenant_activity_date');
            $table->index(['user_id', 'created_at'], 'idx_user_activity_date');
            $table->index(['action', 'entity_type'], 'idx_activity_action_entity');
            $table->index(['entity_type', 'entity_id'], 'idx_activity_entity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_activity_logs');
        Schema::dropIfExists('tenant_invitations');
        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('tenant_users');
    }
};
