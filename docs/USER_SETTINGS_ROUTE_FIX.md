# User Settings Route Fix

## Problem
The route `admin.user-settings.store` was not defined, causing errors in the account menu when trying to save theme and language settings.

## Root Cause
The admin routes group in `routes/admin.php` was missing the `name('admin.')` prefix, which meant that route names were not being generated with the `admin.` prefix.

## Solution
Added the `name('admin.')` prefix to the admin routes group in `routes/admin.php`:

```php
// Before (line 67)
Route::prefix('admin')->middleware(['auth'])->group(function () {

// After (line 67)  
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
```

## Files Modified
- `routes/admin.php` - Added `name('admin.')` prefix to the main admin routes group

## Routes Affected
This fix affects all admin routes, ensuring they have the proper `admin.` prefix:

- `admin.user-settings.index`
- `admin.user-settings.store` 
- `admin.user-settings.show`
- `admin.user-settings.update`
- `admin.user-settings.destroy`
- And all other admin routes...

## Testing
To verify the fix is working:

1. Run the custom command:
   ```bash
   php artisan fix:user-settings-route
   ```

2. Check if the route exists:
   ```bash
   php artisan route:list | grep user-settings
   ```

3. Test in browser:
   - Go to admin panel
   - Click on account menu
   - Try changing theme or language
   - Should work without route errors

## Files Created for Debugging
- `app/Console/Commands/FixUserSettingsRoute.php` - Command to verify route fix
- `app/Console/Commands/CheckUserSettingsRoute.php` - Command to check route existence

## Related Components
- **Controller**: `App\Http\Controllers\Admin\CMS\UserSettingsController`
- **Model**: `App\Models\UserSetting`
- **View**: `resources/views/admin/elements/app_account_menu.blade.php` (lines 2234, 2275)
- **Language**: `lang/vi/common.php` (theme_updated, language_updated, error_saving_settings)

## Route Structure
```
admin.user-settings.index    GET     admin/user-settings
admin.user-settings.store    POST    admin/user-settings  
admin.user-settings.show     GET     admin/user-settings/{id}
admin.user-settings.update   PUT     admin/user-settings/{id}
admin.user-settings.destroy  DELETE  admin/user-settings/{id}
```

## Additional Notes
- The UserSettingsController is in the `Admin\CMS` namespace
- The routes are protected by the `auth` middleware
- Theme and language settings are saved via AJAX calls
- Success/error messages use Vietnamese translations
