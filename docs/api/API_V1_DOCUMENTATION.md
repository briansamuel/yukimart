# YukiMart API v1 Documentation

## 🚀 **API Overview**

YukiMart API v1 là RESTful API được thiết kế cho Flutter App, cung cấp các chức năng quản lý hóa đơn và authentication.

### **Base URL**
```
http://yukimart.local/api/v1
```

### **Authentication**
API sử dụng Laravel Sanctum với Bearer Token authentication.

## 📋 **API Endpoints**

### **1. Authentication Endpoints**

#### **POST /auth/login**
Đăng nhập và nhận token

**Request:**
```json
{
  "email": "yukimart@gmail.com",
  "password": "123456",
  "device_name": "Flutter App" // optional
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 12,
      "username": "yukimart",
      "email": "yukimart@gmail.com",
      "full_name": "YukiMart Admin",
      "phone": "0987654321",
      "avatar": null,
      "status": "active",
      "roles": [],
      "branch_shops": [],
      "primary_branch_shop": null
    },
    "token": "29|8j8MAOjMWXeiZ0DvI48OlwNtOrHjYGVebfoSiFVF6dfd9a0a",
    "token_type": "Bearer",
    "expires_in": null
  }
}
```

#### **GET /auth/profile**
Lấy thông tin profile user (Requires Authentication)

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": "success",
  "message": "Profile retrieved successfully",
  "data": {
    "user": {
      "id": 12,
      "username": "yukimart",
      "email": "yukimart@gmail.com",
      "full_name": "YukiMart Admin",
      "phone": "0987654321",
      "avatar": null,
      "status": "active",
      "roles": [],
      "branch_shops": []
    }
  }
}
```

#### **POST /auth/logout**
Đăng xuất và revoke token (Requires Authentication)

#### **POST /auth/refresh**
Refresh token (Requires Authentication)

### **2. Invoice Management Endpoints**

#### **GET /invoices**
Lấy danh sách hóa đơn với pagination và filters (Requires Authentication)

**Query Parameters:**
- `status`: Filter by status (draft, sent, processing, completed, cancelled)
- `payment_status`: Filter by payment status (unpaid, partial, paid, overdue)
- `customer_id`: Filter by customer ID
- `branch_shop_id`: Filter by branch shop ID
- `date_from`: Filter from date (Y-m-d)
- `date_to`: Filter to date (Y-m-d)
- `search`: Search in invoice number, customer name, reference number
- `sort_by`: Sort field (default: created_at)
- `sort_order`: Sort order (asc, desc - default: desc)
- `per_page`: Items per page (default: 15)

**Response:**
```json
{
  "status": "success",
  "message": "Invoices retrieved successfully",
  "data": [
    {
      "id": 1,
      "invoice_number": "HD001",
      "invoice_type": "sale",
      "status": "draft",
      "payment_status": "unpaid",
      "invoice_date": "2025-08-07",
      "due_date": "2025-08-14",
      "customer": {
        "id": 1,
        "name": "Nguyễn Văn A",
        "phone": "0123456789"
      },
      "subtotal": 1000000.00,
      "tax_amount": 100000.00,
      "discount_amount": 50000.00,
      "total_amount": 1050000.00,
      "amount_paid": 0.00,
      "amount_due": 1050000.00,
      "items_count": 2,
      "created_at": "2025-08-07T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1,
    "from": 1,
    "to": 1
  }
}
```

#### **GET /invoices/{id}**
Lấy chi tiết hóa đơn (Requires Authentication)

**Response:**
```json
{
  "status": "success",
  "message": "Invoice retrieved successfully",
  "data": {
    "id": 1,
    "invoice_number": "HD001",
    "invoice_type": "sale",
    "status": "draft",
    "payment_status": "unpaid",
    "invoice_date": "2025-08-07",
    "due_date": "2025-08-14",
    "customer": {
      "id": 1,
      "name": "Nguyễn Văn A",
      "phone": "0123456789",
      "email": "nguyenvana@email.com"
    },
    "branch_shop": {
      "id": 1,
      "name": "Chi nhánh chính",
      "address": "123 Đường ABC"
    },
    "items": [
      {
        "id": 1,
        "product_name": "Sản phẩm A",
        "product_sku": "SP001",
        "quantity": 2,
        "unit": "cái",
        "unit_price": 500000.00,
        "discount_amount": 25000.00,
        "tax_amount": 47500.00,
        "line_total": 522500.00
      }
    ],
    "subtotal": 1000000.00,
    "tax_amount": 100000.00,
    "discount_amount": 50000.00,
    "total_amount": 1050000.00,
    "amount_paid": 0.00,
    "amount_due": 1050000.00,
    "notes": "Ghi chú hóa đơn",
    "created_at": "2025-08-07T10:00:00.000000Z"
  }
}
```

#### **POST /invoices**
Tạo hóa đơn mới (Requires Authentication)

**Request:**
```json
{
  "customer_id": 1, // nullable, nếu null thì dùng customer_name
  "customer_name": "Khách lẻ", // required if customer_id is null
  "branch_shop_id": 1,
  "invoice_type": "sale", // sale, service, other
  "invoice_date": "2025-08-07",
  "due_date": "2025-08-14", // nullable
  "payment_terms": "Thanh toán trong 7 ngày",
  "notes": "Ghi chú hóa đơn",
  "reference_number": "REF001",
  "items": [
    {
      "product_id": 1, // nullable
      "product_name": "Sản phẩm A",
      "product_sku": "SP001",
      "product_description": "Mô tả sản phẩm",
      "quantity": 2,
      "unit": "cái",
      "unit_price": 500000.00,
      "discount_rate": 5, // % or null
      "discount_amount": 25000.00, // or null
      "tax_rate": 10, // % or null
      "notes": "Ghi chú sản phẩm"
    }
  ]
}
```

#### **PUT /invoices/{id}**
Cập nhật hóa đơn (Requires Authentication)

#### **DELETE /invoices/{id}**
Xóa hóa đơn (Requires Authentication)

#### **GET /invoices/statistics**
Lấy thống kê hóa đơn (Requires Authentication)

**Query Parameters:**
- `date_from`: From date
- `date_to`: To date  
- `branch_shop_id`: Branch shop filter

**Response:**
```json
{
  "status": "success",
  "message": "Statistics retrieved successfully",
  "data": {
    "total_invoices": 100,
    "total_amount": 50000000.00,
    "paid_amount": 30000000.00,
    "outstanding_amount": 20000000.00,
    "by_status": {
      "draft": {"count": 10, "total": 5000000.00},
      "sent": {"count": 20, "total": 10000000.00},
      "completed": {"count": 70, "total": 35000000.00}
    },
    "by_payment_status": {
      "unpaid": {"count": 30, "total": 15000000.00},
      "paid": {"count": 60, "total": 30000000.00},
      "partial": {"count": 10, "total": 5000000.00}
    }
  }
}
```

### **3. System Endpoints**

#### **GET /health**
Health check endpoint (No Authentication Required)

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-08-07T02:38:05.377936Z",
  "version": "1.0.0",
  "environment": "local",
  "checks": {
    "database": {"status": "healthy", "message": "Database connection successful"},
    "cache": {"status": "healthy", "message": "Cache working properly"},
    "storage": {"status": "healthy", "message": "Storage writable"},
    "memory": {"status": "healthy", "usage": "4 MB", "limit": "512M", "percentage": 0.78}
  }
}
```

## 🔐 **Authentication Flow**

### **For Flutter App:**

1. **Login:**
```dart
final response = await http.post(
  Uri.parse('http://yukimart.local/api/v1/auth/login'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'email': 'yukimart@gmail.com',
    'password': '123456',
    'device_name': 'Flutter App'
  }),
);
```

2. **Store Token:**
```dart
final data = jsonDecode(response.body);
final token = data['data']['token'];
// Store token in secure storage
```

3. **Use Token:**
```dart
final response = await http.get(
  Uri.parse('http://yukimart.local/api/v1/auth/profile'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

## 📊 **Error Responses**

### **Validation Error (422):**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["Email là bắt buộc"],
    "password": ["Mật khẩu phải có ít nhất 6 ký tự"]
  }
}
```

### **Authentication Error (401):**
```json
{
  "status": "error",
  "message": "Invalid credentials"
}
```

### **Authorization Error (403):**
```json
{
  "status": "error",
  "message": "Unauthorized access"
}
```

### **Not Found Error (404):**
```json
{
  "status": "error",
  "message": "Resource not found"
}
```

### **Server Error (500):**
```json
{
  "status": "error",
  "message": "Internal server error",
  "error": "Error details"
}
```

## 🎯 **Next Steps**

1. **Implement Customer API** - CRUD operations for customers
2. **Implement Product API** - Product management
3. **Implement Order API** - Order management  
4. **Add File Upload** - Image upload for products/customers
5. **Add Push Notifications** - Real-time updates
6. **Add API Rate Limiting** - Security enhancement
7. **Add API Versioning** - Future compatibility

## 📱 **Flutter Integration Ready**

API đã sẵn sàng cho Flutter App với:
- ✅ RESTful design chuẩn
- ✅ JSON responses consistent
- ✅ Token-based authentication
- ✅ Comprehensive error handling
- ✅ Pagination support
- ✅ Filtering và searching
- ✅ Resource relationships
- ✅ Validation messages tiếng Việt
