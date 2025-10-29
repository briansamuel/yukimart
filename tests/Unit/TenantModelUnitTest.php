<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\TenantSetting;
use PHPUnit\Framework\TestCase;

class TenantModelUnitTest extends TestCase
{
    /**
     * Test tenant model constants
     */
    public function test_tenant_model_constants()
    {
        // Test business type constants
        $this->assertEquals('retail', Tenant::BUSINESS_TYPE_RETAIL);
        $this->assertEquals('wholesale', Tenant::BUSINESS_TYPE_WHOLESALE);
        $this->assertEquals('restaurant', Tenant::BUSINESS_TYPE_RESTAURANT);

        // Test status constants
        $this->assertEquals('active', Tenant::STATUS_ACTIVE);
        $this->assertEquals('inactive', Tenant::STATUS_INACTIVE);
        $this->assertEquals('suspended', Tenant::STATUS_SUSPENDED);
        $this->assertEquals('trial', Tenant::STATUS_TRIAL);
        $this->assertEquals('expired', Tenant::STATUS_EXPIRED);

        // Test plan type constants
        $this->assertEquals('trial', Tenant::PLAN_TRIAL);
        $this->assertEquals('basic', Tenant::PLAN_BASIC);
        $this->assertEquals('premium', Tenant::PLAN_PREMIUM);
        $this->assertEquals('enterprise', Tenant::PLAN_ENTERPRISE);
        $this->assertEquals('custom', Tenant::PLAN_CUSTOM);
    }

    /**
     * Test tenant static methods
     */
    public function test_tenant_static_methods()
    {
        // Test getBusinessTypes
        $businessTypes = Tenant::getBusinessTypes();
        $this->assertIsArray($businessTypes);
        $this->assertArrayHasKey(Tenant::BUSINESS_TYPE_RETAIL, $businessTypes);
        $this->assertEquals('Bán lẻ', $businessTypes[Tenant::BUSINESS_TYPE_RETAIL]);

        // Test getStatuses
        $statuses = Tenant::getStatuses();
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey(Tenant::STATUS_ACTIVE, $statuses);
        $this->assertEquals('Hoạt động', $statuses[Tenant::STATUS_ACTIVE]);

        // Test getPlanTypes
        $planTypes = Tenant::getPlanTypes();
        $this->assertIsArray($planTypes);
        $this->assertArrayHasKey(Tenant::PLAN_BASIC, $planTypes);
        $this->assertEquals('Cơ bản', $planTypes[Tenant::PLAN_BASIC]);
    }

    /**
     * Test tenant user model constants
     */
    public function test_tenant_user_model_constants()
    {
        // Test role constants
        $this->assertEquals('owner', TenantUser::ROLE_OWNER);
        $this->assertEquals('admin', TenantUser::ROLE_ADMIN);
        $this->assertEquals('manager', TenantUser::ROLE_MANAGER);
        $this->assertEquals('staff', TenantUser::ROLE_STAFF);
        $this->assertEquals('viewer', TenantUser::ROLE_VIEWER);

        // Test invitation status constants
        $this->assertEquals('pending', TenantUser::INVITATION_PENDING);
        $this->assertEquals('accepted', TenantUser::INVITATION_ACCEPTED);
        $this->assertEquals('declined', TenantUser::INVITATION_DECLINED);
        $this->assertEquals('expired', TenantUser::INVITATION_EXPIRED);
    }

    /**
     * Test tenant user static methods
     */
    public function test_tenant_user_static_methods()
    {
        // Test getRoles
        $roles = TenantUser::getRoles();
        $this->assertIsArray($roles);
        $this->assertArrayHasKey(TenantUser::ROLE_OWNER, $roles);
        $this->assertEquals('Chủ sở hữu', $roles[TenantUser::ROLE_OWNER]);

        // Test getInvitationStatuses
        $statuses = TenantUser::getInvitationStatuses();
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey(TenantUser::INVITATION_PENDING, $statuses);
        $this->assertEquals('Chờ xác nhận', $statuses[TenantUser::INVITATION_PENDING]);
    }

    /**
     * Test tenant setting model constants
     */
    public function test_tenant_setting_model_constants()
    {
        // Test type constants
        $this->assertEquals('string', TenantSetting::TYPE_STRING);
        $this->assertEquals('integer', TenantSetting::TYPE_INTEGER);
        $this->assertEquals('boolean', TenantSetting::TYPE_BOOLEAN);
        $this->assertEquals('json', TenantSetting::TYPE_JSON);
        $this->assertEquals('array', TenantSetting::TYPE_ARRAY);
        $this->assertEquals('decimal', TenantSetting::TYPE_DECIMAL);

        // Test category constants
        $this->assertEquals('general', TenantSetting::CATEGORY_GENERAL);
        $this->assertEquals('inventory', TenantSetting::CATEGORY_INVENTORY);
        $this->assertEquals('sales', TenantSetting::CATEGORY_SALES);
        $this->assertEquals('notifications', TenantSetting::CATEGORY_NOTIFICATIONS);
        $this->assertEquals('integrations', TenantSetting::CATEGORY_INTEGRATIONS);
        $this->assertEquals('security', TenantSetting::CATEGORY_SECURITY);
        $this->assertEquals('appearance', TenantSetting::CATEGORY_APPEARANCE);
        $this->assertEquals('billing', TenantSetting::CATEGORY_BILLING);
    }

    /**
     * Test tenant setting static methods
     */
    public function test_tenant_setting_static_methods()
    {
        // Test getTypes
        $types = TenantSetting::getTypes();
        $this->assertIsArray($types);
        $this->assertArrayHasKey(TenantSetting::TYPE_STRING, $types);
        $this->assertEquals('Chuỗi', $types[TenantSetting::TYPE_STRING]);

        // Test getCategories
        $categories = TenantSetting::getCategories();
        $this->assertIsArray($categories);
        $this->assertArrayHasKey(TenantSetting::CATEGORY_GENERAL, $categories);
        $this->assertEquals('Chung', $categories[TenantSetting::CATEGORY_GENERAL]);
    }

    /**
     * Test model class existence
     */
    public function test_model_classes_exist()
    {
        $this->assertTrue(class_exists(Tenant::class));
        $this->assertTrue(class_exists(TenantUser::class));
        $this->assertTrue(class_exists(TenantSetting::class));
        $this->assertTrue(class_exists(\App\Models\TenantInvitation::class));
        $this->assertTrue(class_exists(\App\Models\TenantActivityLog::class));
    }

    /**
     * Test trait existence
     */
    public function test_trait_classes_exist()
    {
        $this->assertTrue(trait_exists(\App\Traits\TenantScoped::class));
        $this->assertTrue(class_exists(\App\Scopes\TenantScope::class));
    }

    /**
     * Test factory classes exist
     */
    public function test_factory_classes_exist()
    {
        $this->assertTrue(class_exists(\Database\Factories\TenantFactory::class));
        $this->assertTrue(class_exists(\Database\Factories\TenantUserFactory::class));
        $this->assertTrue(class_exists(\Database\Factories\TenantSettingFactory::class));
    }

    /**
     * Test model fillable attributes
     */
    public function test_model_fillable_attributes()
    {
        $tenant = new Tenant();
        $fillable = $tenant->getFillable();
        
        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('plan_type', $fillable);

        $tenantUser = new TenantUser();
        $userFillable = $tenantUser->getFillable();
        
        $this->assertContains('tenant_id', $userFillable);
        $this->assertContains('user_id', $userFillable);
        $this->assertContains('role', $userFillable);
        $this->assertContains('permissions', $userFillable);
        $this->assertContains('is_active', $userFillable);

        $tenantSetting = new TenantSetting();
        $settingFillable = $tenantSetting->getFillable();
        
        $this->assertContains('tenant_id', $settingFillable);
        $this->assertContains('category', $settingFillable);
        $this->assertContains('key', $settingFillable);
        $this->assertContains('value', $settingFillable);
        $this->assertContains('type', $settingFillable);
    }

    /**
     * Test model casts
     */
    public function test_model_casts()
    {
        $tenant = new Tenant();
        $casts = $tenant->getCasts();
        
        $this->assertArrayHasKey('settings', $casts);
        $this->assertEquals('array', $casts['settings']);
        $this->assertArrayHasKey('features', $casts);
        $this->assertEquals('array', $casts['features']);

        $tenantUser = new TenantUser();
        $userCasts = $tenantUser->getCasts();
        
        $this->assertArrayHasKey('permissions', $userCasts);
        $this->assertEquals('array', $userCasts['permissions']);
        $this->assertArrayHasKey('is_active', $userCasts);
        $this->assertEquals('boolean', $userCasts['is_active']);

        $tenantSetting = new TenantSetting();
        $settingCasts = $tenantSetting->getCasts();
        
        $this->assertArrayHasKey('is_public', $settingCasts);
        $this->assertEquals('boolean', $settingCasts['is_public']);
        $this->assertArrayHasKey('is_readonly', $settingCasts);
        $this->assertEquals('boolean', $settingCasts['is_readonly']);
    }

    /**
     * Test model table names
     */
    public function test_model_table_names()
    {
        $tenant = new Tenant();
        $this->assertEquals('tenants', $tenant->getTable());

        $tenantUser = new TenantUser();
        $this->assertEquals('tenant_users', $tenantUser->getTable());

        $tenantSetting = new TenantSetting();
        $this->assertEquals('tenant_settings', $tenantSetting->getTable());
    }

    /**
     * Test model uses traits
     */
    public function test_model_uses_traits()
    {
        $tenant = new Tenant();
        $tenantTraits = class_uses($tenant);
        
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $tenantTraits);
        $this->assertContains('Illuminate\Database\Eloquent\SoftDeletes', $tenantTraits);

        $tenantUser = new TenantUser();
        $userTraits = class_uses($tenantUser);
        
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $userTraits);

        $tenantSetting = new TenantSetting();
        $settingTraits = class_uses($tenantSetting);
        
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $settingTraits);
    }
}
