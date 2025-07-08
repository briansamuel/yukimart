<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class UpdateCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing customers with new fields
        $customers = [
            [
                'id' => 1,
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
                'id' => 2,
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
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::find($customerData['id']);
            if ($customer) {
                $customer->update($customerData);
            } else {
                Customer::create($customerData);
            }
        }
    }
}
