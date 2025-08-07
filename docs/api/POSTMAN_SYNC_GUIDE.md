# YukiMart API - Postman Collection Sync Guide

## 🎯 **OVERVIEW**

YukiMart API có tính năng tự động đồng bộ với Postman Collection, giúp team development có thể test API một cách dễ dàng và hiệu quả.

## 📋 **SETUP REQUIREMENTS**

### 1. **Postman Account Setup**
- Tạo account trên [Postman](https://www.postman.com/)
- Tạo workspace cho project
- Generate API key từ Postman settings

### 2. **Environment Configuration**
Cập nhật file `.env` với thông tin Postman:

```env
# Postman Integration
POSTMAN_API_KEY=your_postman_api_key_here
POSTMAN_COLLECTION_ID=your_collection_id_here
POSTMAN_WORKSPACE_ID=your_workspace_id_here
POSTMAN_AUTO_SYNC=true
```

### 3. **Get Postman API Key**
1. Đăng nhập vào Postman
2. Vào **Settings** → **API Keys**
3. Click **Generate API Key**
4. Copy API key và paste vào `.env`

### 4. **Create Collection**
1. Tạo collection mới trong Postman workspace
2. Copy Collection ID từ URL hoặc collection settings
3. Paste Collection ID vào `.env`

## 🚀 **SYNC METHODS**

### Method 1: **Manual Sync via API**

```bash
# POST request to sync endpoint
curl -X POST http://yukimart.local/api/v1/docs/postman/sync \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
```

### Method 2: **Automatic Sync**
API sẽ tự động sync khi:
- Có routes mới được thêm
- Controllers được cập nhật
- API documentation được regenerate

### Method 3: **Command Line**
```bash
# Chạy sync command
php artisan api:sync-postman
```

## 📊 **COLLECTION STRUCTURE**

Postman collection sẽ được tổ chức theo folders:

### **📁 Authentication**
- POST `/auth/login` - User login
- POST `/auth/register` - User registration
- POST `/auth/logout` - User logout
- POST `/auth/refresh` - Token refresh
- GET `/auth/me` - Get user profile
- PUT `/auth/profile` - Update profile
- POST `/auth/change-password` - Change password
- POST `/auth/forgot-password` - Password reset request
- POST `/auth/reset-password` - Password reset

### **📁 User Profile**
- GET `/user/profile` - Get detailed profile
- PUT `/user/profile` - Update profile
- GET `/user/permissions` - Get user permissions
- GET `/user/branches` - Get user branches

### **📁 Invoice Management**
- GET `/invoices` - List invoices
- POST `/invoices` - Create invoice
- GET `/invoices/{id}` - Get invoice details
- PUT `/invoices/{id}` - Update invoice
- DELETE `/invoices/{id}` - Delete invoice
- GET `/invoices/{id}/items` - Get invoice items
- POST `/invoices/{id}/payment` - Process payment
- PUT `/invoices/{id}/status` - Update status
- GET `/invoices/{id}/pdf` - Generate PDF
- POST `/invoices/bulk-update` - Bulk operations
- GET `/invoices/summary` - Invoice summary

### **📁 Order Management**
- GET `/orders` - List orders
- POST `/orders` - Create order
- GET `/orders/{id}` - Get order details
- PUT `/orders/{id}` - Update order
- DELETE `/orders/{id}` - Delete order
- GET `/orders/{id}/items` - Get order items
- PUT `/orders/{id}/status` - Update order status
- POST `/orders/{id}/payment` - Record payment

### **📁 Product Catalog**
- GET `/products` - List products
- POST `/products` - Create product
- GET `/products/{id}` - Get product details
- PUT `/products/{id}` - Update product
- DELETE `/products/{id}` - Delete product
- GET `/products/search` - Search products
- GET `/products/barcode/{barcode}` - Find by barcode
- GET `/products/{id}/variants` - Get product variants
- GET `/products/{id}/inventory` - Get inventory info

### **📁 Customer Management**
- GET `/customers` - List customers
- POST `/customers` - Create customer
- GET `/customers/{id}` - Get customer details
- PUT `/customers/{id}` - Update customer
- DELETE `/customers/{id}` - Delete customer
- GET `/customers/search` - Search customers
- GET `/customers/{id}/orders` - Get customer orders
- GET `/customers/{id}/invoices` - Get customer invoices
- GET `/customers/{id}/payments` - Get customer payments

### **📁 Payment Processing**
- GET `/payments` - List payments
- POST `/payments` - Create payment
- GET `/payments/{id}` - Get payment details
- PUT `/payments/{id}` - Update payment
- DELETE `/payments/{id}` - Delete payment
- GET `/payments/summary` - Payment summary
- POST `/payments/bulk-create` - Bulk create payments
- POST `/payments/{id}/approve` - Approve payment

### **📁 Utilities**
- GET `/utils/branches` - Get branches
- GET `/utils/payment-methods` - Get payment methods
- GET `/health` - Health check
- GET `/status` - Status check

### **📁 General**
- GET `/docs/openapi` - OpenAPI specification
- GET `/docs/openapi/download` - Download spec
- POST `/docs/postman/sync` - Sync to Postman
- GET `/docs/info` - API information

## 🔧 **COLLECTION FEATURES**

### **Environment Variables**
```json
{
  "base_url": "http://yukimart.local/api/v1",
  "api_token": "your_bearer_token_here"
}
```

### **Authentication**
- Bearer token authentication được setup sẵn
- Variable `{{api_token}}` cho tất cả protected endpoints
- Login request sẽ tự động set token

### **Request Examples**
Mỗi request đều có:
- ✅ **Headers** đã setup (Accept, Content-Type)
- ✅ **Authentication** cho protected routes
- ✅ **Request body examples** với sample data
- ✅ **URL parameters** với variables
- ✅ **Query parameters** documentation

### **Sample Request Bodies**

#### **Login Request**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": false,
  "device_name": "Mobile App"
}
```

#### **Create Product**
```json
{
  "product_name": "iPhone 15 Pro Max",
  "product_description": "Latest iPhone with advanced features",
  "sku": "IPH15PM001",
  "barcode": "1234567890123",
  "category_id": 1,
  "brand": "Apple",
  "cost_price": 25000000,
  "sale_price": 30000000,
  "regular_price": 32000000,
  "reorder_point": 10,
  "weight": 221,
  "points": 300,
  "initial_stock": 50
}
```

#### **Create Order**
```json
{
  "customer_id": 1,
  "note": "Giao hàng nhanh",
  "due_date": "2025-08-10",
  "discount_amount": 50000,
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "unit_price": 100000,
      "discount": 10000
    },
    {
      "product_id": 2,
      "quantity": 1,
      "unit_price": 200000
    }
  ]
}
```

## 🔄 **SYNC WORKFLOW**

### **Automatic Sync Process**
1. **Route Detection**: System scans all API v1 routes
2. **Folder Organization**: Groups endpoints by functionality
3. **Request Generation**: Creates Postman requests with examples
4. **Authentication Setup**: Configures Bearer token auth
5. **Variable Setup**: Sets up environment variables
6. **Collection Update**: Pushes to Postman via API

### **Sync Triggers**
- Manual API call to `/docs/postman/sync`
- Artisan command execution
- Automatic on route changes (if enabled)
- CI/CD pipeline integration

## 📈 **BENEFITS**

### **For Developers**
- ✅ **Ready-to-use** Postman collection
- ✅ **No manual setup** required
- ✅ **Always up-to-date** with latest API changes
- ✅ **Sample requests** with realistic data
- ✅ **Authentication** pre-configured

### **For QA Team**
- ✅ **Complete test suite** in Postman
- ✅ **Organized by features** for easy testing
- ✅ **Environment variables** for different stages
- ✅ **Request examples** for validation

### **For Flutter Team**
- ✅ **API exploration** before implementation
- ✅ **Request/response examples** for reference
- ✅ **Authentication flow** testing
- ✅ **Data structure** understanding

## 🛠️ **TROUBLESHOOTING**

### **Common Issues**

#### **Sync Failed - API Key Invalid**
```bash
# Check API key in .env
POSTMAN_API_KEY=your_valid_api_key_here
```

#### **Collection Not Found**
```bash
# Verify collection ID
POSTMAN_COLLECTION_ID=your_valid_collection_id_here
```

#### **Permission Denied**
- Ensure API key has write permissions
- Check workspace access rights
- Verify collection ownership

### **Debug Sync Process**
```bash
# Check logs for sync details
tail -f storage/logs/laravel.log | grep "Postman"
```

### **Manual Verification**
1. Check Postman collection after sync
2. Verify folder structure
3. Test sample requests
4. Confirm authentication setup

## 🎯 **NEXT STEPS**

1. **Setup Postman credentials** in `.env`
2. **Run initial sync** via API or command
3. **Import collection** in Postman
4. **Configure environment** variables
5. **Test API endpoints** with sample data
6. **Share collection** with team members

## 🏆 **RESULT**

Sau khi sync thành công, bạn sẽ có:
- ✅ **65+ API endpoints** ready to test
- ✅ **Organized folder structure** by features
- ✅ **Authentication setup** with Bearer tokens
- ✅ **Sample request bodies** with realistic data
- ✅ **Environment variables** for easy configuration
- ✅ **Auto-updated collection** khi API thay đổi

**Perfect for Flutter development team to start API integration immediately!**
