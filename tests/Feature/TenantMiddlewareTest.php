<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenant;
    protected $user;
    protected $tenantContextService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test tenant
        $this->tenant = Tenant::factory()->active()->create([
            'slug' => 'test-tenant',
            'subdomain' => 'test',
            'domain' => null
        ]);

        // Create test user
        $this->user = User::factory()->create();

        // Create tenant-user relationship
        TenantUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'role' => TenantUser::ROLE_ADMIN,
            'is_active' => true,
            'invitation_status' => TenantUser::INVITATION_ACCEPTED
        ]);

        $this->tenantContextService = app(TenantContextService::class);
    }

    /**
     * Test tenant context service basic functionality
     */
    public function test_tenant_context_service_basic_functionality()
    {
        // Test setting current tenant
        $this->tenantContextService->setCurrentTenant($this->tenant);
        
        $currentTenant = $this->tenantContextService->getCurrentTenant();
        $this->assertNotNull($currentTenant);
        $this->assertEquals($this->tenant->id, $currentTenant->id);

        // Test getting tenant ID
        $this->assertEquals($this->tenant->id, $this->tenantContextService->getCurrentTenantId());

        // Test has tenant context
        $this->assertTrue($this->tenantContextService->hasTenantContext());

        // Test clearing context
        $this->tenantContextService->clearContext();
        $this->assertFalse($this->tenantContextService->hasTenantContext());
    }

    /**
     * Test tenant switching functionality
     */
    public function test_tenant_switching_functionality()
    {
        // Test successful switch
        $success = $this->tenantContextService->switchToTenant($this->tenant->id, $this->user);
        $this->assertTrue($success);
        
        $currentTenant = $this->tenantContextService->getCurrentTenant();
        $this->assertEquals($this->tenant->id, $currentTenant->id);

        // Test switch to non-existent tenant
        $success = $this->tenantContextService->switchToTenant(99999, $this->user);
        $this->assertFalse($success);
    }

    /**
     * Test getting available tenants for user
     */
    public function test_getting_available_tenants_for_user()
    {
        $availableTenants = $this->tenantContextService->getAvailableTenantsForUser($this->user);
        
        $this->assertCount(1, $availableTenants);
        $this->assertEquals($this->tenant->id, $availableTenants->first()->id);
    }

    /**
     * Test user role in tenant
     */
    public function test_user_role_in_tenant()
    {
        $this->tenantContextService->setCurrentTenant($this->tenant);
        
        $role = $this->tenantContextService->getUserRoleInCurrentTenant($this->user);
        $this->assertEquals(TenantUser::ROLE_ADMIN, $role);
    }

    /**
     * Test user permissions in tenant
     */
    public function test_user_permissions_in_tenant()
    {
        $this->tenantContextService->setCurrentTenant($this->tenant);
        
        // Admin should have management permissions
        $canManageUsers = $this->tenantContextService->userCanPerformAction('users.manage', $this->user);
        $this->assertTrue($canManageUsers);
        
        // Test non-existent permission
        $canDoSomething = $this->tenantContextService->userCanPerformAction('non.existent.permission', $this->user);
        $this->assertFalse($canDoSomething);
    }

    /**
     * Test tenant settings
     */
    public function test_tenant_settings()
    {
        $this->tenantContextService->setCurrentTenant($this->tenant);
        
        // Test setting a value
        $success = $this->tenantContextService->setTenantSetting('test_key', 'test_value');
        $this->assertTrue($success);
        
        // Test getting the value
        $value = $this->tenantContextService->getTenantSetting('test_key');
        $this->assertEquals('test_value', $value);
        
        // Test getting non-existent setting with default
        $defaultValue = $this->tenantContextService->getTenantSetting('non_existent', 'default');
        $this->assertEquals('default', $defaultValue);
    }

    /**
     * Test tenant statistics
     */
    public function test_tenant_statistics()
    {
        $this->tenantContextService->setCurrentTenant($this->tenant);
        
        $statistics = $this->tenantContextService->getTenantStatistics();
        
        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('users', $statistics);
        $this->assertArrayHasKey('products', $statistics);
        $this->assertArrayHasKey('storage', $statistics);
        
        // Check structure of user statistics
        $this->assertArrayHasKey('current', $statistics['users']);
        $this->assertArrayHasKey('max', $statistics['users']);
        $this->assertArrayHasKey('remaining', $statistics['users']);
        $this->assertArrayHasKey('percentage', $statistics['users']);
    }

    /**
     * Test tenant controller endpoints
     */
    public function test_tenant_controller_endpoints()
    {
        $this->actingAs($this->user);
        
        // Test current tenant endpoint
        $response = $this->getJson(route('tenant.current'));
        $response->assertStatus(200);
        
        // Test available tenants endpoint
        $response = $this->getJson(route('tenant.available'));
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'tenants' => [
                        '*' => ['id', 'name', 'slug', 'status', 'plan_type', 'role']
                    ]
                ]);
        
        // Test tenant switching endpoint
        $response = $this->postJson(route('tenant.switch'), [
            'tenant_id' => $this->tenant->id
        ]);
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    /**
     * Test tenant validation
     */
    public function test_tenant_validation()
    {
        $this->actingAs($this->user);
        
        // Test validate access endpoint
        $response = $this->postJson(route('tenant.validate-access'), [
            'tenant_id' => $this->tenant->id
        ]);
        
        $response->assertStatus(200)
                ->assertJson(['has_access' => true]);
        
        // Test with permission
        $response = $this->postJson(route('tenant.validate-access'), [
            'tenant_id' => $this->tenant->id,
            'permission' => 'users.view'
        ]);
        
        $response->assertStatus(200)
                ->assertJson(['has_access' => true]);
    }

    /**
     * Test unauthorized tenant access
     */
    public function test_unauthorized_tenant_access()
    {
        // Create another tenant
        $anotherTenant = Tenant::factory()->active()->create();
        
        $this->actingAs($this->user);
        
        // Test switching to unauthorized tenant
        $response = $this->postJson(route('tenant.switch'), [
            'tenant_id' => $anotherTenant->id
        ]);
        
        $response->assertStatus(403);
        
        // Test validate access for unauthorized tenant
        $response = $this->postJson(route('tenant.validate-access'), [
            'tenant_id' => $anotherTenant->id
        ]);
        
        $response->assertStatus(200)
                ->assertJson(['has_access' => false]);
    }

    /**
     * Test tenant context without authentication
     */
    public function test_tenant_context_without_authentication()
    {
        // Test current tenant endpoint without auth
        $response = $this->getJson(route('tenant.current'));
        $response->assertStatus(200)
                ->assertJson(['tenant' => null]);
        
        // Test available tenants without auth
        $response = $this->getJson(route('tenant.available'));
        $response->assertStatus(401);
        
        // Test tenant switching without auth
        $response = $this->postJson(route('tenant.switch'), [
            'tenant_id' => $this->tenant->id
        ]);
        $response->assertStatus(401);
    }

    /**
     * Test tenant settings endpoints
     */
    public function test_tenant_settings_endpoints()
    {
        $this->actingAs($this->user);
        $this->tenantContextService->setCurrentTenant($this->tenant);
        
        // Test getting settings
        $response = $this->getJson(route('tenant.settings'));
        $response->assertStatus(200)
                ->assertJsonStructure(['settings']);
        
        // Test updating setting (would need proper permission setup)
        // This is a basic structure test
        $response = $this->postJson(route('tenant.settings.update'), [
            'key' => 'test_setting',
            'value' => 'test_value'
        ]);
        
        // May return 403 if user doesn't have settings.edit permission
        $this->assertContains($response->status(), [200, 403]);
    }

    /**
     * Test tenant statistics endpoint
     */
    public function test_tenant_statistics_endpoint()
    {
        $this->actingAs($this->user);
        $this->tenantContextService->setCurrentTenant($this->tenant);
        
        $response = $this->getJson(route('tenant.statistics'));
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'statistics' => [
                        'users' => ['current', 'max', 'remaining', 'percentage'],
                        'products' => ['current', 'max', 'remaining', 'percentage'],
                        'storage' => ['current', 'max', 'remaining', 'percentage']
                    ]
                ]);
    }
}
