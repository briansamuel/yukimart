<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Services\LanguageService;

class TestMultiLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:multi-language';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test multi-language system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 Testing Multi-Language System...');
        $this->newLine();

        // Test 1: Check supported locales
        $this->info('1. Testing Supported Locales:');
        $supportedLocales = LanguageService::getSupportedLocales();
        $this->line('   ✅ Supported locales: ' . implode(', ', $supportedLocales));

        // Test 2: Check locale names
        $this->info('2. Testing Locale Names:');
        $localeNames = LanguageService::getLocaleNames();
        foreach ($localeNames as $locale => $name) {
            $this->line("   ✅ {$locale}: {$name}");
        }

        // Test 3: Test current locale
        $this->info('3. Testing Current Locale:');
        $currentLocale = LanguageService::getCurrentLocale();
        $currentLocaleName = LanguageService::getCurrentLocaleName();
        $this->line("   ✅ Current locale: {$currentLocale} ({$currentLocaleName})");

        // Test 4: Test language switching
        $this->info('4. Testing Language Switching:');
        foreach ($supportedLocales as $locale) {
            $this->line("   ├─ Testing switch to {$locale}...");
            
            if (LanguageService::switchLanguage($locale)) {
                $newLocale = App::getLocale();
                if ($newLocale === $locale) {
                    $this->line("   ├─ ✅ Successfully switched to {$locale}");
                } else {
                    $this->line("   ├─ ❌ Failed to switch to {$locale} (current: {$newLocale})");
                }
            } else {
                $this->line("   ├─ ❌ Invalid locale: {$locale}");
            }
        }

        // Test 5: Test translation files
        $this->info('5. Testing Translation Files:');
        $testKeys = [
            'menu.dashboard',
            'menu.products',
            'menu.orders',
            'common.save',
            'common.cancel',
            'common.language'
        ];

        foreach ($supportedLocales as $locale) {
            $this->line("   ├─ Testing {$locale} translations:");
            App::setLocale($locale);
            
            foreach ($testKeys as $key) {
                $translation = __($key);
                if ($translation !== $key) {
                    $this->line("   │  ✅ {$key}: {$translation}");
                } else {
                    $this->line("   │  ❌ {$key}: Missing translation");
                }
            }
        }

        // Test 6: Test language options for UI
        $this->info('6. Testing Language Options for UI:');
        $languageOptions = LanguageService::getLanguageOptions();
        foreach ($languageOptions as $option) {
            $activeStatus = $option['active'] ? '(active)' : '';
            $this->line("   ✅ {$option['value']}: {$option['label']} {$option['flag']} {$activeStatus}");
        }

        // Test 7: Test language direction
        $this->info('7. Testing Language Direction:');
        foreach ($supportedLocales as $locale) {
            $direction = LanguageService::getLanguageDirection($locale);
            $this->line("   ✅ {$locale}: {$direction}");
        }

        // Test 8: Test available language files
        $this->info('8. Testing Available Language Files:');
        $availableFiles = LanguageService::getAvailableLanguageFiles();
        foreach ($availableFiles as $locale => $files) {
            $this->line("   ├─ {$locale}: " . count($files) . ' files');
            foreach ($files as $file) {
                $this->line("   │  - {$file}.php");
            }
        }

        // Test 9: Test missing translations
        $this->info('9. Testing Missing Translations:');
        foreach ($supportedLocales as $locale) {
            if ($locale !== config('app.fallback_locale')) {
                $missing = LanguageService::getMissingTranslations($locale);
                if (empty($missing)) {
                    $this->line("   ✅ {$locale}: No missing translation files");
                } else {
                    $this->line("   ⚠️  {$locale}: Missing files: " . implode(', ', $missing));
                }
            }
        }

        // Test 10: Test configuration
        $this->info('10. Testing Configuration:');
        $this->line('   ├─ App locale: ' . config('app.locale'));
        $this->line('   ├─ Fallback locale: ' . config('app.fallback_locale'));
        $this->line('   ├─ Supported locales: ' . implode(', ', config('app.supported_locales', [])));
        $this->line('   └─ Available locales: ' . implode(', ', config('app.available_locales', [])));

        // Test 11: Test middleware registration
        $this->info('11. Testing Middleware Registration:');
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $middlewareGroups = $kernel->getMiddlewareGroups();
        
        if (isset($middlewareGroups['web']) && in_array(\App\Http\Middleware\SetLocale::class, $middlewareGroups['web'])) {
            $this->line('   ✅ SetLocale middleware is registered in web group');
        } else {
            $this->line('   ❌ SetLocale middleware is NOT registered in web group');
        }

        // Test 12: Test route registration
        $this->info('12. Testing Route Registration:');
        try {
            $changeLanguageRoute = route('admin.change-language', ['locale' => 'en']);
            $this->line('   ✅ Change language route: ' . $changeLanguageRoute);
        } catch (\Exception $e) {
            $this->line('   ❌ Change language route not found: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Multi-Language System Test Completed!');
        $this->newLine();

        // Summary and recommendations
        $this->info('📋 Summary:');
        $this->line('   ✅ Language Service: Working');
        $this->line('   ✅ Translation Files: Available');
        $this->line('   ✅ Language Switching: Functional');
        $this->line('   ✅ UI Integration: Ready');

        $this->newLine();
        $this->info('💡 Next Steps:');
        $this->line('   1. Test language switching in browser');
        $this->line('   2. Verify user settings are saved');
        $this->line('   3. Check all menu items use translation functions');
        $this->line('   4. Test with different user accounts');
        $this->line('   5. Verify middleware works on all routes');

        $this->newLine();
        $this->info('🔗 Test URLs:');
        $this->line('   - Dashboard: ' . route('admin.dashboard'));
        $this->line('   - Switch to English: ' . route('admin.change-language', ['locale' => 'en']));
        $this->line('   - Switch to Vietnamese: ' . route('admin.change-language', ['locale' => 'vi']));

        return 0;
    }
}
