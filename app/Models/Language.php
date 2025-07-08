<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_rtl' => 'boolean',
        'date_format' => 'array',
        'number_format' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with translations.
     */
    public function translations()
    {
        return $this->hasMany(Translation::class, 'language_code', 'code');
    }

    /**
     * Relationship with translation values.
     */
    public function translationValues()
    {
        return $this->hasMany(TranslationValue::class, 'language_code', 'code');
    }

    /**
     * Get flag icon HTML.
     */
    protected function flagIconHtml(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $icon = $attributes['flag_icon'] ?? '';
                if (empty($icon)) {
                    return '<i class="fas fa-globe"></i>';
                }
                
                if (str_starts_with($icon, 'fa')) {
                    return "<i class=\"{$icon}\"></i>";
                }
                
                return "<img src=\"{$icon}\" alt=\"{$attributes['name']}\" class=\"flag-icon\">";
            }
        );
    }

    /**
     * Get display name with flag.
     */
    protected function displayName(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $this->flag_icon_html . ' ' . $attributes['native_name']
        );
    }

    /**
     * Get currency symbol.
     */
    protected function currencySymbol(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $code = $attributes['currency_code'] ?? '';
                return match($code) {
                    'VND' => '₫',
                    'USD' => '$',
                    'JPY' => '¥',
                    'EUR' => '€',
                    'GBP' => '£',
                    default => $code,
                };
            }
        );
    }

    /**
     * Format number according to language settings.
     */
    public function formatNumber($number, $decimals = 0)
    {
        $format = $this->number_format ?? [];
        
        $decimalSeparator = $format['decimal_separator'] ?? '.';
        $thousandsSeparator = $format['thousands_separator'] ?? ',';
        
        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Format currency according to language settings.
     */
    public function formatCurrency($amount, $showSymbol = true)
    {
        $formatted = $this->formatNumber($amount, 0);
        
        if (!$showSymbol) {
            return $formatted;
        }
        
        $symbol = $this->currency_symbol;
        $format = $this->number_format ?? [];
        $symbolPosition = $format['currency_position'] ?? 'after';
        
        return $symbolPosition === 'before' 
            ? $symbol . $formatted 
            : $formatted . ' ' . $symbol;
    }

    /**
     * Format date according to language settings.
     */
    public function formatDate($date, $format = null)
    {
        if (!$date) {
            return '';
        }
        
        $dateFormat = $format ?? $this->date_format['short'] ?? 'd/m/Y';
        
        return $date->format($dateFormat);
    }

    /**
     * Scope for active languages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default language.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get default language.
     */
    public static function getDefault()
    {
        return Cache::remember('default_language', 3600, function() {
            return self::where('is_default', true)->first() ?? self::where('code', 'vi')->first();
        });
    }

    /**
     * Get active languages.
     */
    public static function getActive()
    {
        return Cache::remember('active_languages', 3600, function() {
            return self::active()->ordered()->get();
        });
    }

    /**
     * Get language by code.
     */
    public static function getByCode($code)
    {
        return Cache::remember("language_{$code}", 3600, function() use ($code) {
            return self::where('code', $code)->first();
        });
    }

    /**
     * Set as default language.
     */
    public function setAsDefault()
    {
        // Remove default from other languages
        self::where('is_default', true)->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
        
        // Clear cache
        $this->clearCache();
        
        return $this;
    }

    /**
     * Clear language cache.
     */
    public function clearCache()
    {
        Cache::forget('default_language');
        Cache::forget('active_languages');
        Cache::forget("language_{$this->code}");
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($language) {
            $language->clearCache();
        });

        static::deleted(function ($language) {
            $language->clearCache();
        });

        static::creating(function ($language) {
            // Auto-set as default if no default exists
            if (!self::where('is_default', true)->exists()) {
                $language->is_default = true;
            }
        });
    }

    /**
     * Get translation completion percentage.
     */
    public function getTranslationCompletion()
    {
        $totalKeys = TranslationKey::count();
        if ($totalKeys === 0) {
            return 100;
        }
        
        $translatedKeys = TranslationValue::where('language_code', $this->code)
                                         ->where('is_approved', true)
                                         ->count();
        
        return round(($translatedKeys / $totalKeys) * 100, 2);
    }

    /**
     * Get missing translations count.
     */
    public function getMissingTranslationsCount()
    {
        $totalKeys = TranslationKey::count();
        $translatedKeys = TranslationValue::where('language_code', $this->code)
                                         ->where('is_approved', true)
                                         ->count();
        
        return $totalKeys - $translatedKeys;
    }

    /**
     * Check if language supports RTL.
     */
    public function isRtl()
    {
        return $this->is_rtl;
    }

    /**
     * Get language direction.
     */
    public function getDirection()
    {
        return $this->is_rtl ? 'rtl' : 'ltr';
    }

    /**
     * Get language statistics.
     */
    public function getStatistics()
    {
        return [
            'total_translations' => $this->translationValues()->count(),
            'approved_translations' => $this->translationValues()->where('is_approved', true)->count(),
            'pending_translations' => $this->translationValues()->where('is_approved', false)->count(),
            'auto_translations' => $this->translationValues()->where('is_auto_translated', true)->count(),
            'completion_percentage' => $this->getTranslationCompletion(),
            'missing_translations' => $this->getMissingTranslationsCount(),
        ];
    }
}
