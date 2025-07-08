# Hệ thống Đa ngôn ngữ (Multi-language System)

## 1. Tổng quan

### 1.1 Mục tiêu
- Hỗ trợ đa ngôn ngữ cho toàn bộ website YukiMart
- Ngôn ngữ chính: Tiếng Việt (vi)
- Ngôn ngữ phụ: Tiếng Anh (en), Tiếng Nhật (ja)
- Dễ dàng thêm ngôn ngữ mới
- SEO-friendly URLs
- Admin có thể quản lý translations

### 1.2 Phạm vi
- Frontend: Giao diện người dùng
- Admin Panel: Giao diện quản trị
- Database Content: Nội dung động
- Static Content: Labels, messages, validation

## 2. Kiến trúc Hệ thống

### 2.1 Laravel Localization
- Sử dụng Laravel's built-in localization
- Language files trong `resources/lang/`
- Middleware để detect và set locale
- Session/Cookie để lưu language preference

### 2.2 Database Translations
- Bảng riêng cho translatable content
- Polymorphic relationships
- Fallback mechanism về ngôn ngữ mặc định

### 2.3 URL Structure
```
vi.yukimart.com (default)
en.yukimart.com
ja.yukimart.com

hoặc

yukimart.com/vi/ (default)
yukimart.com/en/
yukimart.com/ja/
```

## 3. Database Design

### 3.1 Bảng `languages`
```sql
- id (bigint, primary key)
- code (varchar 5) - vi, en, ja
- name (varchar 100) - Tiếng Việt, English, 日本語
- native_name (varchar 100) - Tiếng Việt, English, 日本語
- flag_icon (varchar 50) - CSS class hoặc image path
- is_active (boolean)
- is_default (boolean)
- sort_order (integer)
- created_at, updated_at
```

### 3.2 Bảng `translations`
```sql
- id (bigint, primary key)
- translatable_type (varchar) - Model class
- translatable_id (bigint) - Model ID
- language_code (varchar 5)
- field_name (varchar 100) - Tên field cần translate
- field_value (longtext) - Giá trị đã translate
- created_at, updated_at
```

### 3.3 Bảng `translation_keys`
```sql
- id (bigint, primary key)
- key (varchar 255) - Translation key (app.welcome)
- group (varchar 100) - Group name (app, validation, etc.)
- description (text) - Mô tả cho translator
- created_at, updated_at
```

### 3.4 Bảng `translation_values`
```sql
- id (bigint, primary key)
- translation_key_id (bigint)
- language_code (varchar 5)
- value (longtext)
- is_approved (boolean) - Đã duyệt chưa
- created_by (bigint) - User ID
- updated_by (bigint)
- created_at, updated_at
```

## 4. Implementation Strategy

### 4.1 Phase 1: Core Infrastructure
1. Database migrations
2. Language models
3. Translation trait cho models
4. Middleware setup
5. Helper functions

### 4.2 Phase 2: Admin Interface
1. Language management
2. Translation management
3. Bulk import/export
4. Translation status tracking

### 4.3 Phase 3: Frontend Integration
1. Language switcher
2. Route localization
3. Content translation
4. Form validation messages

### 4.4 Phase 4: Content Translation
1. Product translations
2. Category translations
3. Page content translations
4. Email templates

## 5. Technical Implementation

### 5.1 Translatable Trait
```php
trait Translatable
{
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
    
    public function translate($field, $locale = null)
    {
        // Implementation
    }
}
```

### 5.2 Language Middleware
```php
class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Detect language from URL, session, or browser
        // Set app locale
        // Store in session
    }
}
```

### 5.3 Translation Helper
```php
function trans_db($key, $locale = null, $fallback = null)
{
    // Get translation from database
    // Fallback to default language
    // Cache results
}
```

## 6. Content Strategy

### 6.1 Static Content (Language Files)
```
resources/lang/vi/
├── app.php (Ứng dụng chung)
├── auth.php (Đăng nhập/đăng ký)
├── validation.php (Validation messages)
├── pagination.php (Phân trang)
├── passwords.php (Quên mật khẩu)
├── admin.php (Admin panel)
├── product.php (Sản phẩm)
├── order.php (Đơn hàng)
├── invoice.php (Hóa đơn)
└── inventory.php (Kho hàng)
```

### 6.2 Dynamic Content (Database)
- Product names, descriptions
- Category names, descriptions
- Page content (About, Contact, etc.)
- Blog posts
- Notifications
- Email templates

## 7. SEO Considerations

### 7.1 URL Structure
- Subdomain approach: `en.yukimart.com`
- Path approach: `yukimart.com/en/`
- Hreflang tags
- Canonical URLs

### 7.2 Meta Tags
- Translated titles, descriptions
- Open Graph tags
- Schema markup

### 7.3 Sitemap
- Separate sitemaps per language
- Cross-language linking

## 8. User Experience

### 8.1 Language Detection
1. URL parameter/subdomain
2. User preference (logged in)
3. Session/Cookie
4. Browser Accept-Language header
5. Default to Vietnamese

### 8.2 Language Switcher
- Dropdown in header
- Flag icons + language names
- Remember user choice
- Redirect to same page in new language

### 8.3 Fallback Strategy
1. Requested language
2. Default language (Vietnamese)
3. English (if available)
4. Original key/content

## 9. Admin Features

### 9.1 Language Management
- Add/edit/delete languages
- Enable/disable languages
- Set default language
- Reorder languages

### 9.2 Translation Management
- View all translation keys
- Edit translations inline
- Bulk import/export (CSV, JSON)
- Translation progress tracking
- Missing translation reports

### 9.3 Content Translation
- Translate products, categories
- Translate static pages
- Translate email templates
- Preview translations

## 10. Performance Optimization

### 10.1 Caching Strategy
- Cache translation files
- Cache database translations
- Redis/Memcached support
- Cache invalidation

### 10.2 Lazy Loading
- Load translations on demand
- Chunk loading for large datasets
- Background translation loading

### 10.3 CDN Integration
- Serve static assets per language
- Edge caching for translations
- Geographic routing

## 11. Development Workflow

### 11.1 Translation Keys
- Consistent naming convention
- Nested keys for organization
- Descriptive key names
- Documentation for context

### 11.2 Code Standards
```php
// Good
__('product.add_to_cart')
trans_db('product.name', 'en')

// Bad
__('Add to Cart')
'Thêm vào giỏ'
```

### 11.3 Testing
- Unit tests for translation functions
- Integration tests for language switching
- Browser tests for UI elements
- Translation completeness tests

## 12. Deployment Considerations

### 12.1 Environment Setup
- Language files deployment
- Database seeding
- Cache warming
- CDN configuration

### 12.2 Monitoring
- Translation coverage metrics
- Performance monitoring
- Error tracking
- User language preferences

## 13. Future Enhancements

### 13.1 Advanced Features
- Right-to-left (RTL) support
- Pluralization rules
- Date/time formatting
- Number formatting
- Currency formatting

### 13.2 AI Integration
- Auto-translation suggestions
- Translation quality scoring
- Context-aware translations
- Machine learning improvements

### 13.3 Collaboration Tools
- Translator dashboard
- Translation approval workflow
- Version control for translations
- Translation memory
