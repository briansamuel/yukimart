<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'customer_code' => 'KH000257',
                'name' => 'Anh Thành',
                'phone' => '0986319229',
                'email' => 'anhthanh@example.com',
                'address' => 'Số nhà, tòa nhà, ngõ, đường',
                'area' => 'Chọn Tỉnh/TP - Quận/Huyện',
                'customer_type' => 'individual',
                'customer_group' => 'VIP',
                'tax_code' => null,
                'facebook' => null,
                'birthday' => '1990-05-15',
                'points' => 524,
                'notes' => 'Chi nhánh tạo: 524 Lý Thường Kiệt',
                'status' => 'active',
            ],
            [
                'customer_code' => 'KH000001',
                'name' => 'Sen cam',
                'phone' => '0358108991',
                'email' => 'sencam@example.com',
                'address' => '123 Đường ABC, Quận 1',
                'area' => 'TP.HCM - Quận 1',
                'customer_type' => 'individual',
                'customer_group' => 'Thường',
                'tax_code' => null,
                'facebook' => 'sencam.fb',
                'birthday' => '1985-03-20',
                'points' => 150,
                'notes' => 'Khách hàng thân thiết',
                'status' => 'active',
            ],
            [
                'customer_code' => 'KH000002',
                'name' => 'Nguyễn Văn A',
                'phone' => '0987654321',
                'email' => 'nguyenvana@example.com',
                'address' => '456 Đường XYZ, Quận 2',
                'area' => 'TP.HCM - Quận 2',
                'customer_type' => 'company',
                'customer_group' => 'Doanh nghiệp',
                'tax_code' => '0123456789',
                'facebook' => null,
                'birthday' => '1980-12-10',
                'points' => 300,
                'notes' => 'Công ty ABC',
                'status' => 'active',
            ],
            [
                'customer_code' => 'KH000003',
                'name' => 'Trần Thị B',
                'phone' => '0912345678',
                'email' => 'tranthib@example.com',
                'address' => '789 Đường DEF, Quận 3',
                'area' => 'TP.HCM - Quận 3',
                'customer_type' => 'individual',
                'customer_group' => 'VIP',
                'tax_code' => null,
                'facebook' => 'tranthib.fb',
                'birthday' => '1992-07-25',
                'points' => 750,
                'notes' => 'Khách VIP',
                'status' => 'active',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
