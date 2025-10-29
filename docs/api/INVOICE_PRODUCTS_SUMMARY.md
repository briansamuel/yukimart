# Invoice API - Enhanced Features

## 📋 Overview

The Invoice API has been updated with two new features:
1. **`products_summary`** - Simplified list of products with names and quantities
2. **`payment_methods`** - Summary of payment methods used for the invoice

These features provide quick overview information without requiring full detailed data.

## 🚀 What's New

### 1. New Field: `products_summary`

A simplified array of products with only names and quantities:

```json
{
  "products_summary": [
    {
      "name": "Product Name 1",
      "quantity": 2
    },
    {
      "name": "Product Name 2",
      "quantity": 1
    }
  ]
}
```

### 2. New Field: `payment_methods`

A summary of payment methods used, grouped by method with totals:

```json
{
  "payment_methods": [
    {
      "method": "cash",
      "method_label": "Tiền mặt",
      "total_amount": 300000,
      "payment_count": 2
    },
    {
      "method": "card",
      "method_label": "Thẻ",
      "total_amount": 150000,
      "payment_count": 1
    }
  ]
}
```

## 📡 API Endpoints

### GET /api/v1/invoices

**Response includes `products_summary` and `payment_methods`:**

```json
{
  "status": "success",
  "message": "Invoices retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "invoice_number": "INV-20240822-2632",
        "status": "completed",
        "total_amount": 490600,
        "items_count": 2,
        "products_summary": [
          {
            "name": "Lọ thủy tinh nắp vặn Echo 1L",
            "quantity": 1
          },
          {
            "name": "Băng vệ sinh ban đêm Laurier Kao siêu mềm 40cm túi 7 miếng",
            "quantity": 3
          }
        ],
        "payment_methods": [
          {
            "method": "cash",
            "method_label": "Tiền mặt",
            "total_amount": 300000,
            "payment_count": 2
          },
          {
            "method": "card",
            "method_label": "Thẻ",
            "total_amount": 150000,
            "payment_count": 1
          }
        ],
        // ... other invoice fields
      }
    ]
  }
}
```

### GET /api/v1/invoices/{id}

**Response includes `products_summary` and `payment_methods`:**

```json
{
  "status": "success",
  "message": "Invoice retrieved successfully",
  "data": {
    "id": 1,
    "invoice_number": "INV-20240822-2632",
    "status": "completed",
    "total_amount": 490600,
    "items_count": 2,
    "products_summary": [
      {
        "name": "Lọ thủy tinh nắp vặn Echo 1L",
        "quantity": 1
      },
      {
        "name": "Băng vệ sinh ban đêm Laurier Kao siêu mềm 40cm túi 7 miếng",
        "quantity": 3
      }
    ],
    "payment_methods": [
      {
        "method": "cash",
        "method_label": "Tiền mặt",
        "total_amount": 300000,
        "payment_count": 2
      },
      {
        "method": "card",
        "method_label": "Thẻ",
        "total_amount": 150000,
        "payment_count": 1
      }
    ],
    "items": [
      // Full detailed items array (unchanged)
    ],
    "payments": [
      // Full detailed payments array (unchanged)
    ],
    // ... other invoice fields
  }
}
```

## 🔧 Technical Implementation

### Changes Made

1. **InvoiceResource.php** - Added `products_summary` and `payment_methods` fields
2. **InvoiceController.php** - Updated to load `invoiceItems` relationship (payments already loaded)
3. **Backward Compatibility** - All existing fields remain unchanged

### Code Changes

#### InvoiceResource.php
```php
// Products summary (name and quantity only)
'products_summary' => $this->whenLoaded('invoiceItems', function () {
    return $this->invoiceItems->map(function ($item) {
        return [
            'name' => $item->product_name,
            'quantity' => (int) $item->quantity,
        ];
    });
}),

// Payment methods summary
'payment_methods' => $this->whenLoaded('payments', function () {
    $completedPayments = $this->payments->where('status', 'completed');

    if ($completedPayments->isEmpty()) {
        return [];
    }

    // Group payments by method and sum amounts
    $methodSummary = $completedPayments->groupBy('payment_method')->map(function ($payments, $method) {
        return [
            'method' => $method,
            'method_label' => $this->getPaymentMethodLabel($method),
            'total_amount' => (float) $payments->sum('actual_amount'),
            'payment_count' => $payments->count(),
        ];
    })->values();

    return $methodSummary;
}),
```

#### InvoiceController.php
```php
// Updated to include invoiceItems relationship
$query = Invoice::with(['customer', 'branchShop', 'creator', 'seller', 'invoiceItems']);
```

## ✅ Benefits

1. **Simplified Data**: Quick access to product names/quantities and payment method summaries
2. **Performance**: Lighter payload for overview displays
3. **Backward Compatible**: Existing API consumers unaffected
4. **Consistent**: Available in both list and detail endpoints
5. **Grouped Data**: Payment methods are automatically grouped and summed
6. **Localized Labels**: Payment method labels in Vietnamese

## 🧪 Testing

Both features have been tested and verified:

**Products Summary:**
- ✅ Field exists in API response
- ✅ Correct product names and quantities
- ✅ JSON format matches specification
- ✅ Count matches full items array

**Payment Methods:**
- ✅ Field exists in API response
- ✅ Correct grouping by payment method
- ✅ Accurate total amounts and counts
- ✅ Vietnamese labels display correctly
- ✅ Only completed payments included

**General:**
- ✅ Backward compatibility maintained

## 📝 Usage Examples

### Frontend Display
```javascript
// Display products summary in a simple list
invoice.products_summary.forEach(product => {
  console.log(`${product.name}: ${product.quantity}`);
});

// Display payment methods summary
invoice.payment_methods.forEach(method => {
  console.log(`${method.method_label}: ${method.total_amount.toLocaleString()} VND (${method.payment_count} payments)`);
});
```

### Mobile App
```swift
// Swift example for iOS
for product in invoice.productsSummary {
    print("\(product.name): \(product.quantity)")
}

for method in invoice.paymentMethods {
    print("\(method.methodLabel): \(method.totalAmount) VND (\(method.paymentCount) payments)")
}
```

## 🔄 Migration Notes

- **No breaking changes** - All existing fields remain
- **Automatic availability** - No client-side changes required
- **Optional usage** - Can use either `items`/`products_summary` and `payments`/`payment_methods`
