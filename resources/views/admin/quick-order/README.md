# Quick Order System - Modular Structure

## 📁 File Structure

```
resources/views/admin/quick-order/
├── index.blade.php                    # Main file (133 lines)
├── elements/
│   └── tab-template.blade.php         # Reusable tab template
└── modals/
    ├── discount-modal.blade.php       # Discount calculation modal
    ├── other-charges-modal.blade.php  # Additional charges modal
    ├── customer-info-modal.blade.php  # Customer details modal
    ├── confirm-close-tab-modal.blade.php # Tab close confirmation
    └── invoice-selection-modal.blade.php # Invoice selection for returns

public/admin-assets/
├── css/
│   └── quick-orders.css              # All CSS styles (1500+ lines)
└── js/
    ├── quick-order-main.js           # Core functionality (1300+ lines)
    └── quick-order-modals.js         # Modal interactions (400+ lines)
```

## 🚀 Features

### Core Functionality
- **Multi-tab System**: Support for Order, Invoice, and Return tabs
- **Product Search**: Real-time barcode/SKU/name search with suggestions
- **Customer Management**: Search and select customers with autocomplete
- **Payment Methods**: Cash, Transfer, Card, Wallet support
- **Bank Account Selection**: For transfer payments with QR code
- **Order Calculations**: Automatic subtotal, discount, charges calculation

### Advanced Features
- **Auto-save Drafts**: Saves work every 30 seconds to localStorage
- **Draft Recovery**: Restores unsaved work on page refresh
- **Keyboard Shortcuts**: F3 (focus barcode), F7 (exchange search), Ctrl+N/I/R (new tabs)
- **Real-time Updates**: Live time display and order totals
- **Responsive Design**: Works on desktop and tablet devices

### Modal System
- **Discount Modal**: Percentage or fixed amount discounts
- **Other Charges Modal**: Additional fees and charges
- **Customer Info Modal**: Complete customer details with history tabs
- **Invoice Selection**: For return order processing

## 🎯 Usage Guide

### Creating New Tabs
```javascript
// Create new order tab
addNewTab('order');

// Create new invoice tab  
addNewTab('invoice');

// Create new return tab
addNewTab('return');
```

### Adding Products
```javascript
// Add product by barcode/SKU
addProductByBarcode('BARCODE123');

// Add product from suggestion
addProductFromSuggestion(productId);

// Add product to specific tab
addProductToTab(tabId, productData);
```

### Managing Customers
```javascript
// Show customer info modal
showCustomerInfo(customerId);

// Customer search is automatic via input field
```

### Working with Modals
```javascript
// Open discount modal
openDiscountModal();

// Open other charges modal
openOtherChargesModal();

// Apply discount
applyDiscount();

// Apply other charges
applyOtherCharges();
```

## ⌨️ Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `F3` | Focus barcode input |
| `F7` | Focus exchange search (return tabs) |
| `Ctrl+N` | Create new order tab |
| `Ctrl+I` | Create new invoice tab |
| `Ctrl+R` | Create new return tab |
| `Enter` | Add product from barcode input |

## 🔧 Configuration

### Server Data Required
The system expects these variables to be passed from the server:

```php
// In your controller
$defaultBranchShop = BranchShop::where('is_default', true)->first();
$bankAccounts = BankAccount::where('is_active', true)->get();
$customers = Customer::select('id', 'name', 'phone', 'customer_code')->get();
$sellers = User::where('role', 'seller')->get();

return view('admin.quick-order.index', compact(
    'defaultBranchShop', 
    'bankAccounts', 
    'customers', 
    'sellers'
));
```

### JavaScript Initialization
```javascript
// Pass data to JavaScript
window.defaultBranchShop = @json($defaultBranchShop ?? null);
window.bankAccounts = @json($bankAccounts ?? []);
window.customers = @json($customers ?? []);
window.sellers = @json($sellers ?? []);

// Initialize system
$(document).ready(function() {
    initializeQuickOrder();
});
```

## 🛠️ Customization

### Adding New Tab Types
1. Update `addNewTab()` function to handle new type
2. Add type-specific UI in `setupTabTypeUI()`
3. Create appropriate templates in `tab-template.blade.php`

### Adding New Modals
1. Create modal file in `modals/` directory
2. Add modal JavaScript in `quick-order-modals.js`
3. Include modal in `index.blade.php`

### Styling Changes
- Edit `public/admin-assets/css/quick-orders.css`
- Use existing CSS classes for consistency
- Follow the established color scheme

## 🐛 Troubleshooting

### Common Issues
1. **Tabs not creating**: Check console for JavaScript errors
2. **Product search not working**: Verify AJAX endpoint `/admin/quick-order/search-product`
3. **Modals not opening**: Check Bootstrap JS is loaded
4. **Drafts not saving**: Check localStorage permissions
5. **CSS not loading**: Verify file paths in `index.blade.php`

### Debug Mode
```javascript
// Enable debug logging
window.quickOrderDebug = true;

// Check tab data
console.log('Current tabs:', orderTabs);
console.log('Active tab:', activeTabId);
```

## 📈 Performance

### Optimizations Implemented
- **Debounced Search**: 300ms delay for product/customer search
- **Request Cancellation**: Cancels previous AJAX requests
- **Efficient DOM Updates**: Minimal DOM manipulation
- **CSS Caching**: Separate CSS file for browser caching
- **LocalStorage**: Client-side draft persistence

### Best Practices
- Keep tab count reasonable (< 10 tabs)
- Clear old drafts periodically
- Monitor memory usage with many products
- Use pagination for large customer lists

## 🔄 Migration from Old System

The old monolithic file has been split into:
- **95% CSS** → `quick-orders.css`
- **90% JavaScript** → `quick-order-main.js` + `quick-order-modals.js`
- **HTML Templates** → `tab-template.blade.php` + modal files
- **Main Structure** → `index.blade.php` (133 lines)

All functionality has been preserved and enhanced with new features like auto-save and improved UX.
