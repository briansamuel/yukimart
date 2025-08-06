<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\Language;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->detectLocale($request);
        
        // Validate locale
        if (!$this->isValidLocale($locale)) {
            $locale = $this->getDefaultLocale();
        }
        
        // Set application locale
        App::setLocale($locale);
        
        // Store in session for persistence
        Session::put('locale', $locale);
        
        // Set locale for Carbon (dates)
        \Carbon\Carbon::setLocale($locale);
        
        // Add locale to view data
        view()->share('currentLocale', $locale);
        view()->share('availableLocales', $this->getAvailableLocales());
        
        $response = $next($request);

        // Set cookie for future visits (30 days) - only for regular responses, not file downloads
        if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
            $response->withCookie(Cookie::make('locale', $locale, 60 * 24 * 30));
        }

        return $response;
    }

    /**
     * Detect locale from various sources.
     */
    protected function detectLocale(Request $request): string
    {
        // 1. URL parameter (highest priority)
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 2. Route parameter
        $routeLocale = $request->route('locale');
        if ($routeLocale && $this->isValidLocale($routeLocale)) {
            return $routeLocale;
        }

        // 3. Subdomain detection
        $subdomain = $this->getSubdomain($request);
        if ($subdomain && $this->isValidLocale($subdomain)) {
            return $subdomain;
        }

        // 4. User preference (if authenticated)
        if (auth()->check() && auth()->user()->locale) {
            $userLocale = auth()->user()->locale;
            if ($this->isValidLocale($userLocale)) {
                return $userLocale;
            }
        }

        // 5. Session
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && $this->isValidLocale($sessionLocale)) {
            return $sessionLocale;
        }

        // 6. Cookie
        $cookieLocale = Cookie::get('locale');
        if ($cookieLocale && $this->isValidLocale($cookieLocale)) {
            return $cookieLocale;
        }

        // 7. Browser Accept-Language header
        $browserLocale = $this->getBrowserLocale($request);
        if ($browserLocale && $this->isValidLocale($browserLocale)) {
            return $browserLocale;
        }

        // 8. Default locale
        return $this->getDefaultLocale();
    }

    /**
     * Get subdomain from request.
     */
    protected function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Check if subdomain exists and is not 'www'
        if (count($parts) >= 3 && $parts[0] !== 'www') {
            return $parts[0];
        }
        
        return null;
    }

    /**
     * Get browser preferred locale.
     */
    protected function getBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
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
            foreach (array_keys($languages) as $lang) {
                // Extract language code (e.g., 'en' from 'en-US')
                $langCode = substr($lang, 0, 2);
                if ($this->isValidLocale($langCode)) {
                    return $langCode;
                }
            }
        }
        
        return null;
    }

    /**
     * Check if locale is valid and active.
     */
    protected function isValidLocale(string $locale): bool
    {
        $availableLocales = $this->getAvailableLocales();
        return in_array($locale, $availableLocales);
    }

    /**
     * Get available locales.
     */
    protected function getAvailableLocales(): array
    {
        static $locales = null;
        
        if ($locales === null) {
            try {
                $locales = Language::active()->pluck('code')->toArray();
                
                // Fallback to config if database is not available
                if (empty($locales)) {
                    $locales = config('app.available_locales', ['vi', 'en']);
                }
            } catch (\Exception $e) {
                // Database might not be available (during migration, etc.)
                $locales = config('app.available_locales', ['vi', 'en']);
            }
        }
        
        return $locales;
    }

    /**
     * Get default locale.
     */
    protected function getDefaultLocale(): string
    {
        try {
            $defaultLanguage = Language::getDefault();
            if ($defaultLanguage) {
                return $defaultLanguage->code;
            }
        } catch (\Exception $e) {
            // Database might not be available
        }
        
        return config('app.locale', 'vi');
    }

    /**
     * Get locale from URL path.
     */
    protected function getLocaleFromPath(Request $request): ?string
    {
        $path = trim($request->getPathInfo(), '/');
        $segments = explode('/', $path);
        
        if (!empty($segments[0]) && strlen($segments[0]) === 2) {
            return $segments[0];
        }
        
        return null;
    }

    /**
     * Check if request is for admin panel.
     */
    protected function isAdminRequest(Request $request): bool
    {
        return $request->is('admin/*') || $request->is('admin');
    }

    /**
     * Check if request is for API.
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Get locale for admin panel.
     */
    protected function getAdminLocale(Request $request): string
    {
        // Admin can have different locale preference
        if (auth()->check() && auth()->user()->admin_locale) {
            $adminLocale = auth()->user()->admin_locale;
            if ($this->isValidLocale($adminLocale)) {
                return $adminLocale;
            }
        }
        
        // Fallback to regular locale detection
        return $this->detectLocale($request);
    }

    /**
     * Store user locale preference.
     */
    protected function storeUserLocalePreference(string $locale): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->locale !== $locale) {
                $user->update(['locale' => $locale]);
            }
        }
    }
}
