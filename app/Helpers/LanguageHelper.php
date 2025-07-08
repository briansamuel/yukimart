<?php

namespace App\Helpers;

use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class LanguageHelper
{
    /**
     * Get current language.
     */
    public static function getCurrentLanguage()
    {
        $currentLocale = App::getLocale();
        return Language::getByCode($currentLocale) ?? Language::getDefault();
    }

    /**
     * Get available languages.
     */
    public static function getAvailableLanguages()
    {
        return Language::getActive();
    }

    /**
     * Set application language.
     */
    public static function setLanguage($languageCode)
    {
        $language = Language::getByCode($languageCode);
        
        if ($language && $language->is_active) {
            App::setLocale($languageCode);
            Session::put('locale', $languageCode);
            
            // Set Carbon locale for date formatting
            \Carbon\Carbon::setLocale($languageCode);
            
            return true;
        }
        
        return false;
    }

    /**
     * Get language direction (LTR or RTL).
     */
    public static function getDirection($languageCode = null)
    {
        $languageCode = $languageCode ?? App::getLocale();
        $language = Language::getByCode($languageCode);
        
        return $language && $language->is_rtl ? 'rtl' : 'ltr';
    }

    /**
     * Check if current language is RTL.
     */
    public static function isRtl($languageCode = null)
    {
        return self::getDirection($languageCode) === 'rtl';
    }

    /**
     * Get language flag icon.
     */
    public static function getFlagIcon($languageCode = null)
    {
        $languageCode = $languageCode ?? App::getLocale();
        $language = Language::getByCode($languageCode);
        
        return $language ? $language->flag_icon_html : '<i class="fas fa-globe"></i>';
    }

    /**
     * Get language display name.
     */
    public static function getDisplayName($languageCode = null)
    {
        $languageCode = $languageCode ?? App::getLocale();
        $language = Language::getByCode($languageCode);
        
        return $language ? $language->display_name : $languageCode;
    }

    /**
     * Format number according to current language.
     */
    public static function formatNumber($number, $decimals = 0, $languageCode = null)
    {
        $languageCode = $languageCode ?? App::getLocale();
        $language = Language::getByCode($languageCode);
        
        if ($language) {
            return $language->formatNumber($number, $decimals);
        }
        
        return number_format($number, $decimals);
    }

    /**
     * Format currency according to current language.
     */
    public static function formatCurrency($amount, $showSymbol = true, $languageCode = null)
    {
        $languageCode = $languageCode ?? App::getLocale();
        $language = Language::getByCode($languageCode);
        
        if ($language) {
            return $language->formatCurrency($amount, $showSymbol);
        }
        
        return number_format($amount, 0) . ($showSymbol ? ' VND' : '');
    }

    /**
     * Format date according to current language.
     */
    public static function formatDate($date, $format = null, $languageCode = null)
    {
        if (!$date) {
            return '';
        }
        
        $languageCode = $languageCode ?? App::getLocale();
        $language = Language::getByCode($languageCode);
        
        if ($language) {
            return $language->formatDate($date, $format);
        }
        
        $format = $format ?? 'd/m/Y';
        return $date->format($format);
    }

    /**
     * Get translation with fallback.
     */
    public static function trans($key, $replace = [], $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        
        // Try to get translation
        $translation = trans($key, $replace, $locale);
        
        // If translation not found and not in default language, try default language
        if ($translation === $key && $locale !== config('app.fallback_locale')) {
            $translation = trans($key, $replace, config('app.fallback_locale'));
        }
        
        return $translation;
    }

    /**
     * Get language switcher data for frontend.
     */
    public static function getLanguageSwitcherData()
    {
        $currentLanguage = self::getCurrentLanguage();
        $availableLanguages = self::getAvailableLanguages();
        
        return [
            'current' => [
                'code' => $currentLanguage->code,
                'name' => $currentLanguage->name,
                'native_name' => $currentLanguage->native_name,
                'flag_icon' => $currentLanguage->flag_icon,
                'flag_icon_html' => $currentLanguage->flag_icon_html,
                'is_rtl' => $currentLanguage->is_rtl,
                'direction' => $currentLanguage->getDirection(),
            ],
            'available' => $availableLanguages->map(function ($language) {
                return [
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                    'flag_icon' => $language->flag_icon,
                    'flag_icon_html' => $language->flag_icon_html,
                    'is_rtl' => $language->is_rtl,
                    'direction' => $language->getDirection(),
                    'url' => route('language.switch', $language->code),
                ];
            })->toArray(),
        ];
    }

    /**
     * Get browser preferred language.
     */
    public static function getBrowserLanguage($acceptLanguage = null)
    {
        $acceptLanguage = $acceptLanguage ?? $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        if (empty($acceptLanguage)) {
            return null;
        }
        
        // Parse Accept-Language header
        $languages = [];
        preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)\s*(?:;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLanguage, $matches);
        
        if (count($matches[1])) {
            $languages = array_combine($matches[1], $matches[2]);
            
            // Set default quality to 1 if not specified
            foreach ($languages as $lang => $quality) {
                if ($quality === '') {
                    $languages[$lang] = 1;
                } else {
                    $languages[$lang] = floatval($quality);
                }
            }
            
            // Sort by quality
            arsort($languages);
            
            // Find first supported language
            $availableLanguages = Language::active()->pluck('code')->toArray();
            
            foreach (array_keys($languages) as $lang) {
                // Extract language code (e.g., 'en' from 'en-US')
                $langCode = substr($lang, 0, 2);
                if (in_array($langCode, $availableLanguages)) {
                    return $langCode;
                }
            }
        }
        
        return null;
    }

    /**
     * Generate language URLs for SEO.
     */
    public static function getLanguageUrls($route = null, $parameters = [])
    {
        $route = $route ?? request()->route()->getName();
        $parameters = array_merge(request()->route()->parameters(), $parameters);
        
        $urls = [];
        $availableLanguages = self::getAvailableLanguages();
        
        foreach ($availableLanguages as $language) {
            try {
                $urls[$language->code] = route($route, array_merge($parameters, ['locale' => $language->code]));
            } catch (\Exception $e) {
                // If route doesn't support locale parameter, use current URL with query parameter
                $urls[$language->code] = request()->fullUrlWithQuery(['lang' => $language->code]);
            }
        }
        
        return $urls;
    }

    /**
     * Get language meta tags for SEO.
     */
    public static function getLanguageMetaTags()
    {
        $urls = self::getLanguageUrls();
        $currentLanguage = self::getCurrentLanguage();
        
        $tags = [];
        
        // Add hreflang tags
        foreach ($urls as $langCode => $url) {
            $tags[] = '<link rel="alternate" hreflang="' . $langCode . '" href="' . $url . '">';
        }
        
        // Add x-default for default language
        if (isset($urls[$currentLanguage->code])) {
            $tags[] = '<link rel="alternate" hreflang="x-default" href="' . $urls[$currentLanguage->code] . '">';
        }
        
        return implode("\n", $tags);
    }

    /**
     * Clear language cache.
     */
    public static function clearCache()
    {
        Cache::forget('default_language');
        Cache::forget('active_languages');
        
        $languages = Language::all();
        foreach ($languages as $language) {
            Cache::forget("language_{$language->code}");
        }
    }

    /**
     * Get language statistics.
     */
    public static function getLanguageStatistics()
    {
        $languages = Language::all();
        $statistics = [];
        
        foreach ($languages as $language) {
            $statistics[$language->code] = $language->getStatistics();
        }
        
        return $statistics;
    }

    /**
     * Check if translation exists.
     */
    public static function hasTranslation($key, $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        return trans($key, [], $locale) !== $key;
    }

    /**
     * Get missing translations for a language.
     */
    public static function getMissingTranslations($languageCode, $namespace = null)
    {
        // This would require scanning all translation files
        // Implementation depends on specific requirements
        return [];
    }

    /**
     * Auto-detect user language preference.
     */
    public static function autoDetectLanguage()
    {
        // 1. Check if user is authenticated and has language preference
        if (auth()->check() && auth()->user()->locale) {
            $userLocale = auth()->user()->locale;
            if (Language::where('code', $userLocale)->where('is_active', true)->exists()) {
                return $userLocale;
            }
        }

        // 2. Check session
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && Language::where('code', $sessionLocale)->where('is_active', true)->exists()) {
            return $sessionLocale;
        }

        // 3. Check browser language
        $browserLanguage = self::getBrowserLanguage();
        if ($browserLanguage) {
            return $browserLanguage;
        }

        // 4. Return default language
        $defaultLanguage = Language::getDefault();
        return $defaultLanguage ? $defaultLanguage->code : config('app.locale');
    }
}
