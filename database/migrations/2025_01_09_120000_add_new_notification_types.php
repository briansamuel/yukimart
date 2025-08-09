<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationSetting;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm default notification settings cho các types mới
        $this->addNewNotificationTypes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa các notification types mới
        $newTypes = [
            'customer_birthday',
            'receipt_voucher',
            'payment_voucher',
            'inventory_update',
            'inventory_check',
            'inventory_alert',
            'order_complete',
            'order_cancel',
            'invoice_complete',
            'invoice_cancel',
            'return_complete',
            'delivery_complete',
            'import_complete',
            'import_return',
            'transfer_complete',
            'transfer_cancel',
        ];

        DB::table('notification_settings')
            ->whereIn('notification_type', $newTypes)
            ->delete();
    }

    /**
     * Thêm notification settings mới cho tất cả users hiện có.
     */
    private function addNewNotificationTypes()
    {
        $newTypes = [
            // === KHÁCH HÀNG ===
            'customer_birthday' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],

            // === SỔ QUỸ ===
            'receipt_voucher' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'payment_voucher' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],

            // === HÀNG HÓA ===
            'inventory_update' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'inventory_check' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'inventory_alert' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],

            // === GIAO DỊCH ===
            'order_complete' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'order_cancel' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'invoice_complete' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'invoice_cancel' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'return_complete' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'delivery_complete' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'import_complete' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'import_return' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'transfer_complete' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
            'transfer_cancel' => [
                'default_channels' => ['web', 'fcm'],
                'default_enabled' => true,
            ],
        ];

        // Lấy tất cả users
        $users = User::all();

        foreach ($users as $user) {
            foreach ($newTypes as $type => $config) {
                // Kiểm tra xem setting đã tồn tại chưa
                $exists = DB::table('notification_settings')
                    ->where('user_id', $user->id)
                    ->where('notification_type', $type)
                    ->exists();

                if (!$exists) {
                    DB::table('notification_settings')->insert([
                        'user_id' => $user->id,
                        'notification_type' => $type,
                        'channels' => json_encode($config['default_channels']),
                        'is_enabled' => $config['default_enabled'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
