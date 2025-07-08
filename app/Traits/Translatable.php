<?php

namespace App\Traits;

use App\Models\Translation;
use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

trait Translatable
{
    /**
     * Fields that can be translated.
     */
    protected $translatable = [];

    /**
     * Get translatable fields.
     */
    public function getTranslatableFields()
    {
        return property_exists($this, 'translatable') ? $this->translatable : [];
    }

    /**
     * Relationship with translations.
     */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get translation for specific field and language.
     */
    public function getTranslation($field, $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        
        // Return original value if it's the default language
        $defaultLanguage = Language::getDefault();
        if ($defaultLanguage && $locale === $defaultLanguage->code) {
            return $this->getAttribute($field);
        }

        $cacheKey = "translation_{$this->getMorphClass()}_{$this->id}_{$field}_{$locale}";
        
        return Cache::remember($cacheKey, 3600, function() use ($field, $locale) {
            $translation = $this->translations()
                               ->where('field_name', $field)
                               ->where('language_code', $locale)
                               ->where('is_approved', true)
                               ->first();

            if ($translation) {
                return $translation->field_value;
            }

            // Fallback to default language
            $defaultLanguage = Language::getDefault();
            if ($defaultLanguage && $locale !== $defaultLanguage->code) {
                return $this->getAttribute($field);
            }

            return null;
        });
    }

    /**
     * Set translation for specific field and language.
     */
    public function setTranslation($field, $value, $locale, $approved = false)
    {
        // Don't create translation for default language
        $defaultLanguage = Language::getDefault();
        if ($defaultLanguage && $locale === $defaultLanguage->code) {
            return $this->update([$field => $value]);
        }

        $translation = $this->translations()
                           ->where('field_name', $field)
                           ->where('language_code', $locale)
                           ->first();

        if ($translation) {
            $translation->update([
                'field_value' => $value,
                'is_approved' => $approved,
                'updated_by' => auth()->id(),
            ]);
        } else {
            $translation = $this->translations()->create([
                'field_name' => $field,
                'field_value' => $value,
                'language_code' => $locale,
                'is_approved' => $approved,
                'created_by' => auth()->id(),
            ]);
        }

        // Clear cache
        $this->clearTranslationCache($field, $locale);

        return $translation;
    }

    /**
     * Get all translations for a field.
     */
    public function getTranslations($field)
    {
        $translations = $this->translations()
                            ->where('field_name', $field)
                            ->get()
                            ->keyBy('language_code');

        // Add default language value
        $defaultLanguage = Language::getDefault();
        if ($defaultLanguage) {
            $translations[$defaultLanguage->code] = (object) [
                'field_value' => $this->getAttribute($field),
                'language_code' => $defaultLanguage->code,
                'is_approved' => true,
                'is_default' => true,
            ];
        }

        return $translations;
    }

    /**
     * Check if field has translation in specific language.
     */
    public function hasTranslation($field, $locale)
    {
        $defaultLanguage = Language::getDefault();
        if ($defaultLanguage && $locale === $defaultLanguage->code) {
            return !empty($this->getAttribute($field));
        }

        return $this->translations()
                   ->where('field_name', $field)
                   ->where('language_code', $locale)
                   ->where('is_approved', true)
                   ->exists();
    }

    /**
     * Get translated attribute.
     */
    public function getTranslatedAttribute($field, $locale = null)
    {
        if (!in_array($field, $this->getTranslatableFields())) {
            return $this->getAttribute($field);
        }

        $translation = $this->getTranslation($field, $locale);
        
        return $translation ?? $this->getAttribute($field);
    }

    /**
     * Magic method to get translated attributes.
     */
    public function __get($key)
    {
        // Check if it's a translatable field
        if (in_array($key, $this->getTranslatableFields())) {
            return $this->getTranslatedAttribute($key);
        }

        return parent::__get($key);
    }

    /**
     * Get translation completion for this model.
     */
    public function getTranslationCompletion()
    {
        $translatableFields = $this->getTranslatableFields();
        $activeLanguages = Language::getActive();
        
        if (empty($translatableFields) || $activeLanguages->isEmpty()) {
            return 100;
        }

        $totalRequired = count($translatableFields) * $activeLanguages->count();
        $completed = 0;

        foreach ($activeLanguages as $language) {
            foreach ($translatableFields as $field) {
                if ($this->hasTranslation($field, $language->code)) {
                    $completed++;
                }
            }
        }

        return $totalRequired > 0 ? round(($completed / $totalRequired) * 100, 2) : 100;
    }

    /**
     * Get missing translations for this model.
     */
    public function getMissingTranslations()
    {
        $missing = [];
        $translatableFields = $this->getTranslatableFields();
        $activeLanguages = Language::getActive();

        foreach ($activeLanguages as $language) {
            foreach ($translatableFields as $field) {
                if (!$this->hasTranslation($field, $language->code)) {
                    $missing[] = [
                        'field' => $field,
                        'language' => $language->code,
                        'language_name' => $language->name,
                    ];
                }
            }
        }

        return $missing;
    }

    /**
     * Clear translation cache for specific field and language.
     */
    protected function clearTranslationCache($field = null, $locale = null)
    {
        if ($field && $locale) {
            $cacheKey = "translation_{$this->getMorphClass()}_{$this->id}_{$field}_{$locale}";
            Cache::forget($cacheKey);
        } else {
            // Clear all translation cache for this model
            $translatableFields = $this->getTranslatableFields();
            $activeLanguages = Language::getActive();

            foreach ($activeLanguages as $language) {
                foreach ($translatableFields as $fieldName) {
                    $cacheKey = "translation_{$this->getMorphClass()}_{$this->id}_{$fieldName}_{$language->code}";
                    Cache::forget($cacheKey);
                }
            }
        }
    }

    /**
     * Boot the trait.
     */
    public static function bootTranslatable()
    {
        static::saved(function ($model) {
            $model->clearTranslationCache();
        });

        static::deleted(function ($model) {
            // Delete all translations when model is deleted
            $model->translations()->delete();
            $model->clearTranslationCache();
        });
    }

    /**
     * Scope to include translations.
     */
    public function scopeWithTranslations($query, $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        
        return $query->with(['translations' => function($q) use ($locale) {
            $q->where('language_code', $locale)
              ->where('is_approved', true);
        }]);
    }

    /**
     * Scope to filter by translated field.
     */
    public function scopeWhereTranslation($query, $field, $operator, $value, $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        
        return $query->whereHas('translations', function($q) use ($field, $operator, $value, $locale) {
            $q->where('field_name', $field)
              ->where('language_code', $locale)
              ->where('field_value', $operator, $value)
              ->where('is_approved', true);
        });
    }

    /**
     * Get model with all translations.
     */
    public function getAllTranslations()
    {
        $result = [];
        $translatableFields = $this->getTranslatableFields();
        
        foreach ($translatableFields as $field) {
            $result[$field] = $this->getTranslations($field);
        }
        
        return $result;
    }

    /**
     * Bulk update translations.
     */
    public function updateTranslations(array $translations, $approved = false)
    {
        foreach ($translations as $locale => $fields) {
            foreach ($fields as $field => $value) {
                if (in_array($field, $this->getTranslatableFields())) {
                    $this->setTranslation($field, $value, $locale, $approved);
                }
            }
        }

        return $this;
    }

    /**
     * Duplicate translations to another model.
     */
    public function duplicateTranslationsTo($targetModel)
    {
        if (!method_exists($targetModel, 'getTranslatableFields')) {
            return false;
        }

        $sourceFields = $this->getTranslatableFields();
        $targetFields = $targetModel->getTranslatableFields();
        $commonFields = array_intersect($sourceFields, $targetFields);

        foreach ($commonFields as $field) {
            $translations = $this->getTranslations($field);
            
            foreach ($translations as $locale => $translation) {
                if (!isset($translation->is_default) || !$translation->is_default) {
                    $targetModel->setTranslation(
                        $field, 
                        $translation->field_value, 
                        $locale, 
                        $translation->is_approved ?? false
                    );
                }
            }
        }

        return true;
    }
}
