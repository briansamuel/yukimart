<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating audit logs...');

        // Get existing users
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Creating a default user for audit logs.');
            $users = collect([
                User::factory()->create([
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                ])
            ]);
        }

        // Create various types of audit logs
        $this->createLoginLogs($users);
        $this->createProductLogs($users);
        $this->createOrderLogs($users);
        $this->createCustomerLogs($users);
        $this->createSystemLogs($users);

        $this->command->info('Audit logs created successfully!');
    }

    /**
     * Create login/logout audit logs
     */
    private function createLoginLogs($users): void
    {
        $this->command->info('Creating login/logout logs...');

        foreach ($users as $user) {
            // Create login logs
            AuditLog::factory()
                ->count(rand(5, 15))
                ->login()
                ->forUser($user)
                ->create();

            // Create logout logs
            AuditLog::factory()
                ->count(rand(3, 10))
                ->logout()
                ->forUser($user)
                ->create();
        }
    }

    /**
     * Create product-related audit logs
     */
    private function createProductLogs($users): void
    {
        $this->command->info('Creating product-related logs...');

        foreach ($users as $user) {
            // Product creation logs
            AuditLog::factory()
                ->count(rand(10, 20))
                ->product()
                ->forUser($user)
                ->state(['action' => 'created'])
                ->create();

            // Product update logs
            AuditLog::factory()
                ->count(rand(15, 30))
                ->product()
                ->forUser($user)
                ->state(['action' => 'updated'])
                ->create();

            // Product view logs
            AuditLog::factory()
                ->count(rand(20, 40))
                ->product()
                ->forUser($user)
                ->state(['action' => 'viewed'])
                ->create();

            // Product deletion logs
            AuditLog::factory()
                ->count(rand(2, 5))
                ->product()
                ->forUser($user)
                ->state(['action' => 'deleted'])
                ->create();
        }
    }

    /**
     * Create order-related audit logs
     */
    private function createOrderLogs($users): void
    {
        $this->command->info('Creating order-related logs...');

        foreach ($users as $user) {
            // Order creation logs
            AuditLog::factory()
                ->count(rand(15, 25))
                ->order()
                ->forUser($user)
                ->state(['action' => 'created'])
                ->create();

            // Order update logs
            AuditLog::factory()
                ->count(rand(20, 35))
                ->order()
                ->forUser($user)
                ->state(['action' => 'updated'])
                ->create();

            // Order view logs
            AuditLog::factory()
                ->count(rand(30, 50))
                ->order()
                ->forUser($user)
                ->state(['action' => 'viewed'])
                ->create();
        }
    }

    /**
     * Create customer-related audit logs
     */
    private function createCustomerLogs($users): void
    {
        $this->command->info('Creating customer-related logs...');

        foreach ($users as $user) {
            // Customer creation logs
            AuditLog::factory()
                ->count(rand(8, 15))
                ->customer()
                ->forUser($user)
                ->state(['action' => 'created'])
                ->create();

            // Customer update logs
            AuditLog::factory()
                ->count(rand(10, 20))
                ->customer()
                ->forUser($user)
                ->state(['action' => 'updated'])
                ->create();

            // Customer view logs
            AuditLog::factory()
                ->count(rand(15, 25))
                ->customer()
                ->forUser($user)
                ->state(['action' => 'viewed'])
                ->create();
        }
    }

    /**
     * Create system-related audit logs
     */
    private function createSystemLogs($users): void
    {
        $this->command->info('Creating system-related logs...');

        foreach ($users as $user) {
            // Export logs
            AuditLog::factory()
                ->count(rand(3, 8))
                ->forUser($user)
                ->state([
                    'action' => 'exported',
                    'model_type' => null,
                    'model_id' => null,
                    'description' => 'Xuất dữ liệu hệ thống',
                ])
                ->create();

            // Import logs
            AuditLog::factory()
                ->count(rand(2, 5))
                ->forUser($user)
                ->state([
                    'action' => 'imported',
                    'model_type' => null,
                    'model_id' => null,
                    'description' => 'Nhập dữ liệu hệ thống',
                ])
                ->create();

            // Backup logs
            AuditLog::factory()
                ->count(rand(1, 3))
                ->forUser($user)
                ->state([
                    'action' => 'backup',
                    'model_type' => null,
                    'model_id' => null,
                    'description' => 'Tạo sao lưu hệ thống',
                ])
                ->create();
        }
    }
}
