<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TenantSettingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenant;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
    }

    /**
     * Test tenant setting creation
     */
    public function test_tenant_setting_can_be_created()
    {
        $setting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'category' => TenantSetting::CATEGORY_GENERAL,
            'key' => 'company_name',
            'value' => 'Test Company',
            'type' => TenantSetting::TYPE_STRING
        ]);

        $this->assertDatabaseHas('tenant_settings', [
            'tenant_id' => $this->tenant->id,
            'key' => 'company_name',
            'value' => 'Test Company'
        ]);

        $this->assertEquals('Test Company', $setting->value);
        $this->assertEquals(TenantSetting::TYPE_STRING, $setting->type);
    }

    /**
     * Test typed value casting
     */
    public function test_typed_value_casting()
    {
        // String value
        $stringSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_STRING,
            'value' => 'test string'
        ]);
        $this->assertEquals('test string', $stringSetting->typed_value);

        // Boolean value
        $booleanSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_BOOLEAN,
            'value' => '1'
        ]);
        $this->assertTrue($booleanSetting->typed_value);

        $booleanSetting2 = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_BOOLEAN,
            'value' => '0'
        ]);
        $this->assertFalse($booleanSetting2->typed_value);

        // Integer value
        $integerSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_INTEGER,
            'value' => '42'
        ]);
        $this->assertEquals(42, $integerSetting->typed_value);

        // Decimal value
        $decimalSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_DECIMAL,
            'value' => '10.50'
        ]);
        $this->assertEquals(10.50, $decimalSetting->typed_value);

        // JSON value
        $jsonSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_JSON,
            'value' => '{"key": "value", "number": 123}'
        ]);
        $this->assertEquals(['key' => 'value', 'number' => 123], $jsonSetting->typed_value);
    }

    /**
     * Test display value formatting
     */
    public function test_display_value_formatting()
    {
        // Boolean display
        $booleanTrue = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_BOOLEAN,
            'value' => '1'
        ]);
        $this->assertEquals('Có', $booleanTrue->display_value);

        $booleanFalse = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_BOOLEAN,
            'value' => '0'
        ]);
        $this->assertEquals('Không', $booleanFalse->display_value);

        // JSON display
        $jsonSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_JSON,
            'value' => '{"test": "value"}'
        ]);
        $this->assertStringContainsString('test', $jsonSetting->display_value);
    }

    /**
     * Test setValue method
     */
    public function test_set_value_method()
    {
        $setting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantSetting::TYPE_BOOLEAN
        ]);

        // Set boolean value
        $setting->setValue(true);
        $this->assertEquals('1', $setting->value);

        $setting->setValue(false);
        $this->assertEquals('0', $setting->value);

        // Set JSON value
        $setting->type = TenantSetting::TYPE_JSON;
        $setting->setValue(['key' => 'value']);
        $this->assertEquals('{"key":"value"}', $setting->value);
    }

    /**
     * Test validation
     */
    public function test_validation()
    {
        $setting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'validation_rules' => 'required|min:3|max:10'
        ]);

        // Valid values
        $this->assertTrue($setting->validateValue('test'));
        $this->assertTrue($setting->validateValue('1234567890'));

        // Invalid values
        $this->assertFalse($setting->validateValue(''));
        $this->assertFalse($setting->validateValue('ab'));
        $this->assertFalse($setting->validateValue('12345678901'));

        // Numeric validation
        $numericSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'validation_rules' => 'numeric'
        ]);
        $this->assertTrue($numericSetting->validateValue('123'));
        $this->assertTrue($numericSetting->validateValue('123.45'));
        $this->assertFalse($numericSetting->validateValue('abc'));

        // Email validation
        $emailSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'validation_rules' => 'email'
        ]);
        $this->assertTrue($emailSetting->validateValue('test@example.com'));
        $this->assertFalse($emailSetting->validateValue('invalid-email'));
    }

    /**
     * Test canModify method
     */
    public function test_can_modify()
    {
        // Regular setting
        $regularSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_readonly' => false,
            'is_system' => false
        ]);
        $this->assertTrue($regularSetting->canModify());

        // Readonly setting
        $readonlySetting = TenantSetting::factory()->readonly()->create([
            'tenant_id' => $this->tenant->id
        ]);
        $this->assertFalse($readonlySetting->canModify());

        // System setting
        $systemSetting = TenantSetting::factory()->system()->create([
            'tenant_id' => $this->tenant->id
        ]);
        $this->assertFalse($systemSetting->canModify());
    }

    /**
     * Test options validation
     */
    public function test_options_validation()
    {
        $setting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'options' => [
                'option1' => 'Option 1',
                'option2' => 'Option 2',
                'option3' => 'Option 3'
            ]
        ]);

        // Valid options
        $this->assertTrue($setting->isValidOption('option1'));
        $this->assertTrue($setting->isValidOption('option2'));

        // Invalid option
        $this->assertFalse($setting->isValidOption('invalid_option'));

        // Setting without options should accept any value
        $noOptionsSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'options' => null
        ]);
        $this->assertTrue($noOptionsSetting->isValidOption('any_value'));
    }

    /**
     * Test static helper methods
     */
    public function test_static_helper_methods()
    {
        // Test getForTenant
        TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'key' => 'test_setting',
            'value' => 'test_value',
            'type' => TenantSetting::TYPE_STRING
        ]);

        $value = TenantSetting::getForTenant($this->tenant->id, 'test_setting');
        $this->assertEquals('test_value', $value);

        $defaultValue = TenantSetting::getForTenant($this->tenant->id, 'non_existent', 'default');
        $this->assertEquals('default', $defaultValue);

        // Test setForTenant
        $setting = TenantSetting::setForTenant($this->tenant->id, 'new_setting', 'new_value');
        $this->assertEquals('new_value', $setting->value);
        $this->assertEquals(TenantSetting::TYPE_STRING, $setting->type);

        // Test updating existing setting
        $updatedSetting = TenantSetting::setForTenant($this->tenant->id, 'new_setting', 'updated_value');
        $this->assertEquals('updated_value', $updatedSetting->value);
        $this->assertEquals($setting->id, $updatedSetting->id);
    }

    /**
     * Test scopes
     */
    public function test_scopes()
    {
        $generalSetting = TenantSetting::factory()->general()->create(['tenant_id' => $this->tenant->id]);
        $salesSetting = TenantSetting::factory()->sales()->create(['tenant_id' => $this->tenant->id]);
        $publicSetting = TenantSetting::factory()->public()->create(['tenant_id' => $this->tenant->id]);
        $systemSetting = TenantSetting::factory()->system()->create(['tenant_id' => $this->tenant->id]);
        $editableSetting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_readonly' => false
        ]);

        // Test byCategory scope
        $generalResults = TenantSetting::byCategory(TenantSetting::CATEGORY_GENERAL)->get();
        $this->assertTrue($generalResults->contains($generalSetting));
        $this->assertFalse($generalResults->contains($salesSetting));

        // Test public scope
        $publicResults = TenantSetting::public()->get();
        $this->assertTrue($publicResults->contains($publicSetting));

        // Test system scope
        $systemResults = TenantSetting::system()->get();
        $this->assertTrue($systemResults->contains($systemSetting));

        // Test editable scope
        $editableResults = TenantSetting::editable()->get();
        $this->assertTrue($editableResults->contains($editableSetting));
        $this->assertFalse($editableResults->contains($systemSetting));
    }

    /**
     * Test factory states
     */
    public function test_factory_states()
    {
        // Test public state
        $publicSetting = TenantSetting::factory()->public()->create(['tenant_id' => $this->tenant->id]);
        $this->assertTrue($publicSetting->is_public);

        // Test readonly state
        $readonlySetting = TenantSetting::factory()->readonly()->create(['tenant_id' => $this->tenant->id]);
        $this->assertTrue($readonlySetting->is_readonly);

        // Test system state
        $systemSetting = TenantSetting::factory()->system()->create(['tenant_id' => $this->tenant->id]);
        $this->assertTrue($systemSetting->is_system);
        $this->assertTrue($systemSetting->is_readonly);

        // Test category states
        $generalSetting = TenantSetting::factory()->general()->create(['tenant_id' => $this->tenant->id]);
        $this->assertEquals(TenantSetting::CATEGORY_GENERAL, $generalSetting->category);

        $inventorySetting = TenantSetting::factory()->inventory()->create(['tenant_id' => $this->tenant->id]);
        $this->assertEquals(TenantSetting::CATEGORY_INVENTORY, $inventorySetting->category);

        $salesSetting = TenantSetting::factory()->sales()->create(['tenant_id' => $this->tenant->id]);
        $this->assertEquals(TenantSetting::CATEGORY_SALES, $salesSetting->category);
    }

    /**
     * Test relationships
     */
    public function test_relationships()
    {
        $creator = User::factory()->create();
        $updater = User::factory()->create();

        $setting = TenantSetting::factory()->create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $creator->id,
            'updated_by' => $updater->id
        ]);

        // Test tenant relationship
        $this->assertEquals($this->tenant->id, $setting->tenant->id);

        // Test creator relationship
        $this->assertEquals($creator->id, $setting->creator->id);

        // Test updater relationship
        $this->assertEquals($updater->id, $setting->updater->id);
    }

    /**
     * Test constants and static methods
     */
    public function test_constants_and_static_methods()
    {
        // Test type constants
        $types = TenantSetting::getTypes();
        $this->assertArrayHasKey(TenantSetting::TYPE_STRING, $types);
        $this->assertArrayHasKey(TenantSetting::TYPE_BOOLEAN, $types);
        $this->assertEquals('Chuỗi', $types[TenantSetting::TYPE_STRING]);

        // Test category constants
        $categories = TenantSetting::getCategories();
        $this->assertArrayHasKey(TenantSetting::CATEGORY_GENERAL, $categories);
        $this->assertArrayHasKey(TenantSetting::CATEGORY_SALES, $categories);
        $this->assertEquals('Chung', $categories[TenantSetting::CATEGORY_GENERAL]);
    }
}
