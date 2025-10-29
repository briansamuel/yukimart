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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            
            // Basic tenant information
            $table->string('name'); // Tên công ty/tổ chức
            $table->string('slug', 100)->unique(); // Slug for URL (e.g., 'company-abc')
            $table->string('subdomain', 100)->unique()->nullable(); // Subdomain (e.g., 'company-abc.yukimart.local')
            $table->string('domain', 255)->unique()->nullable(); // Custom domain (e.g., 'company-abc.com')
            
            // Contact information
            $table->string('email')->nullable(); // Contact email
            $table->string('phone', 20)->nullable(); // Contact phone
            $table->text('address')->nullable(); // Business address
            $table->string('tax_number', 50)->nullable(); // Tax identification number
            
            // Business information
            $table->enum('business_type', [
                'retail', 'wholesale', 'restaurant', 'service', 'manufacturing', 'other'
            ])->default('retail'); // Loại hình kinh doanh
            $table->string('industry', 100)->nullable(); // Ngành nghề
            $table->integer('employee_count')->nullable(); // Số lượng nhân viên
            
            // Tenant status and subscription
            $table->enum('status', [
                'active', 'inactive', 'suspended', 'trial', 'expired'
            ])->default('trial'); // Trạng thái tenant
            
            $table->enum('plan_type', [
                'trial', 'basic', 'premium', 'enterprise', 'custom'
            ])->default('trial'); // Gói dịch vụ
            
            // Subscription dates
            $table->date('trial_ends_at')->nullable(); // Ngày hết hạn trial
            $table->date('subscription_starts_at')->nullable(); // Ngày bắt đầu subscription
            $table->date('subscription_ends_at')->nullable(); // Ngày hết hạn subscription
            
            // Limits and quotas
            $table->integer('max_users')->default(5); // Giới hạn số user
            $table->integer('max_branch_shops')->default(1); // Giới hạn số chi nhánh
            $table->integer('max_products')->default(1000); // Giới hạn số sản phẩm
            $table->bigInteger('storage_limit')->default(1073741824); // Giới hạn storage (1GB in bytes)
            $table->integer('api_rate_limit')->default(1000); // API rate limit per hour
            
            // Current usage tracking
            $table->integer('current_users')->default(0); // Số user hiện tại
            $table->integer('current_branch_shops')->default(0); // Số chi nhánh hiện tại
            $table->integer('current_products')->default(0); // Số sản phẩm hiện tại
            $table->bigInteger('current_storage_used')->default(0); // Storage đã sử dụng
            
            // Configuration and settings
            $table->json('settings')->nullable(); // Tenant-specific settings
            $table->json('features')->nullable(); // Enabled features
            $table->string('timezone', 50)->default('Asia/Ho_Chi_Minh'); // Timezone
            $table->string('currency', 3)->default('VND'); // Currency code
            $table->string('language', 5)->default('vi'); // Default language
            
            // Branding and customization
            $table->string('logo_url')->nullable(); // Logo URL
            $table->string('favicon_url')->nullable(); // Favicon URL
            $table->json('theme_settings')->nullable(); // Theme customization
            $table->text('custom_css')->nullable(); // Custom CSS
            
            // Database and technical settings
            $table->string('database_name', 100)->nullable(); // Custom database name (for future multi-DB)
            $table->json('integration_settings')->nullable(); // Third-party integrations
            $table->json('notification_settings')->nullable(); // Notification preferences
            
            // Billing information
            $table->decimal('monthly_fee', 10, 2)->default(0); // Monthly subscription fee
            $table->decimal('setup_fee', 10, 2)->default(0); // One-time setup fee
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('next_billing_date')->nullable(); // Next billing date
            
            // Owner and management
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null'); // Tenant owner
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Who created this tenant
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Last updated by
            
            // Audit and tracking
            $table->timestamp('last_activity_at')->nullable(); // Last activity timestamp
            $table->ipAddress('last_activity_ip')->nullable(); // Last activity IP
            $table->json('metadata')->nullable(); // Additional metadata
            
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at for soft delete
            
            // Indexes for performance
            $table->index(['status', 'plan_type'], 'idx_tenant_status_plan');
            $table->index(['created_at'], 'idx_tenant_created');
            $table->index(['subscription_ends_at'], 'idx_tenant_subscription');
            $table->index(['last_activity_at'], 'idx_tenant_activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
    }
};
