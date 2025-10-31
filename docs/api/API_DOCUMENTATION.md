# 🔌 YUKIMART API DOCUMENTATION

## **YukiMart Multi-Tenant API Reference**

**Version**: 1.0  
**Base URL**: `https://{tenant}.yukimart.com/api`  
**Authentication**: Session-based  
**Content-Type**: `application/json`  

---

## 🌐 **API OVERVIEW**

YukiMart API cung cấp RESTful endpoints để truy cập dữ liệu tenant-specific. Mỗi tenant có API riêng biệt thông qua subdomain.

### **Tenant-Specific URLs:**
- **TechMart**: `https://tenant1.yukimart.local/api`
- **Fashion**: `https://tenant2.yukimart.local/api`
- **FoodBev**: `https://tenant3.yukimart.local/api`
- **HelloMart**: `https://hellomart.yukimart.local/api`
- **BiboMart**: `https://bibomart.yukimart.local/api`

### **Authentication:**
API sử dụng session-based authentication. User phải đăng nhập qua web interface trước khi gọi API.

---

## 🏢 **TENANT ENDPOINTS**

### **GET /api/tenant/info**
Lấy thông tin tenant hiện tại.

**Request:**
```http
GET /api/tenant/info
Host: tenant1.yukimart.local
```

**Response:**
```json
{
  "success": true,
  "tenant": {
    "id": 1,
    "name": "TechMart Store",
    "slug": "techmart",
    "subdomain": "tenant1",
    "status": "active",
    "current_users": 5,
    "current_branch_shops": 4,
    "current_products": 50,
    "created_at": "2025-08-11T00:00:00.000000Z",
    "updated_at": "2025-08-11T12:00:00.000000Z"
  }
}
```

### **GET /api/tenant/current**
Lấy context tenant hiện tại với thông tin chi tiết.

**Request:**
```http
GET /api/tenant/current
Host: hellomart.yukimart.local
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tenant": {
      "id": 4,
      "name": "HelloMart Store",
      "slug": "hellomart",
      "subdomain": "hellomart",
      "status": "active",
      "business_type": "general",
      "description": "General merchandise store",
      "contact_email": "info@hellomart.local",
      "contact_phone": "+84 28 1234 5678"
    },
    "statistics": {
      "total_users": 4,
      "total_branches": 4,
      "total_products": 50,
      "total_orders": 0,
      "total_customers": 0
    }
  }
}
```

---

## 👥 **USER ENDPOINTS**

### **GET /api/users**
Lấy danh sách users của tenant.

**Request:**
```http
GET /api/users?page=1&limit=10
Host: tenant1.yukimart.local
Authorization: Session-based
```

**Response:**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "username": "owner_techmart",
        "email": "owner@techmart.local",
        "full_name": "Owner TechMart Store",
        "phone": "0901000001",
        "status": "active",
        "tenant_role": "owner",
        "joined_at": "2025-08-11T00:00:00.000000Z"
      },
      {
        "id": 2,
        "username": "admin_techmart",
        "email": "admin@techmart.local",
        "full_name": "Admin TechMart Store",
        "phone": "0901000002",
        "status": "active",
        "tenant_role": "admin",
        "joined_at": "2025-08-11T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 5,
      "last_page": 1
    }
  }
}
```

### **GET /api/users/{id}**
Lấy thông tin chi tiết một user.

**Request:**
```http
GET /api/users/1
Host: tenant1.yukimart.local
Authorization: Session-based
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "owner_techmart",
      "email": "owner@techmart.local",
      "full_name": "Owner TechMart Store",
      "phone": "0901000001",
      "address": "123 TechMart Store Street, City",
      "status": "active",
      "email_verified_at": "2025-08-11T00:00:00.000000Z",
      "created_at": "2025-08-11T00:00:00.000000Z",
      "tenant_info": {
        "role": "owner",
        "is_active": true,
        "joined_at": "2025-08-11T00:00:00.000000Z"
      }
    }
  }
}
```

---

## 🛍️ **PRODUCT ENDPOINTS**

### **GET /api/products**
Lấy danh sách sản phẩm của tenant.

**Request:**
```http
GET /api/products?page=1&limit=20&search=iPhone
Host: tenant1.yukimart.local
Authorization: Session-based
```

**Query Parameters:**
- `page`: Trang hiện tại (default: 1)
- `limit`: Số items per page (default: 20, max: 100)
- `search`: Tìm kiếm theo tên, SKU, barcode
- `category_id`: Lọc theo category
- `status`: Lọc theo status (active, inactive)
- `featured`: Lọc sản phẩm nổi bật (1, 0)

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": 1,
        "product_name": "iPhone 15 Pro Max 256GB",
        "product_slug": "iphone-15-pro-max-256gb",
        "sku": "TECH001",
        "barcode": "1000000000001",
        "sale_price": 34990000,
        "cost_price": 24493000,
        "product_description": "iPhone 15 Pro Max với công nghệ tiên tiến và thiết kế hiện đại",
        "product_content": "iPhone 15 Pro Max 256GB",
        "product_status": "publish",
        "product_type": "simple",
        "product_feature": 1,
        "weight": 221,
        "language": "vi",
        "created_at": "2025-08-11T00:00:00.000000Z",
        "updated_at": "2025-08-11T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 50,
      "last_page": 3
    }
  }
}
```

### **GET /api/products/{id}**
Lấy thông tin chi tiết một sản phẩm.

**Request:**
```http
GET /api/products/1
Host: tenant1.yukimart.local
Authorization: Session-based
```

**Response:**
```json
{
  "success": true,
  "data": {
    "product": {
      "id": 1,
      "product_name": "iPhone 15 Pro Max 256GB",
      "product_slug": "iphone-15-pro-max-256gb",
      "sku": "TECH001",
      "barcode": "1000000000001",
      "sale_price": 34990000,
      "cost_price": 24493000,
      "product_description": "iPhone 15 Pro Max với công nghệ tiên tiến và thiết kế hiện đại",
      "product_content": "iPhone 15 Pro Max 256GB",
      "product_status": "publish",
      "product_type": "simple",
      "product_feature": 1,
      "weight": 221,
      "language": "vi",
      "category": {
        "id": 1,
        "category_name": "Điện tử & Công nghệ",
        "category_slug": "dien-tu-cong-nghe"
      },
      "inventory": [
        {
          "branch_id": 1,
          "branch_name": "TechMart Store - Chi nhánh 1",
          "quantity": 45,
          "reserved_quantity": 0,
          "available_quantity": 45
        }
      ],
      "created_at": "2025-08-11T00:00:00.000000Z",
      "updated_at": "2025-08-11T00:00:00.000000Z"
    }
  }
}
```

---

## 🏬 **BRANCH ENDPOINTS**

### **GET /api/branches**
Lấy danh sách chi nhánh của tenant.

**Request:**
```http
GET /api/branches
Host: hellomart.yukimart.local
Authorization: Session-based
```

**Response:**
```json
{
  "success": true,
  "data": {
    "branches": [
      {
        "id": 1,
        "name": "HelloMart Store - Chi nhánh 1",
        "code": "HELLOMART01",
        "address": "Địa chỉ chi nhánh 1, Quận 1, TP.HCM",
        "province": "TP.HCM",
        "district": "Quận 1",
        "ward": "Phường 1",
        "phone": "02812345678",
        "email": "branch1@hellomart.local",
        "status": "active",
        "opening_time": "08:00:00",
        "closing_time": "22:00:00",
        "shop_type": "standard",
        "staff_count": 12,
        "created_at": "2025-08-11T00:00:00.000000Z"
      }
    ]
  }
}
```

### **GET /api/branches/{id}**
Lấy thông tin chi tiết một chi nhánh.

**Request:**
```http
GET /api/branches/1
Host: hellomart.yukimart.local
Authorization: Session-based
```

**Response:**
```json
{
  "success": true,
  "data": {
    "branch": {
      "id": 1,
      "name": "HelloMart Store - Chi nhánh 1",
      "code": "HELLOMART01",
      "address": "Địa chỉ chi nhánh 1, Quận 1, TP.HCM",
      "province": "TP.HCM",
      "district": "Quận 1",
      "ward": "Phường 1",
      "phone": "02812345678",
      "email": "branch1@hellomart.local",
      "status": "active",
      "opening_time": "08:00:00",
      "closing_time": "22:00:00",
      "shop_type": "standard",
      "staff_count": 12,
      "description": "Chi nhánh 1 của HelloMart Store",
      "statistics": {
        "total_products": 50,
        "total_inventory": 2500,
        "total_staff": 12,
        "monthly_orders": 0
      },
      "created_at": "2025-08-11T00:00:00.000000Z",
      "updated_at": "2025-08-11T00:00:00.000000Z"
    }
  }
}
```

---

## 🔐 **AUTHENTICATION ENDPOINTS**

### **GET /admin/auth/check**
Kiểm tra trạng thái authentication.

**Request:**
```http
GET /admin/auth/check
Host: tenant1.yukimart.local
```

**Response (Authenticated):**
```json
{
  "authenticated": true,
  "user": {
    "id": 1,
    "email": "owner@techmart.local",
    "full_name": "Owner TechMart Store"
  },
  "tenant": {
    "id": 1,
    "name": "TechMart Store",
    "slug": "techmart"
  },
  "session_tenant_id": 1,
  "session_tenant_role": "owner"
}
```

**Response (Not Authenticated):**
```json
{
  "authenticated": false,
  "user": null,
  "tenant": {
    "id": 1,
    "name": "TechMart Store",
    "slug": "techmart"
  },
  "session_tenant_id": null,
  "session_tenant_role": null
}
```

---

## 📊 **STATISTICS ENDPOINTS**

### **GET /api/statistics/dashboard**
Lấy thống kê cho dashboard.

**Request:**
```http
GET /api/statistics/dashboard
Host: bibomart.yukimart.local
Authorization: Session-based
```

**Response:**
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_users": 4,
      "total_branches": 2,
      "total_products": 50,
      "total_orders_today": 0,
      "total_revenue_today": 0,
      "total_customers": 0
    },
    "recent_activities": [],
    "top_products": [
      {
        "id": 1,
        "product_name": "Bánh quy Oreo Nguyên bản",
        "total_sold": 0,
        "revenue": 0
      }
    ],
    "branch_performance": [
      {
        "branch_id": 1,
        "branch_name": "BiboMart Store - Chi nhánh 1",
        "orders_today": 0,
        "revenue_today": 0
      }
    ]
  }
}
```

---

## ⚠️ **ERROR HANDLING**

### **Error Response Format:**
```json
{
  "success": false,
  "error": {
    "code": "TENANT_NOT_FOUND",
    "message": "Tenant not found for this subdomain",
    "details": "The requested tenant does not exist or is inactive"
  }
}
```

### **Common Error Codes:**
- **TENANT_NOT_FOUND**: Tenant không tồn tại
- **UNAUTHORIZED**: Chưa đăng nhập
- **FORBIDDEN**: Không có quyền truy cập
- **VALIDATION_ERROR**: Dữ liệu không hợp lệ
- **RESOURCE_NOT_FOUND**: Resource không tồn tại
- **INTERNAL_ERROR**: Lỗi server

### **HTTP Status Codes:**
- **200**: Success
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **500**: Internal Server Error

---

## 🔧 **RATE LIMITING**

API có rate limiting để bảo vệ hệ thống:

- **General API**: 100 requests/minute
- **Authentication**: 5 requests/minute
- **Search**: 50 requests/minute

**Rate Limit Headers:**
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1641024000
```

---

## 📝 **EXAMPLES**

### **JavaScript/Fetch Example:**
```javascript
// Get tenant info
fetch('https://tenant1.yukimart.local/api/tenant/info')
  .then(response => response.json())
  .then(data => {
    console.log('Tenant:', data.tenant.name);
  });

// Get products with search
fetch('https://tenant1.yukimart.local/api/products?search=iPhone&limit=10')
  .then(response => response.json())
  .then(data => {
    console.log('Products:', data.data.products);
  });
```

### **cURL Example:**
```bash
# Get tenant info
curl -X GET "https://tenant1.yukimart.local/api/tenant/info" \
  -H "Accept: application/json"

# Get products
curl -X GET "https://tenant1.yukimart.local/api/products?page=1&limit=20" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"
```

---

**📚 API Documentation hoàn thành!**  
**🔗 Base URL**: `https://{tenant}.yukimart.com/api`  
**📞 Support**: api@yukimart.com
