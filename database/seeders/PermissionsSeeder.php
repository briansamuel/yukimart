<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate permissions table
        Permission::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $permissions = $this->getPermissions();
        
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        
        $this->command->info('Created ' . count($permissions) . ' permissions successfully!');
    }
    
    /**
     * Helper method to generate permissions
     */
    private function generatePermissions(string $module, string $subModule, array $actions, int $baseSort, string $displayPrefix = ''): array
    {
        $permissions = [];
        $sortOrder = $baseSort;

        foreach ($actions as $action => $displayName) {
            $permissions[] = [
                'name' => "{$module}.{$subModule}.{$action}",
                'guard_name' => 'web',
                'display_name' => $displayPrefix . $displayName,
                'module' => $module,
                'sub_module' => $subModule,
                'action' => $action,
                'description' => $displayPrefix . $displayName,
                'is_active' => true,
                'sort_order' => $sortOrder++,
                'tenant_id' => null,
            ];
        }

        return $permissions;
    }

    /**
     * Get all permissions data
     */
    private function getPermissions(): array
    {
        $permissions = [];

        // ========================================
        // 1. TỔNG QUAN (OVERVIEW/DASHBOARD)
        // ========================================
        $permissions = array_merge($permissions, $this->generatePermissions(
            'overview', 'dashboard',
            ['read' => 'Xem tổng quan'],
            100
        ));

        // ========================================
        // 2. HÀNG HÓA (CATALOG)
        // ========================================

        // 2.1 Danh sách hàng hóa
        $permissions = array_merge($permissions, $this->generatePermissions(
            'catalog', 'products',
            [
                'read' => 'Xem danh sách hàng hóa',
                'create' => 'Tạo hàng hóa',
                'update' => 'Chỉnh sửa hàng hóa',
                'delete' => 'Xóa hàng hóa',
                'import' => 'Import hàng hóa',
                'export' => 'Export hàng hóa',
                'cost.view' => 'Xem giá vốn',
                'cost.update' => 'Sửa giá vốn',
                'purchase_price.view' => 'Xem giá nhập',
                'purchase_price.update' => 'Sửa giá nhập',
            ],
            200
        ));

        // 2.2 Danh mục hàng hóa
        $permissions = array_merge($permissions, $this->generatePermissions(
            'catalog', 'categories',
            [
                'read' => 'Xem danh mục',
                'create' => 'Tạo danh mục',
                'update' => 'Chỉnh sửa danh mục',
                'delete' => 'Xóa danh mục',
            ],
            250
        ));

        // ========================================
        // 3. KHO HÀNG (INVENTORY/WAREHOUSE)
        // ========================================

        // 3.1 Danh sách kho hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'inventory', 'warehouses',
            [
                'read' => 'Xem danh sách kho',
                'create' => 'Tạo kho',
                'update' => 'Chỉnh sửa kho',
                'delete' => 'Xóa kho',
            ],
            300
        ));

        // 3.2 Tồn kho
        $permissions = array_merge($permissions, $this->generatePermissions(
            'inventory', 'stock',
            [
                'read' => 'Xem tồn kho',
                'adjust' => 'Điều chỉnh tồn kho',
                'export' => 'Export tồn kho',
            ],
            320
        ));

        // 3.3 Kiểm kho
        $permissions = array_merge($permissions, $this->generatePermissions(
            'inventory', 'stock_check',
            [
                'read' => 'Xem phiếu kiểm kho',
                'create' => 'Tạo phiếu kiểm kho',
                'update' => 'Cập nhật phiếu kiểm kho',
                'delete' => 'Xóa phiếu kiểm kho',
                'approve' => 'Duyệt phiếu kiểm kho',
            ],
            340
        ));

        // 3.4 Chuyển kho
        $permissions = array_merge($permissions, $this->generatePermissions(
            'inventory', 'transfer',
            [
                'read' => 'Xem phiếu chuyển kho',
                'create' => 'Tạo phiếu chuyển kho',
                'update' => 'Cập nhật phiếu chuyển kho',
                'delete' => 'Xóa phiếu chuyển kho',
                'approve' => 'Duyệt phiếu chuyển kho',
            ],
            360
        ));

        // ========================================
        // 4. NHẬP HÀNG (PURCHASING)
        // ========================================

        // 4.1 Nhà cung cấp
        $permissions = array_merge($permissions, $this->generatePermissions(
            'purchasing', 'suppliers',
            [
                'read' => 'Xem nhà cung cấp',
                'create' => 'Tạo nhà cung cấp',
                'update' => 'Chỉnh sửa nhà cung cấp',
                'delete' => 'Xóa nhà cung cấp',
                'import' => 'Import nhà cung cấp',
                'export' => 'Export nhà cung cấp',
            ],
            400
        ));

        // 4.2 Đặt hàng nhập
        $permissions = array_merge($permissions, $this->generatePermissions(
            'purchasing', 'purchase_orders',
            [
                'read' => 'Xem đơn đặt hàng',
                'create' => 'Tạo đơn đặt hàng',
                'update' => 'Chỉnh sửa đơn đặt hàng',
                'delete' => 'Xóa đơn đặt hàng',
                'approve' => 'Duyệt đơn đặt hàng',
                'cancel' => 'Hủy đơn đặt hàng',
            ],
            420
        ));

        // 4.3 Phiếu nhập hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'purchasing', 'receipts',
            [
                'read' => 'Xem phiếu nhập hàng',
                'create' => 'Tạo phiếu nhập hàng',
                'update' => 'Chỉnh sửa phiếu nhập hàng',
                'delete' => 'Xóa phiếu nhập hàng',
                'approve' => 'Duyệt phiếu nhập hàng',
            ],
            440
        ));

        // 4.4 Trả hàng nhập
        $permissions = array_merge($permissions, $this->generatePermissions(
            'purchasing', 'returns',
            [
                'read' => 'Xem phiếu trả hàng',
                'create' => 'Tạo phiếu trả hàng',
                'update' => 'Chỉnh sửa phiếu trả hàng',
                'delete' => 'Xóa phiếu trả hàng',
                'approve' => 'Duyệt phiếu trả hàng',
            ],
            460
        ));

        // 4.5 Thanh toán nhà cung cấp
        $permissions = array_merge($permissions, $this->generatePermissions(
            'purchasing', 'payments',
            [
                'read' => 'Xem thanh toán NCC',
                'create' => 'Tạo thanh toán NCC',
                'update' => 'Chỉnh sửa thanh toán NCC',
                'delete' => 'Xóa thanh toán NCC',
            ],
            480
        ));

        // 4.6 Công nợ nhà cung cấp
        $permissions = array_merge($permissions, $this->generatePermissions(
            'purchasing', 'payables',
            [
                'read' => 'Xem công nợ NCC',
                'export' => 'Export công nợ NCC',
            ],
            500
        ));

        // 4.7 Báo cáo nhập hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'purchasing', 'reports',
            [
                'read' => 'Xem báo cáo nhập hàng',
                'export' => 'Export báo cáo nhập hàng',
            ],
            520
        ));

        // ========================================
        // 5. ĐƠN HÀNG (ORDERS)
        // ========================================

        // 5.1 Danh sách đơn hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'orders', 'list',
            [
                'read' => 'Xem đơn hàng',
                'create' => 'Tạo đơn hàng',
                'update' => 'Chỉnh sửa đơn hàng',
                'delete' => 'Xóa đơn hàng',
                'cancel' => 'Hủy đơn hàng',
                'export' => 'Export đơn hàng',
            ],
            600
        ));

        // 5.2 Hóa đơn
        $permissions = array_merge($permissions, $this->generatePermissions(
            'orders', 'invoices',
            [
                'read' => 'Xem hóa đơn',
                'create' => 'Tạo hóa đơn',
                'update' => 'Chỉnh sửa hóa đơn',
                'delete' => 'Xóa hóa đơn',
                'export' => 'Export hóa đơn',
            ],
            620
        ));

        // 5.3 Trả hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'orders', 'returns',
            [
                'read' => 'Xem đơn trả hàng',
                'create' => 'Tạo đơn trả hàng',
                'update' => 'Chỉnh sửa đơn trả hàng',
                'delete' => 'Xóa đơn trả hàng',
                'approve' => 'Duyệt đơn trả hàng',
            ],
            640
        ));

        // 5.4 Đặt hàng online
        $permissions = array_merge($permissions, $this->generatePermissions(
            'orders', 'online_orders',
            [
                'read' => 'Xem đơn online',
                'update' => 'Xử lý đơn online',
                'cancel' => 'Hủy đơn online',
            ],
            660
        ));

        // 5.5 Bán tại quầy
        $permissions = array_merge($permissions, $this->generatePermissions(
            'orders', 'pos',
            [
                'read' => 'Xem đơn POS',
                'create' => 'Tạo đơn POS',
            ],
            680
        ));

        // 5.6 Báo cáo đơn hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'orders', 'reports',
            [
                'read' => 'Xem báo cáo đơn hàng',
                'export' => 'Export báo cáo đơn hàng',
            ],
            700
        ));

        // ========================================
        // 6. GIAO HÀNG (DELIVERY)
        // ========================================

        // 6.1 Đơn giao hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'delivery', 'shipments',
            [
                'read' => 'Xem đơn giao hàng',
                'create' => 'Tạo đơn giao hàng',
                'update' => 'Cập nhật đơn giao hàng',
                'delete' => 'Xóa đơn giao hàng',
                'assign' => 'Phân công giao hàng',
            ],
            800
        ));

        // 6.2 Đối tác vận chuyển
        $permissions = array_merge($permissions, $this->generatePermissions(
            'delivery', 'carriers',
            [
                'read' => 'Xem đối tác vận chuyển',
                'create' => 'Tạo đối tác vận chuyển',
                'update' => 'Chỉnh sửa đối tác vận chuyển',
                'delete' => 'Xóa đối tác vận chuyển',
            ],
            820
        ));

        // 6.3 Báo cáo giao hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'delivery', 'reports',
            [
                'read' => 'Xem báo cáo giao hàng',
                'export' => 'Export báo cáo giao hàng',
            ],
            840
        ));

        // ========================================
        // 7. KHÁCH HÀNG (CUSTOMERS)
        // ========================================

        // 7.1 Danh sách khách hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'customers', 'list',
            [
                'read' => 'Xem khách hàng',
                'create' => 'Tạo khách hàng',
                'update' => 'Chỉnh sửa khách hàng',
                'delete' => 'Xóa khách hàng',
                'import' => 'Import khách hàng',
                'export' => 'Export khách hàng',
            ],
            900
        ));

        // 7.2 Nhóm khách hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'customers', 'groups',
            [
                'read' => 'Xem nhóm khách hàng',
                'create' => 'Tạo nhóm khách hàng',
                'update' => 'Chỉnh sửa nhóm khách hàng',
                'delete' => 'Xóa nhóm khách hàng',
            ],
            920
        ));

        // 7.3 Công nợ khách hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'customers', 'receivables',
            [
                'read' => 'Xem công nợ khách hàng',
                'export' => 'Export công nợ khách hàng',
            ],
            940
        ));

        // 7.4 Điểm thưởng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'customers', 'loyalty_points',
            [
                'read' => 'Xem điểm thưởng',
                'adjust' => 'Điều chỉnh điểm thưởng',
            ],
            960
        ));

        // ========================================
        // 8. KHUYẾN MẠI (PROMOTIONS)
        // ========================================

        // 8.1 Chương trình khuyến mại
        $permissions = array_merge($permissions, $this->generatePermissions(
            'promotions', 'campaigns',
            [
                'read' => 'Xem chương trình KM',
                'create' => 'Tạo chương trình KM',
                'update' => 'Chỉnh sửa chương trình KM',
                'delete' => 'Xóa chương trình KM',
                'activate' => 'Kích hoạt chương trình KM',
            ],
            1000
        ));

        // 8.2 Mã giảm giá
        $permissions = array_merge($permissions, $this->generatePermissions(
            'promotions', 'coupons',
            [
                'read' => 'Xem mã giảm giá',
                'create' => 'Tạo mã giảm giá',
                'update' => 'Chỉnh sửa mã giảm giá',
                'delete' => 'Xóa mã giảm giá',
            ],
            1020
        ));

        // 8.3 Báo cáo khuyến mại
        $permissions = array_merge($permissions, $this->generatePermissions(
            'promotions', 'reports',
            [
                'read' => 'Xem báo cáo KM',
                'export' => 'Export báo cáo KM',
            ],
            1040
        ));

        // ========================================
        // 9. SỔ QUỸ (CASH_BOOK)
        // ========================================

        // 9.1 Thu chi
        $permissions = array_merge($permissions, $this->generatePermissions(
            'cash_book', 'transactions',
            [
                'read' => 'Xem phiếu thu chi',
                'create' => 'Tạo phiếu thu chi',
                'update' => 'Chỉnh sửa phiếu thu chi',
                'delete' => 'Xóa phiếu thu chi',
                'approve' => 'Duyệt phiếu thu chi',
            ],
            1100
        ));

        // 9.2 Báo cáo sổ quỹ
        $permissions = array_merge($permissions, $this->generatePermissions(
            'cash_book', 'reports',
            [
                'read' => 'Xem báo cáo sổ quỹ',
                'export' => 'Export báo cáo sổ quỹ',
            ],
            1120
        ));

        // ========================================
        // 10. BÁN ONLINE (ONLINE_SALES)
        // ========================================

        // 10.1 Kênh bán hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'online_sales', 'channels',
            [
                'read' => 'Xem kênh bán hàng',
                'create' => 'Tạo kênh bán hàng',
                'update' => 'Chỉnh sửa kênh bán hàng',
                'delete' => 'Xóa kênh bán hàng',
                'sync' => 'Đồng bộ kênh bán hàng',
            ],
            1200
        ));

        // ========================================
        // 11. PHÂN TÍCH & BÁO CÁO (ANALYTICS_REPORTS)
        // ========================================

        // 11.1 Phân tích kinh doanh
        $permissions = array_merge($permissions, $this->generatePermissions(
            'analytics', 'business',
            [
                'read' => 'Xem phân tích kinh doanh',
                'export' => 'Export phân tích kinh doanh',
            ],
            1300
        ));

        // 11.2 Báo cáo cuối ngày
        $permissions = array_merge($permissions, $this->generatePermissions(
            'reports', 'end_of_day',
            [
                'read' => 'Xem báo cáo cuối ngày',
                'export' => 'Export báo cáo cuối ngày',
            ],
            1320
        ));

        // 11.3 Báo cáo bán hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'reports', 'sales',
            [
                'read' => 'Xem báo cáo bán hàng',
                'export' => 'Export báo cáo bán hàng',
                'profit' => 'Xem lợi nhuận',
            ],
            1340
        ));

        // 11.4 Báo cáo hàng hóa
        $permissions = array_merge($permissions, $this->generatePermissions(
            'reports', 'products',
            [
                'read' => 'Xem báo cáo hàng hóa',
                'export' => 'Export báo cáo hàng hóa',
            ],
            1360
        ));

        // 11.5 Báo cáo khách hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'reports', 'customers',
            [
                'read' => 'Xem báo cáo khách hàng',
                'export' => 'Export báo cáo khách hàng',
            ],
            1380
        ));

        // 11.6 Báo cáo nhà cung cấp
        $permissions = array_merge($permissions, $this->generatePermissions(
            'reports', 'suppliers',
            [
                'read' => 'Xem báo cáo NCC',
                'export' => 'Export báo cáo NCC',
            ],
            1400
        ));

        // 11.7 Báo cáo nhân viên
        $permissions = array_merge($permissions, $this->generatePermissions(
            'reports', 'staff',
            [
                'read' => 'Xem báo cáo nhân viên',
                'export' => 'Export báo cáo nhân viên',
            ],
            1420
        ));

        // 11.8 Báo cáo tài chính
        $permissions = array_merge($permissions, $this->generatePermissions(
            'reports', 'financial',
            [
                'read' => 'Xem báo cáo tài chính',
                'export' => 'Export báo cáo tài chính',
            ],
            1440
        ));

        // ========================================
        // 12. NHÂN VIÊN (STAFF)
        // ========================================

        // 12.1 Thông tin nhân viên
        $permissions = array_merge($permissions, $this->generatePermissions(
            'staff', 'employees',
            [
                'read' => 'Xem nhân viên',
                'create' => 'Tạo nhân viên',
                'update' => 'Chỉnh sửa nhân viên',
                'delete' => 'Xóa nhân viên',
            ],
            1500
        ));

        // 12.2 Chấm công
        $permissions = array_merge($permissions, $this->generatePermissions(
            'staff', 'attendance',
            [
                'read' => 'Xem chấm công',
                'create' => 'Tạo chấm công',
                'update' => 'Chỉnh sửa chấm công',
                'delete' => 'Xóa chấm công',
            ],
            1520
        ));

        // 12.3 Lương
        $permissions = array_merge($permissions, $this->generatePermissions(
            'staff', 'payroll',
            [
                'read' => 'Xem bảng lương',
                'create' => 'Tạo bảng lương',
                'update' => 'Chỉnh sửa bảng lương',
                'approve' => 'Duyệt bảng lương',
                'payment' => 'Thanh toán lương',
            ],
            1540
        ));

        // 12.4 Ca làm việc
        $permissions = array_merge($permissions, $this->generatePermissions(
            'staff', 'shifts',
            [
                'read' => 'Xem ca làm việc',
                'create' => 'Tạo ca làm việc',
                'update' => 'Chỉnh sửa ca làm việc',
                'delete' => 'Xóa ca làm việc',
            ],
            1560
        ));

        // 12.5 Máy chấm công
        $permissions = array_merge($permissions, $this->generatePermissions(
            'staff', 'time_clock',
            [
                'read' => 'Xem máy chấm công',
                'create' => 'Tạo máy chấm công',
                'update' => 'Chỉnh sửa máy chấm công',
                'delete' => 'Xóa máy chấm công',
            ],
            1580
        ));

        // ========================================
        // 13. THIẾT LẬP (SETTINGS)
        // ========================================

        // 13.1 Cửa hàng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'settings', 'shop',
            [
                'read' => 'Xem thiết lập cửa hàng',
                'update' => 'Chỉnh sửa thiết lập cửa hàng',
            ],
            1600
        ));

        // 13.2 Chi nhánh
        $permissions = array_merge($permissions, $this->generatePermissions(
            'settings', 'branches',
            [
                'read' => 'Xem chi nhánh',
                'create' => 'Tạo chi nhánh',
                'update' => 'Chỉnh sửa chi nhánh',
                'delete' => 'Xóa chi nhánh',
            ],
            1620
        ));

        // 13.3 Người dùng
        $permissions = array_merge($permissions, $this->generatePermissions(
            'settings', 'users',
            [
                'read' => 'Xem người dùng',
                'create' => 'Tạo người dùng',
                'update' => 'Chỉnh sửa người dùng',
                'delete' => 'Xóa người dùng',
                'reset_password' => 'Reset mật khẩu',
            ],
            1640
        ));

        // 13.4 Vai trò & Phân quyền
        $permissions = array_merge($permissions, $this->generatePermissions(
            'settings', 'roles',
            [
                'read' => 'Xem vai trò',
                'create' => 'Tạo vai trò',
                'update' => 'Chỉnh sửa vai trò',
                'delete' => 'Xóa vai trò',
                'assign' => 'Phân quyền',
            ],
            1660
        ));

        // 13.5 Cấu hình hệ thống
        $permissions = array_merge($permissions, $this->generatePermissions(
            'settings', 'system',
            [
                'read' => 'Xem cấu hình hệ thống',
                'update' => 'Chỉnh sửa cấu hình hệ thống',
            ],
            1680
        ));

        return $permissions;
    }
}

