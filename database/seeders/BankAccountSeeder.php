<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BankAccount;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankAccounts = [
            [
                'bank_name' => 'BIDV',
                'bank_code' => 'bidv',
                'account_number' => '31010002308763',
                'account_holder' => 'LA THI HONG NHUNG',
                'branch_name' => 'Chi nhánh Hà Nội',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'notes' => 'Tài khoản chính của cửa hàng',
            ],
            [
                'bank_name' => 'Vietcombank',
                'bank_code' => 'vcb',
                'account_number' => '1234567890',
                'account_holder' => 'LA THI HONG NHUNG',
                'branch_name' => 'Chi nhánh Cầu Giấy',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
                'notes' => 'Tài khoản phụ',
            ],
            [
                'bank_name' => 'Techcombank',
                'bank_code' => 'tcb',
                'account_number' => '9876543210',
                'account_holder' => 'LA THI HONG NHUNG',
                'branch_name' => 'Chi nhánh Thanh Xuân',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
                'notes' => 'Tài khoản dự phòng',
            ],
            [
                'bank_name' => 'VietinBank',
                'bank_code' => 'vietinbank',
                'account_number' => '113366668888',
                'account_holder' => 'QUY VAC XIN COVID',
                'branch_name' => 'Chi nhánh Hoàn Kiếm',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
                'notes' => 'Tài khoản VietinBank mẫu',
            ],
        ];

        foreach ($bankAccounts as $account) {
            BankAccount::create($account);
        }
    }
}
