# YukiMart API - Flutter Integration Guide

## 🎯 **OVERVIEW**

Hướng dẫn chi tiết để tích hợp YukiMart API vào Flutter mobile application. API đã được thiết kế đặc biệt cho mobile development với standardized responses, comprehensive validation, và offline-ready architecture.

## 📋 **API ENDPOINTS SUMMARY**

### **🔐 Authentication (9 endpoints)**
- `POST /auth/login` - User login
- `POST /auth/register` - User registration
- `POST /auth/logout` - User logout
- `POST /auth/refresh` - Token refresh
- `GET /auth/me` - Get user profile
- `PUT /auth/profile` - Update profile
- `POST /auth/change-password` - Change password
- `POST /auth/forgot-password` - Password reset request
- `POST /auth/reset-password` - Password reset

### **👤 User Management (4 endpoints)**
- `GET /user/profile` - Get detailed profile
- `PUT /user/profile` - Update profile
- `GET /user/permissions` - Get user permissions
- `GET /user/branches` - Get user branches

### **📄 Invoice Management (11 endpoints)**
- `GET /invoices` - List invoices with pagination
- `POST /invoices` - Create new invoice
- `GET /invoices/{id}` - Get invoice details
- `PUT /invoices/{id}` - Update invoice
- `DELETE /invoices/{id}` - Delete invoice
- `GET /invoices/{id}/items` - Get invoice items
- `POST /invoices/{id}/payment` - Process payment
- `PUT /invoices/{id}/status` - Update status
- `GET /invoices/{id}/pdf` - Generate PDF
- `POST /invoices/bulk-update` - Bulk operations
- `GET /invoices/summary` - Invoice summary

### **📦 Order Management (9 endpoints)**
- `GET /orders` - List orders with filters
- `POST /orders` - Create new order
- `GET /orders/{id}` - Get order details
- `PUT /orders/{id}` - Update order
- `DELETE /orders/{id}` - Delete order
- `GET /orders/{id}/items` - Get order items
- `PUT /orders/{id}/status` - Update order status
- `POST /orders/{id}/payment` - Record payment

### **🛍️ Product Catalog (9 endpoints)**
- `GET /products` - List products with filters
- `POST /products` - Create new product
- `GET /products/{id}` - Get product details
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product
- `GET /products/search` - Search products
- `GET /products/barcode/{barcode}` - Find by barcode
- `GET /products/{id}/variants` - Get product variants
- `GET /products/{id}/inventory` - Get inventory info

### **👥 Customer Management (7 endpoints)**
- `GET /customers` - List customers
- `POST /customers` - Create new customer
- `GET /customers/{id}` - Get customer details
- `PUT /customers/{id}` - Update customer
- `DELETE /customers/{id}` - Delete customer
- `GET /customers/search` - Search customers
- `GET /customers/{id}/orders` - Get customer orders
- `GET /customers/{id}/invoices` - Get customer invoices
- `GET /customers/{id}/payments` - Get customer payments

### **💰 Payment Processing (8 endpoints)**
- `GET /payments` - List payments
- `POST /payments` - Create new payment
- `GET /payments/{id}` - Get payment details
- `PUT /payments/{id}` - Update payment
- `DELETE /payments/{id}` - Delete payment
- `GET /payments/summary` - Payment summary
- `POST /payments/bulk-create` - Bulk create payments
- `POST /payments/{id}/approve` - Approve payment

### **🔧 Utilities (8 endpoints)**
- `GET /utils/branches` - Get branches
- `GET /utils/payment-methods` - Get payment methods
- `GET /health` - Health check
- `GET /status` - Status check
- `GET /docs` - Interactive documentation
- `GET /docs/openapi` - OpenAPI specification
- `POST /docs/postman/sync` - Sync to Postman
- `GET /docs/info` - API information

## 🚀 **FLUTTER SETUP**

### **1. Dependencies**
Add to `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP client
  dio: ^5.3.2
  
  # State management
  flutter_bloc: ^8.1.3
  
  # Local storage
  shared_preferences: ^2.2.2
  sqflite: ^2.3.0
  
  # JSON serialization
  json_annotation: ^4.8.1
  
  # Secure storage
  flutter_secure_storage: ^9.0.0
  
  # Connectivity
  connectivity_plus: ^5.0.1

dev_dependencies:
  # Code generation
  build_runner: ^2.4.7
  json_serializable: ^6.7.1
```

### **2. API Client Setup**

```dart
// lib/services/api_client.dart
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  static const String baseUrl = 'http://yukimart.local/api/v1';
  
  late Dio _dio;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  
  ApiClient() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));
    
    _setupInterceptors();
  }
  
  void _setupInterceptors() {
    // Request interceptor for authentication
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _storage.read(key: 'api_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) async {
        if (error.response?.statusCode == 401) {
          // Token expired, redirect to login
          await _storage.delete(key: 'api_token');
          // Navigate to login screen
        }
        handler.next(error);
      },
    ));
    
    // Logging interceptor
    _dio.interceptors.add(LogInterceptor(
      requestBody: true,
      responseBody: true,
    ));
  }
  
  // Generic request method
  Future<ApiResponse<T>> request<T>(
    String endpoint, {
    String method = 'GET',
    Map<String, dynamic>? data,
    Map<String, dynamic>? queryParameters,
    T Function(Map<String, dynamic>)? fromJson,
  }) async {
    try {
      final response = await _dio.request(
        endpoint,
        data: data,
        queryParameters: queryParameters,
        options: Options(method: method),
      );
      
      return ApiResponse<T>.fromJson(
        response.data,
        fromJson: fromJson,
      );
    } on DioException catch (e) {
      throw ApiException.fromDioError(e);
    }
  }
}
```

### **3. Response Models**

```dart
// lib/models/api_response.dart
import 'package:json_annotation/json_annotation.dart';

part 'api_response.g.dart';

@JsonSerializable(genericArgumentFactories: true)
class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final ApiMeta? meta;
  final Map<String, dynamic>? errors;
  final String? errorCode;
  
  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.meta,
    this.errors,
    this.errorCode,
  });
  
  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Object? json)? fromJsonT,
  ) => _$ApiResponseFromJson(json, fromJsonT);
  
  Map<String, dynamic> toJson(Object Function(T value)? toJsonT) =>
      _$ApiResponseToJson(this, toJsonT);
}

@JsonSerializable()
class ApiMeta {
  final String timestamp;
  final String version;
  final String requestId;
  
  ApiMeta({
    required this.timestamp,
    required this.version,
    required this.requestId,
  });
  
  factory ApiMeta.fromJson(Map<String, dynamic> json) => _$ApiMetaFromJson(json);
  Map<String, dynamic> toJson() => _$ApiMetaToJson(this);
}
```

### **4. Authentication Service**

```dart
// lib/services/auth_service.dart
class AuthService {
  final ApiClient _apiClient;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  
  AuthService(this._apiClient);
  
  Future<User> login(String email, String password) async {
    final response = await _apiClient.request<Map<String, dynamic>>(
      '/auth/login',
      method: 'POST',
      data: {
        'email': email,
        'password': password,
        'device_name': 'Flutter App',
      },
    );
    
    if (response.success && response.data != null) {
      final token = response.data!['token'];
      final user = User.fromJson(response.data!['user']);
      
      await _storage.write(key: 'api_token', value: token);
      await _storage.write(key: 'user_data', value: jsonEncode(user.toJson()));
      
      return user;
    }
    
    throw Exception(response.message);
  }
  
  Future<void> logout() async {
    await _apiClient.request('/auth/logout', method: 'POST');
    await _storage.deleteAll();
  }
  
  Future<User?> getCurrentUser() async {
    final userData = await _storage.read(key: 'user_data');
    if (userData != null) {
      return User.fromJson(jsonDecode(userData));
    }
    return null;
  }
  
  Future<bool> isLoggedIn() async {
    final token = await _storage.read(key: 'api_token');
    return token != null;
  }
}
```

### **5. Product Service Example**

```dart
// lib/services/product_service.dart
class ProductService {
  final ApiClient _apiClient;
  
  ProductService(this._apiClient);
  
  Future<List<Product>> getProducts({
    int page = 1,
    int perPage = 20,
    String? search,
    int? categoryId,
    bool? inStock,
  }) async {
    final response = await _apiClient.request<Map<String, dynamic>>(
      '/products',
      queryParameters: {
        'page': page,
        'per_page': perPage,
        if (search != null) 'search': search,
        if (categoryId != null) 'category_id': categoryId,
        if (inStock != null) 'in_stock': inStock,
      },
    );
    
    if (response.success && response.data != null) {
      final List<dynamic> productsJson = response.data!['data'];
      return productsJson.map((json) => Product.fromJson(json)).toList();
    }
    
    return [];
  }
  
  Future<Product?> findProductByBarcode(String barcode) async {
    final response = await _apiClient.request<Map<String, dynamic>>(
      '/products/barcode/$barcode',
    );
    
    if (response.success && response.data != null) {
      if (response.data!['type'] == 'product') {
        return Product.fromJson(response.data!['product']);
      }
      // Handle variant case
      return Product.fromJson(response.data!['product']);
    }
    
    return null;
  }
  
  Future<List<Product>> searchProducts(String query) async {
    final response = await _apiClient.request<List<dynamic>>(
      '/products/search',
      queryParameters: {'q': query, 'limit': 10},
    );
    
    if (response.success && response.data != null) {
      return response.data!.map((json) => Product.fromJson(json)).toList();
    }
    
    return [];
  }
}
```

## 🔄 **STATE MANAGEMENT WITH BLOC**

### **Authentication BLoC**

```dart
// lib/blocs/auth/auth_bloc.dart
class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final AuthService _authService;
  
  AuthBloc(this._authService) : super(AuthInitial()) {
    on<AuthLoginRequested>(_onLoginRequested);
    on<AuthLogoutRequested>(_onLogoutRequested);
    on<AuthStatusChecked>(_onAuthStatusChecked);
  }
  
  Future<void> _onLoginRequested(
    AuthLoginRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    
    try {
      final user = await _authService.login(event.email, event.password);
      emit(AuthAuthenticated(user));
    } catch (e) {
      emit(AuthError(e.toString()));
    }
  }
  
  Future<void> _onLogoutRequested(
    AuthLogoutRequested event,
    Emitter<AuthState> emit,
  ) async {
    await _authService.logout();
    emit(AuthUnauthenticated());
  }
  
  Future<void> _onAuthStatusChecked(
    AuthStatusChecked event,
    Emitter<AuthState> emit,
  ) async {
    final isLoggedIn = await _authService.isLoggedIn();
    if (isLoggedIn) {
      final user = await _authService.getCurrentUser();
      if (user != null) {
        emit(AuthAuthenticated(user));
      } else {
        emit(AuthUnauthenticated());
      }
    } else {
      emit(AuthUnauthenticated());
    }
  }
}
```

## 📱 **UI IMPLEMENTATION EXAMPLES**

### **Login Screen**

```dart
// lib/screens/auth/login_screen.dart
class LoginScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: BlocConsumer<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is AuthAuthenticated) {
            Navigator.pushReplacementNamed(context, '/home');
          } else if (state is AuthError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(state.message)),
            );
          }
        },
        builder: (context, state) {
          return LoginForm(
            isLoading: state is AuthLoading,
            onLogin: (email, password) {
              context.read<AuthBloc>().add(
                AuthLoginRequested(email, password),
              );
            },
          );
        },
      ),
    );
  }
}
```

### **Product List with Barcode Scanner**

```dart
// lib/screens/products/product_list_screen.dart
class ProductListScreen extends StatefulWidget {
  @override
  _ProductListScreenState createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final ProductService _productService = GetIt.instance<ProductService>();
  List<Product> _products = [];
  bool _isLoading = false;
  
  @override
  void initState() {
    super.initState();
    _loadProducts();
  }
  
  Future<void> _loadProducts() async {
    setState(() => _isLoading = true);
    try {
      final products = await _productService.getProducts();
      setState(() => _products = products);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading products: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }
  
  Future<void> _scanBarcode() async {
    // Implement barcode scanning
    final barcode = await BarcodeScanner.scan();
    if (barcode.isNotEmpty) {
      final product = await _productService.findProductByBarcode(barcode);
      if (product != null) {
        // Navigate to product detail or add to cart
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => ProductDetailScreen(product: product),
          ),
        );
      }
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Products'),
        actions: [
          IconButton(
            icon: Icon(Icons.qr_code_scanner),
            onPressed: _scanBarcode,
          ),
        ],
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: _products.length,
              itemBuilder: (context, index) {
                final product = _products[index];
                return ProductListTile(
                  product: product,
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => ProductDetailScreen(product: product),
                    ),
                  ),
                );
              },
            ),
    );
  }
}
```

## 🔄 **OFFLINE SUPPORT**

### **Local Database Setup**

```dart
// lib/database/app_database.dart
class AppDatabase {
  static Database? _database;
  
  static Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }
  
  static Future<Database> _initDatabase() async {
    final path = join(await getDatabasesPath(), 'yukimart.db');
    
    return await openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        // Create tables for offline storage
        await db.execute('''
          CREATE TABLE products(
            id INTEGER PRIMARY KEY,
            product_name TEXT,
            sku TEXT,
            barcode TEXT,
            sale_price REAL,
            stock_quantity INTEGER,
            data TEXT,
            last_sync INTEGER
          )
        ''');
        
        await db.execute('''
          CREATE TABLE customers(
            id INTEGER PRIMARY KEY,
            name TEXT,
            phone TEXT,
            email TEXT,
            data TEXT,
            last_sync INTEGER
          )
        ''');
        
        // Add more tables as needed
      },
    );
  }
}
```

## 🎯 **BEST PRACTICES**

### **1. Error Handling**
- Always wrap API calls in try-catch blocks
- Show user-friendly error messages
- Implement retry mechanisms for network failures
- Log errors for debugging

### **2. Performance**
- Use pagination for large datasets
- Implement lazy loading for images
- Cache frequently accessed data
- Use connection pooling

### **3. Security**
- Store tokens securely using FlutterSecureStorage
- Implement certificate pinning
- Validate all user inputs
- Use HTTPS only

### **4. User Experience**
- Show loading indicators
- Implement pull-to-refresh
- Provide offline functionality
- Use optimistic updates

## 🔗 **USEFUL RESOURCES**

- **API Documentation**: http://yukimart.local/api/v1/docs
- **OpenAPI Spec**: http://yukimart.local/api/v1/docs/openapi
- **Postman Collection**: https://www.postman.com/collections/4968736-bea65acc-62a1-422c-8997-5f654cb18517
- **Health Check**: http://yukimart.local/api/v1/health

## 🏆 **CONCLUSION**

YukiMart API được thiết kế đặc biệt cho Flutter mobile development với:
- ✅ **Standardized responses** dễ parse
- ✅ **Comprehensive validation** giảm client-side errors
- ✅ **Offline-ready architecture** với consistent data structure
- ✅ **Mobile-optimized endpoints** như barcode scanning
- ✅ **Production-ready security** với Bearer token authentication

**Ready to build amazing mobile experience!** 🚀
