# Product Category API Documentation

## 🎯 **API OVERVIEW**

**Base URL**: `/api/v1/product-categories`  
**Authentication**: Required (Bearer Token)  
**Content-Type**: `application/json`

## 📋 **ENDPOINTS**

### **1. GET** `/api/v1/product-categories`
**Description**: Get list of product categories with various formats and filters

**Query Parameters:**
- `parent_id` (optional): Filter by parent category ID (`null` or `0` for root categories)
- `is_active` (optional): Filter by active status (`true`/`false`)
- `show_in_menu` (optional): Filter by menu visibility (`true`/`false`)
- `show_on_homepage` (optional): Filter by homepage visibility (`true`/`false`)
- `search` (optional): Search in name, description, or slug
- `sort_by` (optional): Sort field (`name`, `sort_order`, `created_at`, `updated_at`)
- `sort_direction` (optional): Sort direction (`asc`, `desc`)
- `format` (optional): Response format (`paginated`, `tree`, `flat`)
- `per_page` (optional): Items per page for paginated format (default: 15)

**Response Formats:**

#### **Paginated Format (default):**
```json
{
  "status": "success",
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Điện tử",
      "slug": "dien-tu",
      "description": "Sản phẩm điện tử",
      "image": null,
      "icon": "fas fa-laptop",
      "color": "#007bff",
      "parent_id": null,
      "sort_order": 1,
      "is_active": true,
      "show_in_menu": true,
      "show_on_homepage": true,
      "meta_title": "Điện tử",
      "meta_description": "Danh mục sản phẩm điện tử",
      "meta_keywords": "điện tử, laptop, điện thoại",
      "created_at": "2025-08-09T12:00:00.000000Z",
      "updated_at": "2025-08-09T12:00:00.000000Z",
      "parent": null,
      "children_count": 3,
      "products_count": 25,
      "is_root": true,
      "is_leaf": false,
      "has_children": true,
      "level": 0,
      "breadcrumb": "Điện tử"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67,
    "from": 1,
    "to": 15
  }
}
```

#### **Tree Format:**
```json
{
  "status": "success",
  "message": "Categories tree retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Điện tử",
      "slug": "dien-tu",
      "children": [
        {
          "id": 2,
          "name": "Laptop",
          "slug": "laptop",
          "children": []
        }
      ]
    }
  ]
}
```

#### **Flat Format:**
```json
{
  "status": "success",
  "message": "Categories retrieved successfully",
  "data": [...],
  "total": 67
}
```

### **2. POST** `/api/v1/product-categories`
**Description**: Create a new product category

**Request Body:**
```json
{
  "name": "Điện thoại",
  "slug": "dien-thoai",
  "description": "Danh mục điện thoại di động",
  "image": "categories/phone.jpg",
  "icon": "fas fa-mobile-alt",
  "color": "#28a745",
  "parent_id": 1,
  "sort_order": 1,
  "is_active": true,
  "show_in_menu": true,
  "show_on_homepage": false,
  "meta_title": "Điện thoại di động",
  "meta_description": "Danh mục điện thoại di động chính hãng",
  "meta_keywords": "điện thoại, smartphone, mobile"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `slug`: nullable, string, max:255, unique
- `description`: nullable, string
- `image`: nullable, string, max:500
- `icon`: nullable, string, max:100
- `color`: nullable, string, max:7
- `parent_id`: nullable, exists:product_categories,id
- `sort_order`: nullable, integer, min:0
- `is_active`: nullable, boolean
- `show_in_menu`: nullable, boolean
- `show_on_homepage`: nullable, boolean
- `meta_title`: nullable, string, max:255
- `meta_description`: nullable, string, max:500
- `meta_keywords`: nullable, string, max:500

**Response:**
```json
{
  "status": "success",
  "message": "Category created successfully",
  "data": {
    "id": 15,
    "name": "Điện thoại",
    "slug": "dien-thoai",
    // ... full category data
  }
}
```

### **3. GET** `/api/v1/product-categories/{id}`
**Description**: Get specific category details with products

**Response:**
```json
{
  "status": "success",
  "message": "Category retrieved successfully",
  "data": {
    "id": 1,
    "name": "Điện tử",
    // ... full category data
    "products": [
      {
        "id": 1,
        "product_name": "iPhone 15",
        "sku": "IP15-001",
        "product_status": "active",
        "sale_price": 25000000.00
      }
    ]
  }
}
```

### **4. PUT** `/api/v1/product-categories/{id}`
**Description**: Update existing category

**Request Body**: Same as POST (all fields optional)

**Additional Validation:**
- Cannot set parent to self
- Cannot set parent to descendant

**Response:**
```json
{
  "status": "success",
  "message": "Category updated successfully",
  "data": {
    // ... updated category data
  }
}
```

### **5. DELETE** `/api/v1/product-categories/{id}`
**Description**: Delete category (with safety checks)

**Safety Checks:**
- Cannot delete if category has products
- Cannot delete if category has children

**Response:**
```json
{
  "status": "success",
  "message": "Category deleted successfully"
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Cannot delete category. It has 25 products assigned to it."
}
```

### **6. GET** `/api/v1/product-categories/tree-options`
**Description**: Get tree options for select dropdowns

**Query Parameters:**
- `selected_id` (optional): ID of selected category
- `exclude_id` (optional): ID of category to exclude

**Response:**
```json
{
  "status": "success",
  "message": "Category tree options retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Điện tử",
      "level": 0,
      "selected": false
    },
    {
      "id": 2,
      "name": "— Laptop",
      "level": 1,
      "selected": true
    },
    {
      "id": 3,
      "name": "—— Gaming Laptop",
      "level": 2,
      "selected": false
    }
  ]
}
```

### **7. GET** `/api/v1/product-categories/menu-tree`
**Description**: Get menu tree for navigation (only active + show_in_menu categories)

**Response:**
```json
{
  "status": "success",
  "message": "Menu tree retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Điện tử",
      "slug": "dien-tu",
      "show_in_menu": true,
      "children": [
        {
          "id": 2,
          "name": "Laptop",
          "slug": "laptop",
          "children": []
        }
      ]
    }
  ]
}
```

### **8. GET** `/api/v1/product-categories/stats`
**Description**: Get category statistics

**Response:**
```json
{
  "status": "success",
  "message": "Category statistics retrieved successfully",
  "data": {
    "total_categories": 67,
    "active_categories": 58,
    "root_categories": 12,
    "menu_categories": 45,
    "homepage_categories": 8,
    "categories_with_products": 42,
    "empty_categories": 25
  }
}
```

## 🔧 **IMPLEMENTATION DETAILS**

### **Controller**: `App\Http\Controllers\Api\V1\ProductCategoryController`

### **Model**: `App\Models\ProductCategory`

**Key Features:**
- Hierarchical structure (parent-child relationships)
- Soft deletes support
- Automatic slug generation
- SEO meta fields
- Menu and homepage visibility controls
- Sort ordering
- Product relationships

### **Database Table**: `product_categories`

**Key Fields:**
- `id`: Primary key
- `name`: Category name
- `slug`: URL-friendly identifier
- `description`: Category description
- `image`: Category image path
- `icon`: FontAwesome icon class
- `color`: Hex color code
- `parent_id`: Parent category ID (nullable)
- `sort_order`: Display order
- `is_active`: Active status
- `show_in_menu`: Menu visibility
- `show_on_homepage`: Homepage visibility
- `meta_title`, `meta_description`, `meta_keywords`: SEO fields

## 📱 **MOBILE APP INTEGRATION**

### **Flutter Service Example:**

```dart
class ProductCategoryService {
  static const String baseUrl = 'https://api.yukimart.com/api/v1';
  
  static Future<List<ProductCategory>> getCategories({
    String format = 'paginated',
    int? parentId,
    bool? isActive,
    String? search,
    int page = 1,
    int perPage = 15,
  }) async {
    final queryParams = <String, dynamic>{
      'format': format,
      'page': page,
      'per_page': perPage,
    };
    
    if (parentId != null) queryParams['parent_id'] = parentId;
    if (isActive != null) queryParams['is_active'] = isActive;
    if (search != null) queryParams['search'] = search;
    
    final response = await http.get(
      Uri.parse('$baseUrl/product-categories').replace(queryParameters: queryParams),
      headers: ApiService.headers,
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((json) => ProductCategory.fromJson(json))
          .toList();
    }
    throw Exception('Failed to load categories');
  }
  
  static Future<List<ProductCategory>> getCategoryTree() async {
    final response = await http.get(
      Uri.parse('$baseUrl/product-categories?format=tree'),
      headers: ApiService.headers,
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((json) => ProductCategory.fromJson(json))
          .toList();
    }
    throw Exception('Failed to load category tree');
  }
  
  static Future<CategoryStats> getStats() async {
    final response = await http.get(
      Uri.parse('$baseUrl/product-categories/stats'),
      headers: ApiService.headers,
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return CategoryStats.fromJson(data['data']);
    }
    throw Exception('Failed to load category statistics');
  }
}
```

## 🚀 **DEPLOYMENT STATUS**

### ✅ **Completed:**
- **✅ Full CRUD Operations**: Create, Read, Update, Delete
- **✅ Hierarchical Structure**: Parent-child relationships
- **✅ Multiple Response Formats**: Paginated, Tree, Flat
- **✅ Advanced Filtering**: Search, status, visibility filters
- **✅ Tree Operations**: Tree options, menu tree
- **✅ Statistics**: Comprehensive category stats
- **✅ Validation**: Complete input validation
- **✅ Safety Checks**: Prevent deletion with products/children
- **✅ SEO Support**: Meta fields for SEO optimization
- **✅ API Documentation**: Complete endpoint documentation

### ✅ **Ready for:**
- **📱 Mobile App Integration**: Flutter service examples provided
- **🌐 Frontend Integration**: React/Vue.js components
- **📊 Admin Dashboard**: Category management interface
- **🔍 Search Integration**: Category-based product filtering
- **📋 Menu Systems**: Dynamic navigation menus

---

**🎉 Product Category API provides comprehensive category management with hierarchical structure, advanced filtering, and multiple response formats!** ✅

**Base URL**: `/api/v1/product-categories`  
**Authentication**: Bearer Token Required  
**Features**: CRUD, Tree Structure, Statistics, Search, Multiple Formats  
**Status**: ✅ Ready for Production
