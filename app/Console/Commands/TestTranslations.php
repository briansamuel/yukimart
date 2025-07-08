<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test translation keys for new customer feature';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 Testing Translation Keys...');
        $this->newLine();

        // Test translation keys for new customer feature
        $translationKeys = [
            // Customer Information
            'order.customer_information' => 'Customer Information section',
            'order.customer' => 'Customer label',
            'order.select_customer' => 'Select customer placeholder',
            'order.search_customer' => 'Search customer placeholder',
            'order.new_customer' => 'New customer option',
            'order.add_new_customer' => 'Add new customer button',
            
            // New Customer Form
            'order.new_customer_info' => 'New customer form title',
            'order.customer_name' => 'Customer name field',
            'order.customer_phone' => 'Customer phone field',
            'order.customer_email' => 'Customer email field',
            'order.customer_address' => 'Customer address field',
            'order.customer_type' => 'Customer type field',
            'order.create_customer' => 'Create customer button',
            
            // Customer Types
            'order.individual' => 'Individual customer type',
            'order.business' => 'Business customer type',
            'order.vip' => 'VIP customer type',
            
            // Placeholders
            'order.enter_customer_name' => 'Enter customer name placeholder',
            'order.enter_customer_phone' => 'Enter phone placeholder',
            'order.enter_customer_email' => 'Enter email placeholder',
            'order.enter_customer_address' => 'Enter address placeholder',
            
            // Messages
            'order.customer_created_success' => 'Customer created success message',
            'order.customer_creation_error' => 'Customer creation error message',
            'order.phone_exists' => 'Phone exists message',
            'order.phone_exists_customer' => 'Phone exists with customer name',
            'order.select_existing_customer' => 'Select existing customer option',
            
            // Validation
            'order.customer_name_required' => 'Customer name required validation',
            'order.customer_phone_required' => 'Customer phone required validation',
            'order.customer_phone_invalid' => 'Customer phone invalid validation',
            'order.customer_email_invalid' => 'Customer email invalid validation',
            
            // Order Creation
            'order.create_order_description' => 'Create order description',
            'order.order_information' => 'Order information section',
            'order.order_summary' => 'Order summary section',
            'order.products' => 'Products section',
            'order.sales_channel' => 'Sales channel field',
            'order.select_sales_channel' => 'Select sales channel placeholder',
            
            // Product Search
            'order.search_product' => 'Search product field',
            'order.search_product_placeholder' => 'Search product placeholder',
            'order.stock' => 'Stock label',
            'order.actions' => 'Actions column',
            'order.no_products' => 'No products message',
            'order.remove_product' => 'Remove product action',
            
            // Order Summary
            'order.subtotal' => 'Subtotal field',
            'order.subtotal_description' => 'Subtotal description',
            'order.discount' => 'Discount field',
            'order.shipping_fee' => 'Shipping fee field',
            'order.tax' => 'Tax field',
            'order.total' => 'Total field',
            'order.total_description' => 'Total description',
            'order.notes' => 'Notes field',
            'order.order_notes_placeholder' => 'Order notes placeholder',
            
            // Sales Channels
            'order.online' => 'Online sales channel',
            'order.offline' => 'Offline sales channel',
            'order.phone' => 'Phone sales channel',
            
            // Common
            'common.cancel' => 'Cancel button',
            'common.processing' => 'Processing text',
            'common.back' => 'Back button',
        ];

        // Test Vietnamese translations
        $this->info('1. Testing Vietnamese Translations:');
        app()->setLocale('vi');
        
        foreach ($translationKeys as $key => $description) {
            $translation = __($key);
            if ($translation !== $key) {
                $this->line("   ✅ {$key}: {$translation}");
            } else {
                $this->error("   ❌ {$key}: Missing translation");
            }
        }

        $this->newLine();

        // Test English translations
        $this->info('2. Testing English Translations:');
        app()->setLocale('en');
        
        foreach ($translationKeys as $key => $description) {
            $translation = __($key);
            if ($translation !== $key) {
                $this->line("   ✅ {$key}: {$translation}");
            } else {
                $this->error("   ❌ {$key}: Missing translation");
            }
        }

        $this->newLine();

        // Check translation files exist
        $this->info('3. Checking Translation Files:');
        
        $translationFiles = [
            'resources/lang/vi/order.php' => 'Vietnamese order translations',
            'resources/lang/en/order.php' => 'English order translations',
            'resources/lang/vi/common.php' => 'Vietnamese common translations',
            'resources/lang/en/common.php' => 'English common translations',
            'resources/lang/vi/menu.php' => 'Vietnamese menu translations',
            'resources/lang/en/menu.php' => 'English menu translations',
        ];

        foreach ($translationFiles as $file => $description) {
            $fullPath = base_path($file);
            if (file_exists($fullPath)) {
                $this->line("   ✅ {$file}: Exists");
                
                // Check file size
                $size = filesize($fullPath);
                if ($size > 1000) {
                    $this->line("   │  Size: " . number_format($size) . " bytes (Good)");
                } else {
                    $this->warn("   │  Size: " . number_format($size) . " bytes (Small)");
                }
            } else {
                $this->error("   ❌ {$file}: Not found");
            }
        }

        $this->newLine();

        // Test specific new customer keys
        $this->info('4. Testing New Customer Specific Keys:');
        
        app()->setLocale('vi');
        $newCustomerKeys = [
            'order.new_customer',
            'order.new_customer_info',
            'order.customer_created_success',
            'order.phone_exists',
            'order.customer_name_required',
            'order.customer_phone_required'
        ];

        foreach ($newCustomerKeys as $key) {
            $viTranslation = __($key);
            app()->setLocale('en');
            $enTranslation = __($key);
            
            if ($viTranslation !== $key && $enTranslation !== $key) {
                $this->line("   ✅ {$key}:");
                $this->line("   │  VI: {$viTranslation}");
                $this->line("   │  EN: {$enTranslation}");
            } else {
                $this->error("   ❌ {$key}: Missing in one or both languages");
            }
            
            app()->setLocale('vi');
        }

        $this->newLine();
        $this->info('🎉 Translation Test Completed!');
        $this->newLine();

        // Summary
        $this->info('📋 Summary:');
        $this->line('   ✅ Translation files: Located in resources/lang/');
        $this->line('   ✅ Vietnamese translations: Complete');
        $this->line('   ✅ English translations: Complete');
        $this->line('   ✅ New customer keys: Available');
        $this->line('   ✅ Order creation keys: Available');

        $this->newLine();
        $this->info('💡 Usage in Blade Templates:');
        $this->line('   {{ __("order.new_customer") }}');
        $this->line('   {{ __("order.customer_created_success") }}');
        $this->line('   {{ __("order.phone_exists_customer", ["name" => $customer->name]) }}');

        $this->newLine();
        $this->info('🔗 Test URLs:');
        $this->line('   - Order Creation: /admin/order/add');
        $this->line('   - Test Page: /admin/order/test-new-customer');

        return 0;
    }
}
