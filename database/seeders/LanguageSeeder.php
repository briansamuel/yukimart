<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\TranslationValue;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌍 Bắt đầu tạo dữ liệu ngôn ngữ...');

        // Create languages
        $this->createLanguages();
        
        // Create basic translation keys
        $this->createTranslationKeys();
        
        // Create translation values
        $this->createTranslationValues();

        $this->command->info('✅ Hoàn thành tạo dữ liệu ngôn ngữ!');
    }

    /**
     * Create languages.
     */
    private function createLanguages()
    {
        $languages = [
            [
                'code' => 'vi',
                'name' => 'Tiếng Việt',
                'native_name' => 'Tiếng Việt',
                'flag_icon' => 'flag-icon flag-icon-vn',
                'is_active' => true,
                'is_default' => true,
                'is_rtl' => false,
                'sort_order' => 1,
                'currency_code' => 'VND',
                'date_format' => [
                    'short' => 'd/m/Y',
                    'medium' => 'd/m/Y H:i',
                    'long' => 'd/m/Y H:i:s',
                    'full' => 'l, d/m/Y H:i:s'
                ],
                'number_format' => [
                    'decimal_separator' => ',',
                    'thousands_separator' => '.',
                    'currency_position' => 'after'
                ]
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag_icon' => 'flag-icon flag-icon-us',
                'is_active' => true,
                'is_default' => false,
                'is_rtl' => false,
                'sort_order' => 2,
                'currency_code' => 'USD',
                'date_format' => [
                    'short' => 'm/d/Y',
                    'medium' => 'm/d/Y H:i',
                    'long' => 'm/d/Y H:i:s',
                    'full' => 'l, m/d/Y H:i:s'
                ],
                'number_format' => [
                    'decimal_separator' => '.',
                    'thousands_separator' => ',',
                    'currency_position' => 'before'
                ]
            ],
            [
                'code' => 'ja',
                'name' => 'Japanese',
                'native_name' => '日本語',
                'flag_icon' => 'flag-icon flag-icon-jp',
                'is_active' => true,
                'is_default' => false,
                'is_rtl' => false,
                'sort_order' => 3,
                'currency_code' => 'JPY',
                'date_format' => [
                    'short' => 'Y/m/d',
                    'medium' => 'Y/m/d H:i',
                    'long' => 'Y/m/d H:i:s',
                    'full' => 'l, Y/m/d H:i:s'
                ],
                'number_format' => [
                    'decimal_separator' => '.',
                    'thousands_separator' => ',',
                    'currency_position' => 'before'
                ]
            ]
        ];

        foreach ($languages as $languageData) {
            Language::updateOrCreate(
                ['code' => $languageData['code']],
                $languageData
            );
            
            $this->command->info("✅ Tạo ngôn ngữ: {$languageData['native_name']} ({$languageData['code']})");
        }
    }

    /**
     * Create basic translation keys.
     */
    private function createTranslationKeys()
    {
        $translationKeys = [
            // App general
            ['key' => 'app.name', 'group' => 'app', 'description' => 'Tên ứng dụng'],
            ['key' => 'app.welcome', 'group' => 'app', 'description' => 'Lời chào mừng'],
            ['key' => 'app.home', 'group' => 'app', 'description' => 'Trang chủ'],
            ['key' => 'app.about', 'group' => 'app', 'description' => 'Giới thiệu'],
            ['key' => 'app.contact', 'group' => 'app', 'description' => 'Liên hệ'],
            ['key' => 'app.search', 'group' => 'app', 'description' => 'Tìm kiếm'],
            ['key' => 'app.login', 'group' => 'app', 'description' => 'Đăng nhập'],
            ['key' => 'app.logout', 'group' => 'app', 'description' => 'Đăng xuất'],
            ['key' => 'app.register', 'group' => 'app', 'description' => 'Đăng ký'],
            ['key' => 'app.save', 'group' => 'app', 'description' => 'Lưu'],
            ['key' => 'app.cancel', 'group' => 'app', 'description' => 'Hủy'],
            ['key' => 'app.delete', 'group' => 'app', 'description' => 'Xóa'],
            ['key' => 'app.edit', 'group' => 'app', 'description' => 'Chỉnh sửa'],
            ['key' => 'app.view', 'group' => 'app', 'description' => 'Xem'],
            ['key' => 'app.add', 'group' => 'app', 'description' => 'Thêm'],
            ['key' => 'app.update', 'group' => 'app', 'description' => 'Cập nhật'],
            ['key' => 'app.create', 'group' => 'app', 'description' => 'Tạo mới'],
            ['key' => 'app.back', 'group' => 'app', 'description' => 'Quay lại'],
            ['key' => 'app.next', 'group' => 'app', 'description' => 'Tiếp theo'],
            ['key' => 'app.previous', 'group' => 'app', 'description' => 'Trước đó'],
            ['key' => 'app.loading', 'group' => 'app', 'description' => 'Đang tải'],
            ['key' => 'app.success', 'group' => 'app', 'description' => 'Thành công'],
            ['key' => 'app.error', 'group' => 'app', 'description' => 'Lỗi'],
            ['key' => 'app.warning', 'group' => 'app', 'description' => 'Cảnh báo'],
            ['key' => 'app.info', 'group' => 'app', 'description' => 'Thông tin'],

            // Product
            ['key' => 'product.name', 'group' => 'product', 'description' => 'Tên sản phẩm'],
            ['key' => 'product.price', 'group' => 'product', 'description' => 'Giá sản phẩm'],
            ['key' => 'product.description', 'group' => 'product', 'description' => 'Mô tả sản phẩm'],
            ['key' => 'product.category', 'group' => 'product', 'description' => 'Danh mục'],
            ['key' => 'product.stock', 'group' => 'product', 'description' => 'Tồn kho'],
            ['key' => 'product.add_to_cart', 'group' => 'product', 'description' => 'Thêm vào giỏ'],
            ['key' => 'product.buy_now', 'group' => 'product', 'description' => 'Mua ngay'],
            ['key' => 'product.out_of_stock', 'group' => 'product', 'description' => 'Hết hàng'],
            ['key' => 'product.in_stock', 'group' => 'product', 'description' => 'Còn hàng'],

            // Order
            ['key' => 'order.title', 'group' => 'order', 'description' => 'Đơn hàng'],
            ['key' => 'order.number', 'group' => 'order', 'description' => 'Số đơn hàng'],
            ['key' => 'order.status', 'group' => 'order', 'description' => 'Trạng thái'],
            ['key' => 'order.total', 'group' => 'order', 'description' => 'Tổng tiền'],
            ['key' => 'order.customer', 'group' => 'order', 'description' => 'Khách hàng'],
            ['key' => 'order.date', 'group' => 'order', 'description' => 'Ngày đặt'],
            ['key' => 'order.processing', 'group' => 'order', 'description' => 'Đang xử lý'],
            ['key' => 'order.completed', 'group' => 'order', 'description' => 'Hoàn thành'],
            ['key' => 'order.cancelled', 'group' => 'order', 'description' => 'Đã hủy'],

            // Invoice
            ['key' => 'invoice.title', 'group' => 'invoice', 'description' => 'Hóa đơn'],
            ['key' => 'invoice.number', 'group' => 'invoice', 'description' => 'Số hóa đơn'],
            ['key' => 'invoice.date', 'group' => 'invoice', 'description' => 'Ngày lập'],
            ['key' => 'invoice.due_date', 'group' => 'invoice', 'description' => 'Ngày đến hạn'],
            ['key' => 'invoice.amount', 'group' => 'invoice', 'description' => 'Số tiền'],
            ['key' => 'invoice.paid', 'group' => 'invoice', 'description' => 'Đã thanh toán'],
            ['key' => 'invoice.unpaid', 'group' => 'invoice', 'description' => 'Chưa thanh toán'],
            ['key' => 'invoice.overdue', 'group' => 'invoice', 'description' => 'Quá hạn'],

            // Inventory
            ['key' => 'inventory.title', 'group' => 'inventory', 'description' => 'Kho hàng'],
            ['key' => 'inventory.import', 'group' => 'inventory', 'description' => 'Nhập kho'],
            ['key' => 'inventory.export', 'group' => 'inventory', 'description' => 'Xuất kho'],
            ['key' => 'inventory.quantity', 'group' => 'inventory', 'description' => 'Số lượng'],
            ['key' => 'inventory.warehouse', 'group' => 'inventory', 'description' => 'Kho'],
            ['key' => 'inventory.supplier', 'group' => 'inventory', 'description' => 'Nhà cung cấp'],

            // Admin
            ['key' => 'admin.dashboard', 'group' => 'admin', 'description' => 'Bảng điều khiển'],
            ['key' => 'admin.users', 'group' => 'admin', 'description' => 'Người dùng'],
            ['key' => 'admin.settings', 'group' => 'admin', 'description' => 'Cài đặt'],
            ['key' => 'admin.reports', 'group' => 'admin', 'description' => 'Báo cáo'],
            ['key' => 'admin.statistics', 'group' => 'admin', 'description' => 'Thống kê'],
        ];

        foreach ($translationKeys as $keyData) {
            TranslationKey::updateOrCreate(
                ['key' => $keyData['key']],
                $keyData + ['is_system' => true]
            );
        }

        $this->command->info("✅ Tạo {count($translationKeys)} translation keys");
    }

    /**
     * Create translation values.
     */
    private function createTranslationValues()
    {
        $translations = [
            // Vietnamese (default - stored in translation_values for consistency)
            'vi' => [
                'app.name' => 'YukiMart',
                'app.welcome' => 'Chào mừng',
                'app.home' => 'Trang chủ',
                'app.about' => 'Giới thiệu',
                'app.contact' => 'Liên hệ',
                'app.search' => 'Tìm kiếm',
                'app.login' => 'Đăng nhập',
                'app.logout' => 'Đăng xuất',
                'app.register' => 'Đăng ký',
                'app.save' => 'Lưu',
                'app.cancel' => 'Hủy',
                'app.delete' => 'Xóa',
                'app.edit' => 'Chỉnh sửa',
                'app.view' => 'Xem',
                'app.add' => 'Thêm',
                'app.update' => 'Cập nhật',
                'app.create' => 'Tạo mới',
                'app.back' => 'Quay lại',
                'app.next' => 'Tiếp theo',
                'app.previous' => 'Trước đó',
                'app.loading' => 'Đang tải...',
                'app.success' => 'Thành công',
                'app.error' => 'Lỗi',
                'app.warning' => 'Cảnh báo',
                'app.info' => 'Thông tin',
                'product.name' => 'Tên sản phẩm',
                'product.price' => 'Giá',
                'product.description' => 'Mô tả',
                'product.category' => 'Danh mục',
                'product.stock' => 'Tồn kho',
                'product.add_to_cart' => 'Thêm vào giỏ',
                'product.buy_now' => 'Mua ngay',
                'product.out_of_stock' => 'Hết hàng',
                'product.in_stock' => 'Còn hàng',
                'order.title' => 'Đơn hàng',
                'order.number' => 'Số đơn hàng',
                'order.status' => 'Trạng thái',
                'order.total' => 'Tổng tiền',
                'order.customer' => 'Khách hàng',
                'order.date' => 'Ngày đặt',
                'order.processing' => 'Đang xử lý',
                'order.completed' => 'Hoàn thành',
                'order.cancelled' => 'Đã hủy',
                'invoice.title' => 'Hóa đơn',
                'invoice.number' => 'Số hóa đơn',
                'invoice.date' => 'Ngày lập',
                'invoice.due_date' => 'Ngày đến hạn',
                'invoice.amount' => 'Số tiền',
                'invoice.paid' => 'Đã thanh toán',
                'invoice.unpaid' => 'Chưa thanh toán',
                'invoice.overdue' => 'Quá hạn',
                'inventory.title' => 'Kho hàng',
                'inventory.import' => 'Nhập kho',
                'inventory.export' => 'Xuất kho',
                'inventory.quantity' => 'Số lượng',
                'inventory.warehouse' => 'Kho',
                'inventory.supplier' => 'Nhà cung cấp',
                'admin.dashboard' => 'Bảng điều khiển',
                'admin.users' => 'Người dùng',
                'admin.settings' => 'Cài đặt',
                'admin.reports' => 'Báo cáo',
                'admin.statistics' => 'Thống kê',
            ],
            
            // English
            'en' => [
                'app.name' => 'YukiMart',
                'app.welcome' => 'Welcome',
                'app.home' => 'Home',
                'app.about' => 'About',
                'app.contact' => 'Contact',
                'app.search' => 'Search',
                'app.login' => 'Login',
                'app.logout' => 'Logout',
                'app.register' => 'Register',
                'app.save' => 'Save',
                'app.cancel' => 'Cancel',
                'app.delete' => 'Delete',
                'app.edit' => 'Edit',
                'app.view' => 'View',
                'app.add' => 'Add',
                'app.update' => 'Update',
                'app.create' => 'Create',
                'app.back' => 'Back',
                'app.next' => 'Next',
                'app.previous' => 'Previous',
                'app.loading' => 'Loading...',
                'app.success' => 'Success',
                'app.error' => 'Error',
                'app.warning' => 'Warning',
                'app.info' => 'Information',
                'product.name' => 'Product Name',
                'product.price' => 'Price',
                'product.description' => 'Description',
                'product.category' => 'Category',
                'product.stock' => 'Stock',
                'product.add_to_cart' => 'Add to Cart',
                'product.buy_now' => 'Buy Now',
                'product.out_of_stock' => 'Out of Stock',
                'product.in_stock' => 'In Stock',
                'order.title' => 'Order',
                'order.number' => 'Order Number',
                'order.status' => 'Status',
                'order.total' => 'Total',
                'order.customer' => 'Customer',
                'order.date' => 'Order Date',
                'order.processing' => 'Processing',
                'order.completed' => 'Completed',
                'order.cancelled' => 'Cancelled',
                'invoice.title' => 'Invoice',
                'invoice.number' => 'Invoice Number',
                'invoice.date' => 'Invoice Date',
                'invoice.due_date' => 'Due Date',
                'invoice.amount' => 'Amount',
                'invoice.paid' => 'Paid',
                'invoice.unpaid' => 'Unpaid',
                'invoice.overdue' => 'Overdue',
                'inventory.title' => 'Inventory',
                'inventory.import' => 'Import',
                'inventory.export' => 'Export',
                'inventory.quantity' => 'Quantity',
                'inventory.warehouse' => 'Warehouse',
                'inventory.supplier' => 'Supplier',
                'admin.dashboard' => 'Dashboard',
                'admin.users' => 'Users',
                'admin.settings' => 'Settings',
                'admin.reports' => 'Reports',
                'admin.statistics' => 'Statistics',
            ],
            
            // Japanese
            'ja' => [
                'app.name' => 'YukiMart',
                'app.welcome' => 'ようこそ',
                'app.home' => 'ホーム',
                'app.about' => '会社概要',
                'app.contact' => 'お問い合わせ',
                'app.search' => '検索',
                'app.login' => 'ログイン',
                'app.logout' => 'ログアウト',
                'app.register' => '登録',
                'app.save' => '保存',
                'app.cancel' => 'キャンセル',
                'app.delete' => '削除',
                'app.edit' => '編集',
                'app.view' => '表示',
                'app.add' => '追加',
                'app.update' => '更新',
                'app.create' => '作成',
                'app.back' => '戻る',
                'app.next' => '次へ',
                'app.previous' => '前へ',
                'app.loading' => '読み込み中...',
                'app.success' => '成功',
                'app.error' => 'エラー',
                'app.warning' => '警告',
                'app.info' => '情報',
                'product.name' => '商品名',
                'product.price' => '価格',
                'product.description' => '説明',
                'product.category' => 'カテゴリー',
                'product.stock' => '在庫',
                'product.add_to_cart' => 'カートに追加',
                'product.buy_now' => '今すぐ購入',
                'product.out_of_stock' => '在庫切れ',
                'product.in_stock' => '在庫あり',
                'order.title' => '注文',
                'order.number' => '注文番号',
                'order.status' => 'ステータス',
                'order.total' => '合計',
                'order.customer' => '顧客',
                'order.date' => '注文日',
                'order.processing' => '処理中',
                'order.completed' => '完了',
                'order.cancelled' => 'キャンセル済み',
                'invoice.title' => '請求書',
                'invoice.number' => '請求書番号',
                'invoice.date' => '請求日',
                'invoice.due_date' => '支払期限',
                'invoice.amount' => '金額',
                'invoice.paid' => '支払済み',
                'invoice.unpaid' => '未払い',
                'invoice.overdue' => '期限切れ',
                'inventory.title' => '在庫',
                'inventory.import' => '入庫',
                'inventory.export' => '出庫',
                'inventory.quantity' => '数量',
                'inventory.warehouse' => '倉庫',
                'inventory.supplier' => 'サプライヤー',
                'admin.dashboard' => 'ダッシュボード',
                'admin.users' => 'ユーザー',
                'admin.settings' => '設定',
                'admin.reports' => 'レポート',
                'admin.statistics' => '統計',
            ],
        ];

        foreach ($translations as $languageCode => $values) {
            foreach ($values as $key => $value) {
                $translationKey = TranslationKey::where('key', $key)->first();
                if ($translationKey) {
                    TranslationValue::updateOrCreate(
                        [
                            'translation_key_id' => $translationKey->id,
                            'language_code' => $languageCode,
                        ],
                        [
                            'value' => $value,
                            'is_approved' => true,
                            'created_by' => 1, // Assume admin user ID is 1
                        ]
                    );
                }
            }
            
            $this->command->info("✅ Tạo bản dịch cho ngôn ngữ: {$languageCode}");
        }
    }
}
