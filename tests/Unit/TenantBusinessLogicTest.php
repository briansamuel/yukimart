<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\TenantSetting;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class TenantBusinessLogicTest extends TestCase
{
    /**
     * Test tenant quota checking logic
     */
    public function test_tenant_quota_checking()
    {
        $tenant = new Tenant([
            'max_users' => 10,
            'current_users' => 5,
            'max_products' => 1000,
            'current_products' => 500,
            'storage_limit' => 1073741824, // 1GB
            'current_storage_used' => 536870912 // 512MB
        ]);

        // Test user quota
        $this->assertTrue($tenant->canAddUsers(3));
        $this->assertFalse($tenant->canAddUsers(6));
        $this->assertEquals(5, $tenant->getRemainingUsers());

        // Test product quota
        $this->assertTrue($tenant->canAddProducts(400));
        $this->assertFalse($tenant->canAddProducts(600));
        $this->assertEquals(500, $tenant->getRemainingProducts());

        // Test storage quota
        $this->assertTrue($tenant->hasStorageSpace(536870912)); // 512MB
        $this->assertFalse($tenant->hasStorageSpace(1073741824)); // 1GB
        $this->assertEquals(536870912, $tenant->getRemainingStorage());
    }

    /**
     * Test tenant feature management
     */
    public function test_tenant_feature_management()
    {
        $tenant = new Tenant([
            'features' => [
                'inventory_management' => true,
                'api_access' => false,
                'reporting' => true,
                'multi_branch' => false
            ]
        ]);

        // Test feature checking
        $this->assertTrue($tenant->hasFeature('inventory_management'));
        $this->assertFalse($tenant->hasFeature('api_access'));
        $this->assertTrue($tenant->hasFeature('reporting'));
        $this->assertFalse($tenant->hasFeature('multi_branch'));
        $this->assertFalse($tenant->hasFeature('non_existent_feature'));

        // Test feature enabling/disabling
        $tenant->enableFeature('api_access');
        $this->assertTrue($tenant->hasFeature('api_access'));

        $tenant->disableFeature('reporting');
        $this->assertFalse($tenant->hasFeature('reporting'));
    }

    /**
     * Test tenant expiry checking
     */
    public function test_tenant_expiry_checking()
    {
        // Test expired subscription
        $expiredTenant = new Tenant([
            'subscription_ends_at' => Carbon::now()->subDays(5)
        ]);
        $this->assertTrue($expiredTenant->isExpired());

        // Test active subscription
        $activeTenant = new Tenant([
            'subscription_ends_at' => Carbon::now()->addDays(30)
        ]);
        $this->assertFalse($activeTenant->isExpired());

        // Test near expiry
        $nearExpiryTenant = new Tenant([
            'subscription_ends_at' => Carbon::now()->addDays(3)
        ]);
        $this->assertTrue($nearExpiryTenant->isNearExpiry(7));
        $this->assertFalse($nearExpiryTenant->isNearExpiry(2));

        // Test trial expiry
        $trialTenant = new Tenant([
            'trial_ends_at' => Carbon::now()->subDays(1)
        ]);
        $this->assertTrue($trialTenant->isTrialExpired());
    }

    /**
     * Test tenant status management
     */
    public function test_tenant_status_management()
    {
        $tenant = new Tenant(['status' => Tenant::STATUS_ACTIVE]);

        // Test suspension
        $tenant->suspend('Payment overdue');
        $this->assertEquals(Tenant::STATUS_SUSPENDED, $tenant->status);
        $this->assertArrayHasKey('suspension_reason', $tenant->metadata);
        $this->assertEquals('Payment overdue', $tenant->metadata['suspension_reason']);

        // Test activation
        $tenant->activate();
        $this->assertEquals(Tenant::STATUS_ACTIVE, $tenant->status);
        $this->assertArrayHasKey('activated_at', $tenant->metadata);

        // Test expiration
        $tenant->expire();
        $this->assertEquals(Tenant::STATUS_EXPIRED, $tenant->status);
        $this->assertArrayHasKey('expired_at', $tenant->metadata);
    }

    /**
     * Test tenant user permission management
     */
    public function test_tenant_user_permission_management()
    {
        $tenantUser = new TenantUser([
            'permissions' => ['products.view', 'orders.create'],
            'restrictions' => ['no_delete_permissions']
        ]);

        // Test permission checking
        $this->assertTrue($tenantUser->hasPermission('products.view'));
        $this->assertTrue($tenantUser->hasPermission('orders.create'));
        $this->assertFalse($tenantUser->hasPermission('users.delete'));

        // Test permission adding
        $tenantUser->addPermission('reports.view');
        $this->assertTrue($tenantUser->hasPermission('reports.view'));

        // Test permission removal
        $tenantUser->removePermission('orders.create');
        $this->assertFalse($tenantUser->hasPermission('orders.create'));

        // Test restriction checking
        $this->assertTrue($tenantUser->hasRestriction('no_delete_permissions'));
        $this->assertFalse($tenantUser->hasRestriction('read_only_reports'));
    }

    /**
     * Test tenant user role-based methods
     */
    public function test_tenant_user_role_based_methods()
    {
        $owner = new TenantUser(['role' => TenantUser::ROLE_OWNER]);
        $admin = new TenantUser(['role' => TenantUser::ROLE_ADMIN]);
        $manager = new TenantUser(['role' => TenantUser::ROLE_MANAGER]);
        $staff = new TenantUser(['role' => TenantUser::ROLE_STAFF]);
        $viewer = new TenantUser(['role' => TenantUser::ROLE_VIEWER]);

        // Test isOwner
        $this->assertTrue($owner->isOwner());
        $this->assertFalse($admin->isOwner());

        // Test isAdmin (owner is also admin)
        $this->assertTrue($owner->isAdmin());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($manager->isAdmin());

        // Test canManageUsers
        $this->assertTrue($owner->canManageUsers());
        $this->assertTrue($admin->canManageUsers());
        $this->assertTrue($manager->canManageUsers());
        $this->assertFalse($staff->canManageUsers());
        $this->assertFalse($viewer->canManageUsers());

        // Test canManageProducts
        $this->assertTrue($owner->canManageProducts());
        $this->assertTrue($admin->canManageProducts());
        $this->assertTrue($manager->canManageProducts());
        $this->assertFalse($staff->canManageProducts());
        $this->assertFalse($viewer->canManageProducts());

        // Test canViewReports
        $this->assertTrue($owner->canViewReports());
        $this->assertTrue($admin->canViewReports());
        $this->assertTrue($manager->canViewReports());
        $this->assertFalse($staff->canViewReports());
        $this->assertFalse($viewer->canViewReports());
    }

    /**
     * Test tenant user access control
     */
    public function test_tenant_user_access_control()
    {
        $activeUser = new TenantUser([
            'is_active' => true,
            'invitation_status' => TenantUser::INVITATION_ACCEPTED,
            'access_expires_at' => null
        ]);

        $inactiveUser = new TenantUser([
            'is_active' => false
        ]);

        $expiredUser = new TenantUser([
            'is_active' => true,
            'access_expires_at' => Carbon::now()->subDays(1)
        ]);

        $pendingUser = new TenantUser([
            'is_active' => false,
            'invitation_status' => TenantUser::INVITATION_PENDING
        ]);

        // Test canAccess
        $this->assertTrue($activeUser->canAccess());
        $this->assertFalse($inactiveUser->canAccess());
        $this->assertFalse($expiredUser->canAccess());
        $this->assertFalse($pendingUser->canAccess());

        // Test isExpired
        $this->assertFalse($activeUser->isExpired());
        $this->assertTrue($expiredUser->isExpired());

        // Test isPending
        $this->assertFalse($activeUser->isPending());
        $this->assertTrue($pendingUser->isPending());
    }

    /**
     * Test tenant setting value casting
     */
    public function test_tenant_setting_value_casting()
    {
        // Test string value
        $stringSetting = new TenantSetting([
            'type' => TenantSetting::TYPE_STRING,
            'value' => 'test string'
        ]);
        $this->assertEquals('test string', $stringSetting->getTypedValue());

        // Test boolean values
        $booleanTrue = new TenantSetting([
            'type' => TenantSetting::TYPE_BOOLEAN,
            'value' => '1'
        ]);
        $this->assertTrue($booleanTrue->getTypedValue());

        $booleanFalse = new TenantSetting([
            'type' => TenantSetting::TYPE_BOOLEAN,
            'value' => '0'
        ]);
        $this->assertFalse($booleanFalse->getTypedValue());

        // Test integer value
        $integerSetting = new TenantSetting([
            'type' => TenantSetting::TYPE_INTEGER,
            'value' => '42'
        ]);
        $this->assertEquals(42, $integerSetting->getTypedValue());

        // Test decimal value
        $decimalSetting = new TenantSetting([
            'type' => TenantSetting::TYPE_DECIMAL,
            'value' => '10.50'
        ]);
        $this->assertEquals(10.50, $decimalSetting->getTypedValue());

        // Test JSON value
        $jsonSetting = new TenantSetting([
            'type' => TenantSetting::TYPE_JSON,
            'value' => '{"key": "value", "number": 123}'
        ]);
        $expected = ['key' => 'value', 'number' => 123];
        $this->assertEquals($expected, $jsonSetting->getTypedValue());
    }

    /**
     * Test tenant setting validation
     */
    public function test_tenant_setting_validation()
    {
        $setting = new TenantSetting([
            'validation_rules' => 'required|min:3|max:10'
        ]);

        // Test valid values
        $this->assertTrue($setting->validateValue('test'));
        $this->assertTrue($setting->validateValue('1234567890'));

        // Test invalid values
        $this->assertFalse($setting->validateValue(''));
        $this->assertFalse($setting->validateValue('ab'));
        $this->assertFalse($setting->validateValue('12345678901'));

        // Test options validation
        $optionsSetting = new TenantSetting([
            'options' => [
                'option1' => 'Option 1',
                'option2' => 'Option 2'
            ]
        ]);

        $this->assertTrue($optionsSetting->isValidOption('option1'));
        $this->assertFalse($optionsSetting->isValidOption('invalid_option'));
    }

    /**
     * Test tenant setting modification permissions
     */
    public function test_tenant_setting_modification_permissions()
    {
        $regularSetting = new TenantSetting([
            'is_readonly' => false,
            'is_system' => false
        ]);
        $this->assertTrue($regularSetting->canModify());

        $readonlySetting = new TenantSetting([
            'is_readonly' => true,
            'is_system' => false
        ]);
        $this->assertFalse($readonlySetting->canModify());

        $systemSetting = new TenantSetting([
            'is_readonly' => false,
            'is_system' => true
        ]);
        $this->assertFalse($systemSetting->canModify());
    }
}
