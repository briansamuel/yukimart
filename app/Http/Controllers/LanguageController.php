<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Models\Language;
use App\Helpers\LanguageHelper;

class LanguageController extends Controller
{
    /**
     * Switch application language.
     */
    public function switch(Request $request, $languageCode)
    {
        // Validate language code
        $language = Language::where('code', $languageCode)
                           ->where('is_active', true)
                           ->first();
        
        if (!$language) {
            return redirect()->back()->with('error', __('app.invalid_language'));
        }
        
        // Set language using helper
        LanguageHelper::setLanguage($languageCode);
        
        // Update user preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['locale' => $languageCode]);
        }
        
        // Get redirect URL
        $redirectUrl = $this->getRedirectUrl($request, $languageCode);
        
        return redirect($redirectUrl)->with('success', __('app.language_changed'));
    }
    
    /**
     * Get redirect URL after language switch.
     */
    protected function getRedirectUrl(Request $request, $languageCode)
    {
        // Check if there's a specific redirect URL
        if ($request->has('redirect')) {
            $redirectUrl = $request->get('redirect');
            
            // Validate redirect URL for security
            if ($this->isValidRedirectUrl($redirectUrl)) {
                return $this->updateUrlLanguage($redirectUrl, $languageCode);
            }
        }
        
        // Use referer if available and valid
        $referer = $request->header('referer');
        if ($referer && $this->isValidRedirectUrl($referer)) {
            return $this->updateUrlLanguage($referer, $languageCode);
        }
        
        // Default to home page
        return route('home', ['locale' => $languageCode]);
    }
    
    /**
     * Validate redirect URL for security.
     */
    protected function isValidRedirectUrl($url)
    {
        // Check if URL belongs to the same domain
        $parsedUrl = parse_url($url);
        $currentDomain = parse_url(config('app.url'));
        
        return isset($parsedUrl['host']) && 
               $parsedUrl['host'] === $currentDomain['host'];
    }
    
    /**
     * Update URL with new language parameter.
     */
    protected function updateUrlLanguage($url, $languageCode)
    {
        $parsedUrl = parse_url($url);
        
        // Parse query string
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }
        
        // Update language parameter
        $queryParams['lang'] = $languageCode;
        
        // Rebuild URL
        $newUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
        if (isset($parsedUrl['port'])) {
            $newUrl .= ':' . $parsedUrl['port'];
        }
        
        if (isset($parsedUrl['path'])) {
            $newUrl .= $parsedUrl['path'];
        }
        
        if (!empty($queryParams)) {
            $newUrl .= '?' . http_build_query($queryParams);
        }
        
        if (isset($parsedUrl['fragment'])) {
            $newUrl .= '#' . $parsedUrl['fragment'];
        }
        
        return $newUrl;
    }
    
    /**
     * Get available languages for API.
     */
    public function getAvailableLanguages()
    {
        $languages = Language::active()->get();
        
        return response()->json([
            'success' => true,
            'data' => $languages->map(function ($language) {
                return [
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                    'flag_icon' => $language->flag_icon,
                    'is_rtl' => $language->is_rtl,
                    'is_default' => $language->is_default,
                    'switch_url' => route('language.switch', $language->code),
                ];
            })
        ]);
    }
    
    /**
     * Get current language information.
     */
    public function getCurrentLanguage()
    {
        $currentLanguage = LanguageHelper::getCurrentLanguage();
        
        return response()->json([
            'success' => true,
            'data' => [
                'code' => $currentLanguage->code,
                'name' => $currentLanguage->name,
                'native_name' => $currentLanguage->native_name,
                'flag_icon' => $currentLanguage->flag_icon,
                'is_rtl' => $currentLanguage->is_rtl,
                'is_default' => $currentLanguage->is_default,
                'direction' => $currentLanguage->getDirection(),
                'date_format' => $currentLanguage->date_format,
                'number_format' => $currentLanguage->number_format,
            ]
        ]);
    }
    
    /**
     * Auto-detect and set user language.
     */
    public function autoDetect(Request $request)
    {
        $detectedLanguage = LanguageHelper::autoDetectLanguage();
        
        if ($detectedLanguage && $detectedLanguage !== App::getLocale()) {
            return $this->switch($request, $detectedLanguage);
        }
        
        return redirect()->back();
    }
    
    /**
     * Get language switcher data for AJAX.
     */
    public function getSwitcherData()
    {
        $data = LanguageHelper::getLanguageSwitcherData();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * Update user language preference.
     */
    public function updateUserPreference(Request $request)
    {
        $request->validate([
            'language_code' => 'required|string|exists:languages,code'
        ]);
        
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated')
            ], 401);
        }
        
        $languageCode = $request->input('language_code');
        
        // Validate language is active
        $language = Language::where('code', $languageCode)
                           ->where('is_active', true)
                           ->first();
        
        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => __('app.invalid_language')
            ], 400);
        }
        
        // Update user preference
        auth()->user()->update(['locale' => $languageCode]);
        
        // Set session
        LanguageHelper::setLanguage($languageCode);
        
        return response()->json([
            'success' => true,
            'message' => __('app.language_preference_updated'),
            'data' => [
                'language_code' => $languageCode,
                'language_name' => $language->name,
                'redirect_url' => $request->input('redirect_url', route('home'))
            ]
        ]);
    }
    
    /**
     * Get translation for specific key.
     */
    public function getTranslation(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'locale' => 'nullable|string|exists:languages,code',
            'replace' => 'nullable|array'
        ]);
        
        $key = $request->input('key');
        $locale = $request->input('locale', App::getLocale());
        $replace = $request->input('replace', []);
        
        $translation = LanguageHelper::trans($key, $replace, $locale);
        
        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'locale' => $locale,
                'translation' => $translation,
                'exists' => $translation !== $key
            ]
        ]);
    }
    
    /**
     * Get multiple translations.
     */
    public function getTranslations(Request $request)
    {
        $request->validate([
            'keys' => 'required|array',
            'keys.*' => 'string',
            'locale' => 'nullable|string|exists:languages,code'
        ]);
        
        $keys = $request->input('keys');
        $locale = $request->input('locale', App::getLocale());
        
        $translations = [];
        
        foreach ($keys as $key) {
            $translation = LanguageHelper::trans($key, [], $locale);
            $translations[$key] = [
                'translation' => $translation,
                'exists' => $translation !== $key
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'locale' => $locale,
                'translations' => $translations
            ]
        ]);
    }
}
