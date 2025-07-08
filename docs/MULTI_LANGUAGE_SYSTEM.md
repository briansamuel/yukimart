# Multi-Language System Documentation

## Overview

This document describes the comprehensive multi-language system implemented for the YukiMart admin panel, supporting Vietnamese (vi) and English (en) languages with user-based locale switching.

## Features

### ✅ **Core Features Implemented**

1. **Language Middleware** - Automatic locale detection and setting
2. **User Settings Integration** - Language preferences saved per user
3. **Language Switching Service** - Centralized language management
4. **UI Language Switcher** - Account menu integration with AJAX
5. **Translation Files** - Comprehensive Vietnamese and English translations
6. **Route Integration** - Language switching routes
7. **Session Management** - Locale persistence across requests

### 🎯 **Supported Languages**

- **Vietnamese (vi)** - Default language
- **English (en)** - Secondary language

## Architecture

### 1. **Middleware: SetLocale**
**File:** `app/Http/Middleware/SetLocale.php`

**Purpose:** Automatically detects and sets the application locale for each request.

**Priority Order:**
1. URL parameter (`?locale=en`)
2. User settings (if authenticated)
3. Session storage
4. Browser Accept-Language header
5. Default locale (vi)

**Registration:** Added to `web` middleware group in `app/Http/Kernel.php`

### 2. **Service: LanguageService**
**File:** `app/Services/LanguageService.php`

**Key Methods:**
- `getSupportedLocales()` - Get available languages
- `switchLanguage($locale)` - Change current language
- `getUserLanguagePreference($userId)` - Get user's saved language
- `getLanguageOptions()` - Get options for UI dropdowns
- `trans($key, $replace, $locale)` - Translation with fallback

### 3. **Configuration**
**File:** `config/app.php`

```php
'locale' => 'vi',
'fallback_locale' => 'vi',
'available_locales' => ['vi', 'en'],
'supported_locales' => ['vi', 'en'],
'locale_names' => [
    'vi' => 'Tiếng Việt',
    'en' => 'English',
],
```

### 4. **Translation Files**

#### **Menu Translations**
- `lang/vi/menu.php` - Vietnamese menu items
- `lang/en/menu.php` - English menu items

#### **Common Translations**
- `lang/vi/common.php` - Vietnamese common terms
- `lang/en/common.php` - English common terms

#### **Key Translation Categories:**
- Main menu items (Dashboard, Products, Orders, etc.)
- Common actions (Save, Cancel, Edit, Delete, etc.)
- Status terms (Active, Inactive, Pending, etc.)
- Messages (Success, Error, Warning, etc.)
- Form elements and validation
- Time periods and statistics
- File management terms

### 5. **User Interface Integration**

#### **Language Switcher in Account Menu**
**File:** `resources/views/admin/elements/app_account_menu.blade.php`

**Features:**
- Dropdown with flag icons
- Current language display
- AJAX language switching
- User preference saving

#### **Left Sidebar Menu**
**File:** `resources/views/admin/left-aside.blade.php`

**Updated Elements:**
- Dashboard → `{{ __('menu.dashboard') }}`
- Pages → `{{ __('menu.pages') }}`
- News → `{{ __('menu.news') }}`
- Products → `{{ __('menu.products') }}`
- And more...

### 6. **Routes**
**File:** `routes/admin.php`

```php
// Language switching route
Route::get('/change-language/{locale}', function ($locale) {
    // Validation and switching logic
})->name('change-language');
```

## Usage

### 1. **For Developers**

#### **Using Translations in Blade Templates:**
```blade
{{ __('menu.dashboard') }}
{{ __('common.save') }}
{{ __('common.language') }}
```

#### **Using Translations in Controllers:**
```php
$message = __('common.success');
return response()->json(['message' => $message]);
```

#### **Using LanguageService:**
```php
use App\Services\LanguageService;

// Get current locale
$locale = LanguageService::getCurrentLocale();

// Switch language
LanguageService::switchLanguage('en');

// Get language options for dropdown
$options = LanguageService::getLanguageOptions();
```

### 2. **For Users**

#### **Switching Language:**
1. Click on user avatar in top-right corner
2. Click on language dropdown
3. Select desired language (English/Tiếng Việt)
4. Language changes immediately and preference is saved

#### **URL-based Language Switching:**
- Add `?locale=en` to any URL for English
- Add `?locale=vi` to any URL for Vietnamese

## Testing

### **Test Command**
```bash
php artisan test:multi-language
```

**This command tests:**
- Supported locales configuration
- Language switching functionality
- Translation file availability
- UI integration
- Middleware registration
- Route registration
- Missing translations detection

### **Manual Testing Steps**

1. **Login to Admin Panel**
   - Navigate to `/admin/dashboard`
   - Verify default language (Vietnamese)

2. **Test Language Switcher**
   - Click user avatar → Language dropdown
   - Switch to English
   - Verify menu items change to English
   - Verify language preference is saved

3. **Test URL Parameters**
   - Add `?locale=en` to URL
   - Verify language switches to English
   - Remove parameter, verify language persists

4. **Test User Preferences**
   - Switch language and logout
   - Login again, verify language preference is remembered

## File Structure

```
├── app/
│   ├── Http/Middleware/SetLocale.php
│   ├── Services/LanguageService.php
│   └── Console/Commands/TestMultiLanguage.php
├── config/app.php (updated)
├── lang/
│   ├── vi/
│   │   ├── menu.php
│   │   └── common.php
│   └── en/
│       ├── menu.php
│       └── common.php
├── resources/views/admin/
│   ├── left-aside.blade.php (updated)
│   └── elements/app_account_menu.blade.php (updated)
├── routes/admin.php (updated)
└── docs/MULTI_LANGUAGE_SYSTEM.md
```

## Best Practices

### 1. **Translation Keys**
- Use descriptive, hierarchical keys: `menu.dashboard`, `common.save`
- Group related translations: `menu.*`, `common.*`, `product.*`
- Use consistent naming conventions

### 2. **Adding New Translations**
1. Add key to both `lang/vi/` and `lang/en/` files
2. Use `{{ __('key') }}` in Blade templates
3. Test with both languages

### 3. **Fallback Strategy**
- Vietnamese is the fallback language
- Missing English translations will show Vietnamese
- Missing keys will show the key itself

### 4. **Performance Considerations**
- Translations are cached by Laravel
- Use `php artisan config:cache` after changes
- Consider using `php artisan view:cache` for production

## Troubleshooting

### **Common Issues**

1. **Language not switching:**
   - Check middleware registration in `app/Http/Kernel.php`
   - Verify route exists: `php artisan route:list | grep change-language`
   - Check browser console for JavaScript errors

2. **Translations not showing:**
   - Verify translation files exist in `lang/vi/` and `lang/en/`
   - Check translation key spelling
   - Clear cache: `php artisan config:clear`

3. **User preferences not saving:**
   - Check `user_settings` table exists
   - Verify user is authenticated
   - Check UserSetting model and relationships

### **Debug Commands**

```bash
# Test multi-language system
php artisan test:multi-language

# Clear caches
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Check routes
php artisan route:list | grep language

# Check middleware
php artisan route:list --middleware=SetLocale
```

## Future Enhancements

### **Potential Improvements**

1. **Additional Languages**
   - Add more languages (Chinese, Japanese, etc.)
   - Update configuration and translation files

2. **Advanced Features**
   - RTL language support
   - Pluralization rules
   - Date/time localization
   - Number formatting

3. **Management Interface**
   - Admin interface for managing translations
   - Import/export translation files
   - Translation completion tracking

4. **Performance Optimization**
   - Lazy loading of translation files
   - Translation caching strategies
   - CDN integration for language assets

## Conclusion

The multi-language system provides a robust foundation for internationalization with:
- ✅ Complete Vietnamese and English support
- ✅ User-based language preferences
- ✅ Seamless UI integration
- ✅ Comprehensive translation coverage
- ✅ Easy maintenance and extension

The system is production-ready and can be easily extended to support additional languages as needed.
