# Hướng Dẫn Tạo Môi Trường YukiMart

## ✅ Đã Hoàn Thành
- [x] Tạo file `.env` với cấu hình cơ bản

## 📋 Các Bước Tiếp Theo

### 1. Tạo APP_KEY
```bash
docker exec -it php83 /bin/sh
cd /var/www/html/yukimart
php artisan key:generate
```

### 2. Cài Đặt Composer Dependencies
```bash
docker exec -it php83 /bin/sh
cd /var/www/html/yukimart
composer install
```

### 3. Cài Đặt NPM Dependencies
```bash
docker exec -it php83 /bin/sh
cd /var/www/html/yukimart
npm install
```

### 4. Tạo Database
```bash
docker exec -it php83 /bin/sh
cd /var/www/html/yukimart
php artisan migrate
```

### 5. Seed Database (Tùy Chọn)
```bash
docker exec -it php83 /bin/sh
cd /var/www/html/yukimart
php artisan db:seed
```

### 6. Build Frontend Assets
```bash
docker exec -it php83 /bin/sh
cd /var/www/html/yukimart
npm run build
```

## 🔧 Cấu Hình Database

Cập nhật file `.env` với thông tin database của bạn:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yukimart
DB_USERNAME=root
DB_PASSWORD=
```

## 🌐 Truy Cập Ứng Dụng

Sau khi hoàn thành tất cả các bước, truy cập:
- **URL**: http://yukimart.local/
- **Admin Panel**: http://yukimart.local/admin

## 📝 Cấu Hình Bổ Sung

### Firebase Configuration (Nếu Cần)
Cập nhật các biến sau trong `.env`:
```
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_PRIVATE_KEY=your_private_key
FIREBASE_CLIENT_EMAIL=your_client_email
FIREBASE_DATABASE_URL=your_database_url
```

### Shopee Integration (Nếu Cần)
```
SHOPEE_PARTNER_ID=your_partner_id
SHOPEE_PARTNER_KEY=your_partner_key
SHOPEE_REDIRECT_URL=http://yukimart.local/callback/shopee
```

## ✨ Hoàn Tất

Khi tất cả các bước đã hoàn thành, ứng dụng YukiMart sẽ sẵn sàng sử dụng!

