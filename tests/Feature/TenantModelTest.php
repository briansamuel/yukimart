<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\TenantSetting;
use App\Models\TenantInvitation;
use App\Models\TenantActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class TenantModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test tenant model creation
     */
    public function test_tenant_can_be_created()
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'email' => 'test@company.com'
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Company',
            'slug' => 'test-company',
            'email' => 'test@company.com'
        ]);

        $this->assertEquals('Test Company', $tenant->name);
        $this->assertEquals('test-company', $tenant->slug);
    }

    /**
     * Test tenant business logic methods
     */
    public function test_tenant_business_logic()
    {
        $tenant = Tenant::factory()->create([
            'max_users' => 10,
            'current_users' => 5,
            'max_products' => 1000,
            'current_products' => 500,
            'storage_limit' => 1073741824, // 1GB
            'current_storage_used' => 536870912 // 512MB
        ]);

        // Test quota checking
        $this->assertTrue($tenant->canAddUsers(3));
        $this->assertFalse($tenant->canAddUsers(6));
        
        $this->assertTrue($tenant->canAddProducts(400));
        $this->assertFalse($tenant->canAddProducts(600));
        
        $this->assertTrue($tenant->hasStorageSpace(536870912)); // 512MB
        $this->assertFalse($tenant->hasStorageSpace(1073741824)); // 1GB
    }

    /**
     * Test tenant features
     */
    public function test_tenant_features()
    {
        $tenant = Tenant::factory()->create([
            'features' => [
                'inventory_management' => true,
                'api_access' => false,
                'reporting' => true
            ]
        ]);

        $this->assertTrue($tenant->hasFeature('inventory_management'));
        $this->assertFalse($tenant->hasFeature('api_access'));
        $this->assertTrue($tenant->hasFeature('reporting'));
        $this->assertFalse($tenant->hasFeature('non_existent_feature'));
    }

    /**
     * Test tenant status management
     */
    public function test_tenant_status_management()
    {
        $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);

        // Test suspension
        $tenant->suspend('Payment overdue');
        $this->assertEquals(Tenant::STATUS_SUSPENDED, $tenant->status);
        $this->assertArrayHasKey('suspension_reason', $tenant->metadata);

        // Test activation
        $tenant->activate();
        $this->assertEquals(Tenant::STATUS_ACTIVE, $tenant->status);
        $this->assertArrayHasKey('activated_at', $tenant->metadata);
    }

    /**
     * Test tenant expiry checking
     */
    public function test_tenant_expiry_checking()
    {
        // Test expired subscription
        $expiredTenant = Tenant::factory()->create([
            'subscription_ends_at' => Carbon::now()->subDays(5)
        ]);
        $this->assertTrue($expiredTenant->isExpired());

        // Test active subscription
        $activeTenant = Tenant::factory()->create([
            'subscription_ends_at' => Carbon::now()->addDays(30)
        ]);
        $this->assertFalse($activeTenant->isExpired());

        // Test near expiry
        $nearExpiryTenant = Tenant::factory()->create([
            'subscription_ends_at' => Carbon::now()->addDays(3)
        ]);
        $this->assertTrue($nearExpiryTenant->isNearExpiry(7));
        $this->assertFalse($nearExpiryTenant->isNearExpiry(2));
    }

    /**
     * Test tenant-user relationships
     */
    public function test_tenant_user_relationships()
    {
        $tenant = Tenant::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create tenant-user relationships
        TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user1->id,
            'role' => TenantUser::ROLE_OWNER,
            'is_active' => true
        ]);

        TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user2->id,
            'role' => TenantUser::ROLE_STAFF,
            'is_active' => true
        ]);

        // Test relationships
        $this->assertCount(2, $tenant->users);
        $this->assertCount(1, $tenant->owners());
        $this->assertCount(2, $tenant->activeUsers);
    }

    /**
     * Test tenant settings
     */
    public function test_tenant_settings()
    {
        $tenant = Tenant::factory()->create();

        // Test setting creation
        $tenant->setSetting('company_name', 'Test Company', 'general');
        $tenant->setSetting('tax_rate', 10.5, 'sales');
        $tenant->setSetting('email_notifications', true, 'notifications');

        // Test setting retrieval
        $this->assertEquals('Test Company', $tenant->getSetting('company_name'));
        $this->assertEquals(10.5, $tenant->getSetting('tax_rate'));
        $this->assertTrue($tenant->getSetting('email_notifications'));
        $this->assertNull($tenant->getSetting('non_existent_setting'));
        $this->assertEquals('default', $tenant->getSetting('non_existent_setting', 'default'));

        // Test database storage
        $this->assertDatabaseHas('tenant_settings', [
            'tenant_id' => $tenant->id,
            'key' => 'company_name',
            'value' => 'Test Company'
        ]);
    }

    /**
     * Test tenant scopes
     */
    public function test_tenant_scopes()
    {
        $activeTenant = Tenant::factory()->active()->create();
        $trialTenant = Tenant::factory()->trial()->create();
        $expiredTenant = Tenant::factory()->expired()->create();
        $basicTenant = Tenant::factory()->basicPlan()->create();
        $premiumTenant = Tenant::factory()->premiumPlan()->create();

        // Test active scope
        $activeResults = Tenant::active()->get();
        $this->assertTrue($activeResults->contains($activeTenant));
        $this->assertFalse($activeResults->contains($expiredTenant));

        // Test trial scope
        $trialResults = Tenant::trial()->get();
        $this->assertTrue($trialResults->contains($trialTenant));

        // Test plan scope
        $basicResults = Tenant::byPlan(Tenant::PLAN_BASIC)->get();
        $this->assertTrue($basicResults->contains($basicTenant));
        $this->assertFalse($basicResults->contains($premiumTenant));
    }

    /**
     * Test tenant activity logging
     */
    public function test_tenant_activity_logging()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();

        // Simulate user login
        $this->actingAs($user);

        // Record activity
        $tenant->recordActivity('test_action', ['key' => 'value']);

        // Check activity was logged
        $this->assertDatabaseHas('tenant_activity_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'action' => 'test_action'
        ]);

        // Check last activity was updated
        $tenant->refresh();
        $this->assertNotNull($tenant->last_activity_at);
    }

    /**
     * Test tenant usage statistics update
     */
    public function test_tenant_usage_statistics_update()
    {
        $tenant = Tenant::factory()->create();
        
        // Create some related data
        $user1 = User::factory()->create(['tenant_id' => $tenant->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant->id]);
        
        TenantUser::factory()->create(['tenant_id' => $tenant->id, 'user_id' => $user1->id]);
        TenantUser::factory()->create(['tenant_id' => $tenant->id, 'user_id' => $user2->id]);

        // Update usage stats
        $tenant->updateUsageStats();

        // Check stats were updated
        $tenant->refresh();
        $this->assertEquals(2, $tenant->current_users);
    }

    /**
     * Test tenant factory states
     */
    public function test_tenant_factory_states()
    {
        // Test trial state
        $trialTenant = Tenant::factory()->trial()->create();
        $this->assertEquals(Tenant::STATUS_TRIAL, $trialTenant->status);
        $this->assertEquals(Tenant::PLAN_TRIAL, $trialTenant->plan_type);
        $this->assertNotNull($trialTenant->trial_ends_at);

        // Test active state
        $activeTenant = Tenant::factory()->active()->create();
        $this->assertEquals(Tenant::STATUS_ACTIVE, $activeTenant->status);
        $this->assertNotNull($activeTenant->subscription_starts_at);

        // Test suspended state
        $suspendedTenant = Tenant::factory()->suspended()->create();
        $this->assertEquals(Tenant::STATUS_SUSPENDED, $suspendedTenant->status);
        $this->assertArrayHasKey('suspension_reason', $suspendedTenant->metadata);

        // Test plan states
        $basicTenant = Tenant::factory()->basicPlan()->create();
        $this->assertEquals(Tenant::PLAN_BASIC, $basicTenant->plan_type);
        $this->assertEquals(5, $basicTenant->max_users);

        $premiumTenant = Tenant::factory()->premiumPlan()->create();
        $this->assertEquals(Tenant::PLAN_PREMIUM, $premiumTenant->plan_type);
        $this->assertEquals(25, $premiumTenant->max_users);
    }

    /**
     * Test tenant accessors
     */
    public function test_tenant_accessors()
    {
        $tenant = Tenant::factory()->create([
            'status' => Tenant::STATUS_ACTIVE,
            'plan_type' => Tenant::PLAN_PREMIUM,
            'max_users' => 25,
            'current_users' => 10,
            'subscription_ends_at' => Carbon::now()->addDays(30)
        ]);

        // Test status badge
        $this->assertStringContainsString('Hoạt động', $tenant->status_badge);

        // Test plan badge
        $this->assertStringContainsString('Cao cấp', $tenant->plan_badge);

        // Test usage percentage
        $usage = $tenant->usage_percentage;
        $this->assertEquals(40, $usage['users']); // 10/25 * 100

        // Test is_active
        $this->assertTrue($tenant->is_active);

        // Test days until expiry
        $this->assertEquals(30, $tenant->days_until_expiry);
    }
}
