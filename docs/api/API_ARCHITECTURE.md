# YukiMart API Architecture for Flutter App

## 📋 Overview

RESTful API architecture cho YukiMart Flutter mobile application với Laravel Sanctum authentication và auto-generated documentation.

## 🏗️ API Architecture

### Base Structure
```
/api/v1/
├── auth/           # Authentication endpoints
├── invoices/       # Invoice management
├── orders/         # Order management  
├── products/       # Product catalog
├── customers/      # Customer management
├── payments/       # Payment processing
├── reports/        # Analytics & reports
└── user/          # User profile & settings
```

### Versioning Strategy
- **Current Version**: v1
- **URL Pattern**: `/api/v1/{resource}`
- **Header Versioning**: `Accept: application/vnd.yukimart.v1+json`
- **Backward Compatibility**: Maintain v1 while developing v2

## 🔐 Authentication Strategy

### Laravel Sanctum Implementation
```php
// Token-based authentication for mobile apps
// Stateless API with Bearer tokens
// Refresh token mechanism
// Device-specific tokens
```

### Authentication Flow
```
1. Login → API Token + Refresh Token
2. API Requests → Bearer Token in Header
3. Token Refresh → New API Token
4. Logout → Revoke Token
```

### Security Features
- **Rate Limiting**: 60 requests/minute per user
- **Token Expiration**: 24 hours (configurable)
- **Refresh Token**: 30 days validity
- **Device Tracking**: Multiple device support
- **Permission-based Access**: Role & permission system

## 📊 API Response Standards

### Success Response Format
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Resource data
    },
    "meta": {
        "timestamp": "2025-08-06T10:30:00Z",
        "version": "v1",
        "request_id": "uuid"
    }
}
```

### Error Response Format
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    },
    "error_code": "VALIDATION_ERROR",
    "meta": {
        "timestamp": "2025-08-06T10:30:00Z",
        "version": "v1",
        "request_id": "uuid"
    }
}
```

### Pagination Format
```json
{
    "success": true,
    "data": [...],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 150,
        "last_page": 10,
        "from": 1,
        "to": 15,
        "has_more": true
    }
}
```

## 🎯 Core API Endpoints

### Authentication APIs
```
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh
GET    /api/v1/auth/me
PUT    /api/v1/auth/profile
POST   /api/v1/auth/change-password
```

### Invoice Management APIs
```
GET    /api/v1/invoices              # List invoices
POST   /api/v1/invoices              # Create invoice
GET    /api/v1/invoices/{id}         # Get invoice details
PUT    /api/v1/invoices/{id}         # Update invoice
DELETE /api/v1/invoices/{id}         # Delete invoice
GET    /api/v1/invoices/{id}/items   # Get invoice items
POST   /api/v1/invoices/{id}/payment # Process payment
```

### Additional Resources
```
# Products
GET    /api/v1/products
GET    /api/v1/products/search
GET    /api/v1/products/barcode/{code}

# Customers  
GET    /api/v1/customers
POST   /api/v1/customers
GET    /api/v1/customers/{id}

# Orders
GET    /api/v1/orders
POST   /api/v1/orders
GET    /api/v1/orders/{id}

# Payments
GET    /api/v1/payments
POST   /api/v1/payments
GET    /api/v1/payments/{id}
```

## 🔧 Technical Implementation

### Controller Structure
```php
app/Http/Controllers/Api/V1/
├── AuthController.php
├── InvoiceController.php
├── OrderController.php
├── ProductController.php
├── CustomerController.php
├── PaymentController.php
└── BaseApiController.php
```

### Middleware Stack
```php
Route::middleware(['api', 'auth:sanctum', 'throttle:api'])
    ->prefix('api/v1')
    ->group(function () {
        // API routes
    });
```

### Resource Transformers
```php
app/Http/Resources/V1/
├── UserResource.php
├── InvoiceResource.php
├── InvoiceCollection.php
├── ProductResource.php
└── CustomerResource.php
```

## 📚 Documentation Strategy

### Auto-Generated Documentation
- **OpenAPI 3.0 Specification**
- **Postman Collection Auto-Sync**
- **Interactive API Explorer**
- **Code Examples for Flutter**

### Documentation Tools
```php
// Custom documentation generator
app/Services/ApiDocumentationService.php

// Postman sync service  
app/Services/PostmanSyncService.php

// OpenAPI generator
app/Services/OpenApiGeneratorService.php
```

### Documentation Features
- **Real-time Updates**: Auto-sync when routes change
- **Request/Response Examples**: Live data examples
- **Authentication Guide**: Step-by-step integration
- **Error Code Reference**: Complete error handling guide
- **Flutter SDK**: Generated Dart models and services

## 🚀 Performance Optimization

### Caching Strategy
```php
// API Response Caching
Cache::remember("api.invoices.{user_id}", 300, function() {
    return $invoices;
});

// Database Query Optimization
Invoice::with(['customer', 'items.product'])->paginate(15);
```

### Rate Limiting
```php
// Per-user rate limiting
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### Response Compression
```php
// Gzip compression for large responses
// JSON minification
// Image optimization for product thumbnails
```

## 🔍 Monitoring & Analytics

### API Metrics
- **Response Times**: Average, P95, P99
- **Error Rates**: 4xx, 5xx by endpoint
- **Usage Patterns**: Most used endpoints
- **User Behavior**: API usage analytics

### Logging Strategy
```php
// Structured logging for API requests
Log::channel('api')->info('API Request', [
    'user_id' => $user->id,
    'endpoint' => $request->path(),
    'method' => $request->method(),
    'response_time' => $responseTime,
    'status_code' => $response->status()
]);
```

## 🧪 Testing Strategy

### API Testing Levels
1. **Unit Tests**: Controller logic, services
2. **Feature Tests**: Complete API workflows
3. **Integration Tests**: Database interactions
4. **Performance Tests**: Load testing, stress testing

### Test Coverage Goals
- **Controllers**: 95% coverage
- **Services**: 90% coverage
- **API Endpoints**: 100% functional coverage
- **Error Scenarios**: Complete error handling

## 📱 Flutter Integration

### Generated Dart Models
```dart
// Auto-generated from API responses
class Invoice {
  final String id;
  final String invoiceNumber;
  final Customer customer;
  final List<InvoiceItem> items;
  final double totalAmount;
  
  // fromJson, toJson methods
}
```

### API Service Layer
```dart
// Generated API client
class YukiMartApiClient {
  Future<ApiResponse<List<Invoice>>> getInvoices();
  Future<ApiResponse<Invoice>> createInvoice(CreateInvoiceRequest request);
  Future<ApiResponse<Invoice>> getInvoice(String id);
}
```

## 🔄 Migration Strategy

### Phase 1: Core APIs (Week 1)
- Authentication system
- Invoice management
- Basic CRUD operations

### Phase 2: Extended Features (Week 2)
- Product catalog
- Customer management
- Payment processing

### Phase 3: Advanced Features (Week 3)
- Reports & analytics
- Bulk operations
- Advanced filtering

### Phase 4: Optimization (Week 4)
- Performance tuning
- Documentation completion
- Testing & deployment

## 📋 Next Steps

1. **Setup Laravel Sanctum** for API authentication
2. **Create Base API Controllers** with versioning
3. **Implement Authentication APIs** (login, logout, profile)
4. **Develop Invoice Management APIs** with full CRUD
5. **Setup Documentation Generator** with Postman sync
6. **Create Flutter SDK** with generated models
7. **Testing & Optimization** for production readiness
