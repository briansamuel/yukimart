# YukiMart API - Video Tutorial Script

## 🎬 **VIDEO SERIES OUTLINE**

### **Episode 1: API Overview & Setup (10 minutes)**
- Introduction to YukiMart API
- Architecture overview
- Authentication setup
- First API call

### **Episode 2: Product Catalog Integration (15 minutes)**
- Product listing with pagination
- Search functionality
- Barcode scanning integration
- Product variants handling

### **Episode 3: Order Management (12 minutes)**
- Creating orders
- Order status management
- Payment processing
- Order history

### **Episode 4: Customer Management (10 minutes)**
- Customer CRUD operations
- Customer search
- Customer history and analytics

### **Episode 5: Advanced Features (15 minutes)**
- Offline support
- Error handling
- Performance optimization
- Best practices

---

## 📹 **EPISODE 1: API OVERVIEW & SETUP**

### **Opening (0:00 - 1:00)**
```
"Chào mừng các bạn đến với series tutorial tích hợp YukiMart API vào Flutter app! 

Trong video này, chúng ta sẽ tìm hiểu:
- Tổng quan về YukiMart API
- Cách setup authentication
- Thực hiện API call đầu tiên

YukiMart API là một RESTful API hoàn chỉnh với 65+ endpoints, được thiết kế đặc biệt cho mobile development."
```

### **API Overview (1:00 - 3:00)**
```
"Hãy cùng xem qua API documentation tại http://yukimart.local/api/v1/docs

[Screen: Swagger UI]

API được tổ chức thành 8 modules chính:
1. Authentication - 9 endpoints cho đăng nhập, đăng ký
2. User Management - 4 endpoints quản lý profile
3. Product Catalog - 9 endpoints quản lý sản phẩm
4. Order Management - 9 endpoints quản lý đơn hàng
5. Customer Management - 7 endpoints quản lý khách hàng
6. Payment Processing - 8 endpoints xử lý thanh toán
7. Invoice Management - 11 endpoints quản lý hóa đơn
8. Utilities - 8 endpoints tiện ích

Tất cả đều có response format chuẩn và comprehensive validation."
```

### **Authentication Setup (3:00 - 6:00)**
```
"Bây giờ chúng ta sẽ setup authentication trong Flutter.

[Screen: VS Code - Flutter project]

Đầu tiên, thêm dependencies vào pubspec.yaml:

dependencies:
  dio: ^5.3.2
  flutter_secure_storage: ^9.0.0
  json_annotation: ^4.8.1

[Code demo: Creating ApiClient class]

class ApiClient {
  static const String baseUrl = 'http://yukimart.local/api/v1';
  late Dio _dio;
  
  ApiClient() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));
    
    _setupInterceptors();
  }
}

Interceptor sẽ tự động thêm Bearer token vào mọi request."
```

### **First API Call (6:00 - 9:00)**
```
"Hãy thực hiện API call đầu tiên - Health Check:

[Code demo: Health check call]

Future<void> checkApiHealth() async {
  try {
    final response = await _dio.get('/health');
    print('API Status: ${response.data['data']['status']}');
    print('Version: ${response.data['data']['version']}');
  } catch (e) {
    print('Error: $e');
  }
}

[Screen: Running app, showing console output]

Tuyệt vời! API đã hoạt động. Bây giờ test login:

Future<String> login(String email, String password) async {
  final response = await _dio.post('/auth/login', data: {
    'email': email,
    'password': password,
    'device_name': 'Flutter App',
  });
  
  return response.data['data']['token'];
}

[Demo: Login flow with real credentials]"
```

### **Closing (9:00 - 10:00)**
```
"Trong video tiếp theo, chúng ta sẽ tích hợp Product Catalog với:
- Product listing
- Search functionality  
- Barcode scanning

Đừng quên subscribe và bấm chuông thông báo để không bỏ lỡ video mới!

Link tài liệu: http://yukimart.local/api/v1/docs
Postman Collection: [link]
Source code: [GitHub link]"
```

---

## 📹 **EPISODE 2: PRODUCT CATALOG INTEGRATION**

### **Opening (0:00 - 1:00)**
```
"Chào mừng trở lại! Trong video này chúng ta sẽ tích hợp Product Catalog với:
- Product listing có pagination
- Search functionality
- Barcode scanning
- Product variants

Đây là những tính năng core của mọi e-commerce app."
```

### **Product Model (1:00 - 3:00)**
```
"Đầu tiên, tạo Product model từ API response:

[Screen: API docs showing Product response]

@JsonSerializable()
class Product {
  final int id;
  final String productName;
  final String? sku;
  final String? barcode;
  final double salePrice;
  final int stockQuantity;
  final String? productThumbnail;
  final bool isActive;
  
  Product({
    required this.id,
    required this.productName,
    this.sku,
    this.barcode,
    required this.salePrice,
    required this.stockQuantity,
    this.productThumbnail,
    required this.isActive,
  });
  
  factory Product.fromJson(Map<String, dynamic> json) => 
      _$ProductFromJson(json);
}

[Code demo: Running build_runner to generate code]"
```

### **Product Service (3:00 - 6:00)**
```
"Tạo ProductService để handle API calls:

class ProductService {
  final ApiClient _apiClient;
  
  Future<List<Product>> getProducts({
    int page = 1,
    String? search,
    int? categoryId,
  }) async {
    final response = await _apiClient.get('/products', queryParameters: {
      'page': page,
      'per_page': 20,
      if (search != null) 'search': search,
      if (categoryId != null) 'category_id': categoryId,
    });
    
    final List<dynamic> data = response.data['data'];
    return data.map((json) => Product.fromJson(json)).toList();
  }
}

[Demo: Testing API call in Postman first]
[Screen: Postman showing products response]"
```

### **Product List UI (6:00 - 10:00)**
```
"Bây giờ tạo UI để hiển thị products:

class ProductListScreen extends StatefulWidget {
  @override
  _ProductListScreenState createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  List<Product> _products = [];
  bool _isLoading = false;
  int _currentPage = 1;
  
  @override
  void initState() {
    super.initState();
    _loadProducts();
  }
  
  Future<void> _loadProducts() async {
    setState(() => _isLoading = true);
    try {
      final products = await ProductService().getProducts(page: _currentPage);
      setState(() => _products.addAll(products));
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }
}

[Demo: Building and running the app]
[Screen: Product list displaying real data]"
```

### **Search Functionality (10:00 - 12:00)**
```
"Thêm search functionality:

TextField(
  decoration: InputDecoration(
    hintText: 'Search products...',
    prefixIcon: Icon(Icons.search),
  ),
  onChanged: (value) {
    _searchProducts(value);
  },
),

Future<void> _searchProducts(String query) async {
  if (query.isEmpty) {
    _loadProducts();
    return;
  }
  
  setState(() => _isLoading = true);
  try {
    final products = await ProductService().getProducts(search: query);
    setState(() {
      _products = products;
      _currentPage = 1;
    });
  } catch (e) {
    // Handle error
  } finally {
    setState(() => _isLoading = false);
  }
}

[Demo: Real-time search working]"
```

### **Barcode Scanning (12:00 - 15:00)**
```
"Tích hợp barcode scanning:

dependencies:
  mobile_scanner: ^3.5.6

FloatingActionButton(
  onPressed: _scanBarcode,
  child: Icon(Icons.qr_code_scanner),
),

Future<void> _scanBarcode() async {
  final result = await Navigator.push(
    context,
    MaterialPageRoute(builder: (context) => BarcodeScannerScreen()),
  );
  
  if (result != null) {
    final product = await ProductService().findByBarcode(result);
    if (product != null) {
      // Navigate to product detail
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => ProductDetailScreen(product: product),
        ),
      );
    }
  }
}

[Demo: Scanning real barcode and finding product]"
```

---

## 📹 **EPISODE 3: ORDER MANAGEMENT**

### **Opening (0:00 - 1:00)**
```
"Trong video này chúng ta sẽ implement Order Management:
- Tạo đơn hàng mới
- Quản lý order status
- Xử lý payment
- Xem order history

Đây là heart của e-commerce system!"
```

### **Order Model & Service (1:00 - 4:00)**
```
"Tạo Order model và OrderService:

@JsonSerializable()
class Order {
  final int id;
  final String orderCode;
  final int? customerId;
  final String status;
  final String deliveryStatus;
  final String paymentStatus;
  final double totalAmount;
  final double finalAmount;
  final List<OrderItem> items;
  
  // Constructor và fromJson...
}

class OrderService {
  Future<Order> createOrder(CreateOrderRequest request) async {
    final response = await _apiClient.post('/orders', data: request.toJson());
    return Order.fromJson(response.data['data']);
  }
  
  Future<List<Order>> getOrders({
    int page = 1,
    String? status,
    int? customerId,
  }) async {
    // Implementation...
  }
}

[Demo: Testing order creation in Postman]"
```

### **Shopping Cart (4:00 - 7:00)**
```
"Implement shopping cart functionality:

class CartService extends ChangeNotifier {
  List<CartItem> _items = [];
  
  void addItem(Product product, int quantity) {
    final existingIndex = _items.indexWhere((item) => item.product.id == product.id);
    
    if (existingIndex >= 0) {
      _items[existingIndex] = _items[existingIndex].copyWith(
        quantity: _items[existingIndex].quantity + quantity,
      );
    } else {
      _items.add(CartItem(product: product, quantity: quantity));
    }
    
    notifyListeners();
  }
  
  double get totalAmount => _items.fold(0, (sum, item) => 
      sum + (item.product.salePrice * item.quantity));
}

[Demo: Adding products to cart, calculating total]"
```

### **Order Creation Flow (7:00 - 10:00)**
```
"Tạo order từ cart:

Future<void> _createOrder() async {
  final cartItems = Provider.of<CartService>(context, listen: false).items;
  
  final orderRequest = CreateOrderRequest(
    customerId: _selectedCustomer?.id,
    items: cartItems.map((item) => OrderItemRequest(
      productId: item.product.id,
      quantity: item.quantity,
      unitPrice: item.product.salePrice,
    )).toList(),
    note: _noteController.text,
  );
  
  try {
    final order = await OrderService().createOrder(orderRequest);
    
    // Clear cart
    Provider.of<CartService>(context, listen: false).clear();
    
    // Navigate to order detail
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (context) => OrderDetailScreen(order: order),
      ),
    );
  } catch (e) {
    // Handle error
  }
}

[Demo: Complete order creation flow]"
```

### **Order Status Management (10:00 - 12:00)**
```
"Update order status:

Future<void> _updateOrderStatus(Order order, String newStatus) async {
  try {
    await OrderService().updateStatus(order.id, newStatus);
    
    setState(() {
      order = order.copyWith(status: newStatus);
    });
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Order status updated to $newStatus')),
    );
  } catch (e) {
    // Handle error
  }
}

[Demo: Updating order status through UI]"
```

---

## 🎯 **PRODUCTION TIPS**

### **Performance Tips**
```
"Một số tips để optimize performance:

1. Sử dụng pagination cho large datasets
2. Implement lazy loading cho images
3. Cache frequently accessed data
4. Sử dụng connection pooling
5. Implement proper error handling với retry logic"
```

### **Security Best Practices**
```
"Security best practices:

1. Store tokens securely với FlutterSecureStorage
2. Implement certificate pinning
3. Validate tất cả user inputs
4. Sử dụng HTTPS only
5. Implement proper session management"
```

### **User Experience**
```
"UX best practices:

1. Show loading indicators
2. Implement pull-to-refresh
3. Provide offline functionality
4. Use optimistic updates
5. Handle network errors gracefully"
```

## 📚 **RESOURCES**

### **Links to Include in Video Descriptions**
```
🔗 API Documentation: http://yukimart.local/api/v1/docs
📮 Postman Collection: https://www.postman.com/collections/[collection-id]
📖 Flutter Integration Guide: [GitHub link]
💻 Source Code: [GitHub repository]
📱 Demo App: [App Store/Play Store links]
```

### **Timestamps for Each Video**
```
Episode 1:
00:00 - Introduction
01:00 - API Overview
03:00 - Authentication Setup
06:00 - First API Call
09:00 - Next Episode Preview

Episode 2:
00:00 - Introduction
01:00 - Product Model
03:00 - Product Service
06:00 - Product List UI
10:00 - Search Functionality
12:00 - Barcode Scanning

Episode 3:
00:00 - Introduction
01:00 - Order Model & Service
04:00 - Shopping Cart
07:00 - Order Creation Flow
10:00 - Order Status Management
```

## 🎬 **PRODUCTION NOTES**

### **Screen Recording Setup**
- Record in 1080p minimum
- Use clear, readable font sizes
- Highlight important code sections
- Show both code and running app

### **Audio Quality**
- Use good microphone
- Record in quiet environment
- Speak clearly and at moderate pace
- Add background music (optional)

### **Editing Guidelines**
- Cut out long pauses
- Add zoom effects for important parts
- Include captions for code sections
- Add intro/outro animations

### **Thumbnail Design**
- Use YukiMart branding colors
- Include episode number
- Show key technology logos (Flutter, API)
- Make text readable on mobile

## 🏆 **SUCCESS METRICS**

### **Video Performance Goals**
- Watch time: >70%
- Engagement rate: >5%
- Subscriber conversion: >2%
- Comments with questions/feedback

### **Learning Outcomes**
After watching the series, viewers should be able to:
- ✅ Setup YukiMart API authentication
- ✅ Implement product catalog with search
- ✅ Create and manage orders
- ✅ Handle customer data
- ✅ Implement offline functionality
- ✅ Deploy production-ready app

**Ready to create amazing tutorial content!** 🎬
