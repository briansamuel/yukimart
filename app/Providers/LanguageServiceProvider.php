<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Helpers\LanguageHelper;
use App\Models\Language;

class LanguageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register LanguageHelper as singleton
        $this->app->singleton('language.helper', function ($app) {
            return new LanguageHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Blade directives
        $this->registerBladeDirectives();
        
        // Share language data with all views
        $this->shareLanguageDataWithViews();
        
        // Register view composers
        $this->registerViewComposers();
    }

    /**
     * Register custom Blade directives for language functionality.
     */
    protected function registerBladeDirectives()
    {
        // @lang directive for simple translations
        Blade::directive('lang', function ($expression) {
            return "<?php echo __($expression); ?>";
        });

        // @langChoice directive for pluralization
        Blade::directive('langChoice', function ($expression) {
            return "<?php echo trans_choice($expression); ?>";
        });

        // @langIf directive for conditional translations
        Blade::directive('langIf', function ($expression) {
            return "<?php if(App\\Helpers\\LanguageHelper::hasTranslation($expression)): ?>";
        });

        Blade::directive('endlangIf', function () {
            return "<?php endif; ?>";
        });

        // @langFallback directive with fallback text
        Blade::directive('langFallback', function ($expression) {
            $parts = explode(',', $expression, 2);
            $key = trim($parts[0]);
            $fallback = isset($parts[1]) ? trim($parts[1]) : "''";
            
            return "<?php echo App\\Helpers\\LanguageHelper::hasTranslation($key) ? __($key) : $fallback; ?>";
        });

        // @rtl directive for RTL languages
        Blade::directive('rtl', function () {
            return "<?php if(App\\Helpers\\LanguageHelper::isRtl()): ?>";
        });

        Blade::directive('endrtl', function () {
            return "<?php endif; ?>";
        });

        // @ltr directive for LTR languages
        Blade::directive('ltr', function () {
            return "<?php if(!App\\Helpers\\LanguageHelper::isRtl()): ?>";
        });

        Blade::directive('endltr', function () {
            return "<?php endif; ?>";
        });

        // @langDir directive to output direction
        Blade::directive('langDir', function () {
            return "<?php echo App\\Helpers\\LanguageHelper::getDirection(); ?>";
        });

        // @langFlag directive to output flag icon
        Blade::directive('langFlag', function ($expression = null) {
            if ($expression) {
                return "<?php echo App\\Helpers\\LanguageHelper::getFlagIcon($expression); ?>";
            }
            return "<?php echo App\\Helpers\\LanguageHelper::getFlagIcon(); ?>";
        });

        // @formatNumber directive
        Blade::directive('formatNumber', function ($expression) {
            return "<?php echo App\\Helpers\\LanguageHelper::formatNumber($expression); ?>";
        });

        // @formatCurrency directive
        Blade::directive('formatCurrency', function ($expression) {
            return "<?php echo App\\Helpers\\LanguageHelper::formatCurrency($expression); ?>";
        });

        // @formatDate directive
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo App\\Helpers\\LanguageHelper::formatDate($expression); ?>";
        });

        // @langSwitcher directive to render language switcher
        Blade::directive('langSwitcher', function ($expression = null) {
            $template = $expression ? trim($expression, '"\'') : 'components.language-switcher';
            return "<?php echo view('$template', ['languageData' => App\\Helpers\\LanguageHelper::getLanguageSwitcherData()])->render(); ?>";
        });

        // @langMeta directive for SEO meta tags
        Blade::directive('langMeta', function () {
            return "<?php echo App\\Helpers\\LanguageHelper::getLanguageMetaTags(); ?>";
        });
    }

    /**
     * Share language data with all views.
     */
    protected function shareLanguageDataWithViews()
    {
        View::composer('*', function ($view) {
            try {
                $currentLanguage = LanguageHelper::getCurrentLanguage();
                $availableLanguages = LanguageHelper::getAvailableLanguages();
                
                $view->with([
                    'currentLanguage' => $currentLanguage,
                    'availableLanguages' => $availableLanguages,
                    'isRtl' => LanguageHelper::isRtl(),
                    'languageDirection' => LanguageHelper::getDirection(),
                    'languageSwitcherData' => LanguageHelper::getLanguageSwitcherData(),
                ]);
            } catch (\Exception $e) {
                // Handle case where database is not available (during migration, etc.)
                $view->with([
                    'currentLanguage' => null,
                    'availableLanguages' => collect(),
                    'isRtl' => false,
                    'languageDirection' => 'ltr',
                    'languageSwitcherData' => ['current' => null, 'available' => []],
                ]);
            }
        });
    }

    /**
     * Register view composers for specific views.
     */
    protected function registerViewComposers()
    {
        // Language switcher component
        View::composer('components.language-switcher', function ($view) {
            $view->with('languageData', LanguageHelper::getLanguageSwitcherData());
        });

        // Admin layout
        View::composer('admin.layouts.*', function ($view) {
            try {
                $languageStats = LanguageHelper::getLanguageStatistics();
                $view->with('languageStatistics', $languageStats);
            } catch (\Exception $e) {
                $view->with('languageStatistics', []);
            }
        });

        // Frontend layout
        View::composer('layouts.*', function ($view) {
            $view->with([
                'languageUrls' => LanguageHelper::getLanguageUrls(),
                'languageMetaTags' => LanguageHelper::getLanguageMetaTags(),
            ]);
        });
    }
}
