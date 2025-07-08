<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\UserSetting;

class LanguageService
{
    /**
     * Get all supported locales
     */
    public static function getSupportedLocales(): array
    {
        return config('app.supported_locales', ['vi', 'en']);
    }

    /**
     * Get locale display names
     */
    public static function getLocaleNames(): array
    {
        return config('app.locale_names', [
            'vi' => 'Tiếng Việt',
            'en' => 'English'
        ]);
    }

    /**
     * Get current locale
     */
    public static function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get current locale display name
     */
    public static function getCurrentLocaleName(): string
    {
        $locale = self::getCurrentLocale();
        $names = self::getLocaleNames();
        return $names[$locale] ?? $locale;
    }

    /**
     * Check if locale is supported
     */
    public static function isValidLocale(string $locale): bool
    {
        return in_array($locale, self::getSupportedLocales());
    }

    /**
     * Switch language for current user
     */
    public static function switchLanguage(string $locale): bool
    {
        if (!self::isValidLocale($locale)) {
            return false;
        }

        // Set application locale
        App::setLocale($locale);

        // Store in session
        Session::put('locale', $locale);

        // Save to user settings if authenticated
        if (Auth::check()) {
            self::saveUserLanguagePreference(Auth::id(), $locale);
        }

        return true;
    }

    /**
     * Get user's language preference
     */
    public static function getUserLanguagePreference(?int $userId = null): ?string
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return null;
        }

        try {
            $setting = UserSetting::where('user_id', $userId)
                ->where('key', 'language')
                ->first();

            return $setting ? $setting->value : null;
        } catch (\Exception $e) {
            \Log::warning('Failed to get user language preference: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save user's language preference
     */
    public static function saveUserLanguagePreference(int $userId, string $locale): bool
    {
        if (!self::isValidLocale($locale)) {
            return false;
        }

        try {
            UserSetting::updateOrCreate(
                [
                    'user_id' => $userId,
                    'key' => 'language'
                ],
                [
                    'value' => $locale,
                    'type' => 'string'
                ]
            );

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to save user language preference: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get locale from various sources
     */
    public static function detectLocale(): string
    {
        // 1. Check user settings if authenticated
        if (Auth::check()) {
            $userLocale = self::getUserLanguagePreference();
            if ($userLocale && self::isValidLocale($userLocale)) {
                return $userLocale;
            }
        }

        // 2. Check session
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && self::isValidLocale($sessionLocale)) {
            return $sessionLocale;
        }

        // 3. Check cookie
        $cookieLocale = Cookie::get('locale');
        if ($cookieLocale && self::isValidLocale($cookieLocale)) {
            return $cookieLocale;
        }

        // 4. Return default locale
        return config('app.locale', 'vi');
    }

    /**
     * Get language options for dropdowns
     */
    public static function getLanguageOptions(): array
    {
        $locales = self::getSupportedLocales();
        $names = self::getLocaleNames();
        $options = [];

        foreach ($locales as $locale) {
            $options[] = [
                'value' => $locale,
                'label' => $names[$locale] ?? $locale,
                'flag' => self::getLanguageFlag($locale),
                'active' => $locale === self::getCurrentLocale()
            ];
        }

        return $options;
    }

    /**
     * Get language flag icon
     */
    public static function getLanguageFlag(string $locale): string
    {
        $flags = [
            'vi' => '🇻🇳',
            'en' => '🇺🇸'
        ];

        return $flags[$locale] ?? '🌐';
    }

    /**
     * Get language direction (for RTL languages)
     */
    public static function getLanguageDirection(string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();
        
        $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
        
        return in_array($locale, $rtlLanguages) ? 'rtl' : 'ltr';
    }

    /**
     * Get localized route
     */
    public static function getLocalizedRoute(string $routeName, array $parameters = [], string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();
        
        // Add locale to parameters if route supports it
        if (in_array('locale', array_keys($parameters))) {
            $parameters['locale'] = $locale;
        }

        try {
            return route($routeName, $parameters);
        } catch (\Exception $e) {
            return '#';
        }
    }

    /**
     * Get language switch URL
     */
    public static function getLanguageSwitchUrl(string $locale): string
    {
        if (!self::isValidLocale($locale)) {
            return '#';
        }

        $currentUrl = request()->fullUrl();
        $separator = strpos($currentUrl, '?') !== false ? '&' : '?';
        
        return $currentUrl . $separator . 'locale=' . $locale;
    }

    /**
     * Initialize language for the application
     */
    public static function initialize(): void
    {
        $locale = self::detectLocale();
        
        // Set application locale
        App::setLocale($locale);
        
        // Set Carbon locale for dates
        try {
            \Carbon\Carbon::setLocale($locale);
        } catch (\Exception $e) {
            // Fallback to English if locale not supported by Carbon
            \Carbon\Carbon::setLocale('en');
        }
        
        // Share with views
        view()->share('currentLocale', $locale);
        view()->share('availableLocales', self::getSupportedLocales());
        view()->share('localeNames', self::getLocaleNames());
        view()->share('languageOptions', self::getLanguageOptions());
    }

    /**
     * Get translation with fallback
     */
    public static function trans(string $key, array $replace = [], string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();
        
        $translation = trans($key, $replace, $locale);
        
        // If translation not found and not using fallback locale, try fallback
        if ($translation === $key && $locale !== config('app.fallback_locale')) {
            $translation = trans($key, $replace, config('app.fallback_locale'));
        }
        
        return $translation;
    }

    /**
     * Get available language files
     */
    public static function getAvailableLanguageFiles(): array
    {
        $files = [];
        $langPath = resource_path('lang');
        
        foreach (self::getSupportedLocales() as $locale) {
            $localePath = $langPath . DIRECTORY_SEPARATOR . $locale;
            
            if (is_dir($localePath)) {
                $files[$locale] = [];
                $phpFiles = glob($localePath . DIRECTORY_SEPARATOR . '*.php');
                
                foreach ($phpFiles as $file) {
                    $filename = basename($file, '.php');
                    $files[$locale][] = $filename;
                }
            }
        }
        
        return $files;
    }

    /**
     * Check if translation key exists
     */
    public static function hasTranslation(string $key, string $locale = null): bool
    {
        $locale = $locale ?? self::getCurrentLocale();
        return trans($key, [], $locale) !== $key;
    }

    /**
     * Get missing translations
     */
    public static function getMissingTranslations(string $locale = null): array
    {
        $locale = $locale ?? self::getCurrentLocale();
        $fallbackLocale = config('app.fallback_locale');
        
        if ($locale === $fallbackLocale) {
            return [];
        }
        
        $missing = [];
        $files = self::getAvailableLanguageFiles();
        
        if (!isset($files[$fallbackLocale]) || !isset($files[$locale])) {
            return $missing;
        }
        
        foreach ($files[$fallbackLocale] as $file) {
            if (!in_array($file, $files[$locale])) {
                $missing[] = $file;
            }
        }
        
        return $missing;
    }
}
