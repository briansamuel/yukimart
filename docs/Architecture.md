# YukiMart - System Architecture

## 📋 Tổng quan kiến trúc

YukiMart được thiết kế theo kiến trúc MVC (Model-View-Controller) của Laravel với các layer bổ sung để đảm bảo tính mở rộng và bảo trì.

## 🏗️ Kiến trúc tổng thể

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                       │
├─────────────────────────────────────────────────────────────┤
│  Web Interface  │  API Endpoints  │  Admin Dashboard       │
│  (Blade Views)  │  (JSON/REST)    │  (Metronic Theme)      │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                        │
├─────────────────────────────────────────────────────────────┤
│  Controllers    │  Middleware     │  Form Requests         │
│  Services       │  Jobs/Queues    │  Events/Listeners      │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    BUSINESS LOGIC LAYER                     │
├─────────────────────────────────────────────────────────────┤
│  Models         │  Repositories   │  Business Rules        │
│  Relationships  │  Observers      │  Validation Logic      │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    DATA ACCESS LAYER                        │
├─────────────────────────────────────────────────────────────┤
│  Database       │  Cache          │  File Storage          │
│  (MySQL)        │  (Redis)        │  (Local/Cloud)         │
└─────────────────────────────────────────────────────────────┘
```

## 🗂️ Cấu trúc thư mục

### Backend Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/CMS/          # Admin controllers
│   │   ├── API/                # API controllers
│   │   └── Auth/               # Authentication
│   ├── Middleware/             # Custom middleware
│   ├── Requests/               # Form validation
│   └── Resources/              # API resources
├── Models/                     # Eloquent models
├── Services/                   # Business logic services
├── Repositories/               # Data access layer
├── Observers/                  # Model observers
├── Events/                     # Event classes
├── Listeners/                  # Event listeners
├── Jobs/                       # Queue jobs
└── Providers/                  # Service providers
```

### Frontend Structure
```
resources/
├── views/
│   ├── admin/                  # Admin interface
│   │   ├── layouts/            # Layout templates
│   │   ├── components/         # Reusable components
│   │   ├── products/           # Product management
│   │   ├── orders/             # Order management
│   │   ├── invoices/           # Invoice management
│   │   └── customers/          # Customer management
│   └── auth/                   # Authentication views
├── js/                         # JavaScript files
├── css/                        # CSS files
└── lang/                       # Localization files
```

### Public Assets
```
public/
├── admin-assets/               # Admin theme assets
│   ├── js/                     # JavaScript files
│   ├── css/                    # CSS files
│   └── media/                  # Images, fonts
├── uploads/                    # User uploaded files
└── tests/                      # Debug/test files
```

## 🔧 Core Components

### 1. Authentication & Authorization
- **Laravel Sanctum**: API authentication
- **Role-based Access Control**: Custom roles & permissions
- **Multi-guard Authentication**: Admin & User guards
- **Session Management**: Secure session handling

### 2. Database Design
- **Normalized Structure**: Proper relationships
- **Soft Deletes**: Data integrity
- **Timestamps**: Audit trail
- **Indexes**: Performance optimization

### 3. Caching Strategy
- **Redis Cache**: Session, application cache
- **Query Caching**: Database query optimization
- **View Caching**: Template compilation cache
- **Route Caching**: Route resolution cache

### 4. Queue System
- **Redis Queue**: Background job processing
- **Email Queue**: Asynchronous email sending
- **Notification Queue**: Real-time notifications
- **File Processing**: Image optimization, imports

## 🔄 Data Flow

### 1. Request Lifecycle
```
User Request → Route → Middleware → Controller → Service → Repository → Model → Database
                ↓
Response ← View ← Controller ← Service ← Repository ← Model ← Database
```

### 2. API Request Flow
```
API Request → Route → API Middleware → Controller → Service → Repository → Model
                ↓
JSON Response ← Resource ← Controller ← Service ← Repository ← Model
```

## 🗄️ Database Architecture

### Core Tables
- **users**: User accounts and authentication
- **roles**: Role definitions
- **permissions**: Permission definitions
- **user_roles**: User-role relationships
- **branch_shops**: Branch/store locations
- **user_branch_shop**: User-branch assignments

### Product Management
- **categories**: Product categories
- **products**: Main product information
- **product_variants**: Product variations
- **inventories**: Stock management
- **inventory_transactions**: Stock movement history

### Sales & Orders
- **customers**: Customer information
- **orders**: Order management
- **order_items**: Order line items
- **invoices**: Invoice management
- **invoice_items**: Invoice line items
- **payments**: Payment transactions

### System Tables
- **notifications**: System notifications
- **audit_logs**: Activity tracking
- **file_uploads**: File management
- **settings**: System configuration

## 🔐 Security Architecture

### 1. Authentication Security
- **Password Hashing**: Bcrypt encryption
- **CSRF Protection**: Token-based protection
- **Rate Limiting**: API and login rate limits
- **Session Security**: Secure session configuration

### 2. Data Protection
- **Input Validation**: Form request validation
- **SQL Injection Prevention**: Eloquent ORM protection
- **XSS Prevention**: Blade template escaping
- **File Upload Security**: Type and size validation

### 3. API Security
- **Token Authentication**: Sanctum tokens
- **CORS Configuration**: Cross-origin protection
- **API Rate Limiting**: Request throttling
- **Input Sanitization**: Data cleaning

## 🚀 Performance Optimization

### 1. Database Optimization
- **Query Optimization**: Efficient queries
- **Eager Loading**: N+1 query prevention
- **Database Indexing**: Strategic indexes
- **Connection Pooling**: Connection management

### 2. Caching Strategy
- **Application Cache**: Frequently accessed data
- **Query Cache**: Database query results
- **View Cache**: Compiled templates
- **Asset Optimization**: CSS/JS minification

### 3. Frontend Performance
- **Asset Bundling**: Combined CSS/JS files
- **Image Optimization**: Compressed images
- **Lazy Loading**: On-demand content loading
- **CDN Integration**: Static asset delivery

## 🔧 Development Environment

### Docker Configuration
```yaml
services:
  app:
    image: php:8.3-fpm
    volumes:
      - ./:/var/www/html
  
  database:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: yukimart
  
  redis:
    image: redis:alpine
  
  nginx:
    image: nginx:alpine
    ports:
      - "8083:80"
```

### Environment Variables
```env
APP_NAME=YukiMart
APP_ENV=local
APP_DEBUG=true
APP_URL=http://yukimart.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yukimart

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
BROADCAST_DRIVER=log
```

## 📊 Monitoring & Logging

### 1. Application Monitoring
- **Laravel Telescope**: Development debugging
- **Log Management**: Structured logging
- **Error Tracking**: Exception monitoring
- **Performance Metrics**: Response time tracking

### 2. System Monitoring
- **Database Monitoring**: Query performance
- **Cache Monitoring**: Hit/miss ratios
- **Queue Monitoring**: Job processing
- **Storage Monitoring**: Disk usage

## 🔄 Deployment Architecture

### Production Environment
```
Load Balancer → Web Servers → Application Servers → Database Cluster
                    ↓              ↓                    ↓
                File Storage   Cache Cluster      Backup Systems
```

### Staging Environment
- **Mirror Production**: Same configuration
- **Testing Environment**: Feature testing
- **CI/CD Pipeline**: Automated deployment
- **Database Seeding**: Test data management

---

**Last Updated**: January 2025  
**Version**: 1.0.0  
**Maintainer**: Development Team
