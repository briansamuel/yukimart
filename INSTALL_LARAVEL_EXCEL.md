# 📦 Cài đặt Laravel Excel Package

## Cài đặt package

Chạy lệnh sau để cài đặt Laravel Excel:

```bash
composer require maatwebsite/excel
```

## Publish config (tùy chọn)

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

## Sử dụng

Package này sẽ được sử dụng để:
- Đọc file Excel (.xlsx, .xls)
- Đọc file CSV
- Parse dữ liệu từ file
- Validate dữ liệu import

## Documentation

Xem thêm tại: https://docs.laravel-excel.com/
