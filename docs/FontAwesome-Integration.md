# Font Awesome Icons Integration

This document describes how to use Font Awesome icons in the YukiMart application.

## Overview

The application now uses Font Awesome 6.4.0 icons instead of KeenIcons for better compatibility and easier usage. Icons are implemented using direct HTML `<i>` tags for simplicity and performance.

## Installation

Font Awesome is loaded via CDN in the main layout file:

```html
<!-- resources/views/admin/index.blade.php -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css" />
```

## Usage

### Direct HTML `<i>` Tags

Use Font Awesome icons directly in Blade templates with HTML `<i>` tags:

```html
<!-- Basic icons -->
<i class="fas fa-home"></i>
<i class="fas fa-edit"></i>
<i class="fas fa-trash"></i>

<!-- Icons with classes -->
<i class="fas fa-home me-2 text-primary"></i>
<i class="fas fa-edit me-2"></i>
<i class="fas fa-trash text-danger"></i>

<!-- Different sizes -->
<i class="fas fa-home fa-lg"></i>
<i class="fas fa-home fa-2x"></i>
<i class="fas fa-home fa-3x"></i>
```

### Status Icons with Colors

```html
<i class="fas fa-check-circle text-success"></i>    <!-- Success -->
<i class="fas fa-clock text-warning"></i>           <!-- Warning -->
<i class="fas fa-times-circle text-danger"></i>     <!-- Danger -->
<i class="fas fa-info-circle text-info"></i>        <!-- Info -->
<i class="fas fa-star text-primary"></i>            <!-- Primary -->
```

## Available Font Awesome Classes

### Basic Icons
- `fas fa-home` - Home
- `fas fa-user` - User
- `fas fa-cog` - Settings
- `fas fa-search` - Search
- `fas fa-filter` - Filter

### Action Icons
- `fas fa-edit` - Edit
- `fas fa-trash` - Delete
- `fas fa-eye` - View
- `fas fa-copy` - Copy
- `fas fa-print` - Print
- `fas fa-download` - Export/Download
- `fas fa-plus` - Add
- `fas fa-save` - Save
- `fas fa-times` - Cancel/Close

### Business Icons
- `fas fa-wallet` - Wallet
- `fas fa-box` - Package
- `fas fa-truck` - Delivery
- `fas fa-calendar` - Calendar
- `fas fa-clock` - Time
- `fas fa-rocket` - Rocket

### Status Icons
- `fas fa-check-circle` - Success
- `fas fa-times-circle` - Error
- `fas fa-question-circle` - Question
- `fas fa-info-circle` - Information
- `fas fa-star` - Star

### Arrow Icons
- `fas fa-arrow-up` - Arrow Up
- `fas fa-arrow-down` - Arrow Down
- `fas fa-arrow-left` - Arrow Left
- `fas fa-arrow-right` - Arrow Right

## Examples

### In Blade Templates

```html
<!-- Filter button -->
<button class="btn btn-primary">
    <i class="fas fa-filter me-2"></i>
    Filter
</button>

<!-- Action buttons -->
<button class="btn btn-sm btn-light" onclick="editItem()">
    <i class="fas fa-edit me-1"></i>
    Edit
</button>

<button class="btn btn-sm btn-danger" onclick="deleteItem()">
    <i class="fas fa-trash me-1"></i>
    Delete
</button>

<!-- Status indicators -->
<span class="badge badge-success">
    <i class="fas fa-check-circle text-success"></i>
    Completed
</span>

<!-- Icons with different sizes -->
<i class="fas fa-home fa-lg"></i>
<i class="fas fa-user fa-2x"></i>
<i class="fas fa-cog fa-3x"></i>
```

### In Controllers

```php
<?php

namespace App\Http\Controllers\Admin;

class ExampleController extends Controller
{
    public function getActionButtons($item)
    {
        return '
            <button class="btn btn-sm btn-primary" onclick="viewItem(' . $item->id . ')">
                <i class="fas fa-eye me-1"></i>
                View
            </button>
            <button class="btn btn-sm btn-warning" onclick="editItem(' . $item->id . ')">
                <i class="fas fa-edit me-1"></i>
                Edit
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteItem(' . $item->id . ')">
                <i class="fas fa-trash me-1"></i>
                Delete
            </button>
        ';
    }
}
```

## Icon Showcase

Visit `/admin/icons-showcase` to see all available icons and usage examples.

## Customization

### Adding New Icons

To add new icons, update the `$iconMappings` array in `app/Helpers/FontAwesomeHelper.php`:

```php
private static $iconMappings = [
    // ... existing mappings
    'new-icon' => 'fas fa-new-icon-class',
];
```

### Adding Shortcut Methods

Add new shortcut methods to the `FontAwesomeHelper` class:

```php
public static function newIcon($size = '', $class = '')
{
    return self::render('new-icon', $size, $class);
}
```

## Migration from KeenIcons

If you're migrating from KeenIcons:

1. Replace `keenIcon()` calls with `faIcon()`
2. Update icon names to match Font Awesome naming
3. Remove size parameters like 'fs-2' (Font Awesome uses different sizing)
4. Update any custom icon paths or duotone configurations

## Best Practices

1. **Consistency**: Use the helper methods for consistent icon rendering
2. **Performance**: Icons are loaded via CDN for better caching
3. **Accessibility**: Always provide meaningful alt text or aria-labels when needed
4. **Responsive**: Use appropriate sizing classes for different screen sizes
5. **Color**: Use Bootstrap color classes for consistent theming

## Troubleshooting

### Icons Not Displaying
- Check if Font Awesome CSS is loaded
- Verify icon names in the mappings array
- Check for JavaScript console errors

### Styling Issues
- Ensure Bootstrap classes are available
- Check CSS conflicts with existing styles
- Verify color and spacing classes

### Performance
- Font Awesome is loaded from CDN for better performance
- Consider using Font Awesome's subsetting if you only need specific icons
- Monitor page load times if using many icons
