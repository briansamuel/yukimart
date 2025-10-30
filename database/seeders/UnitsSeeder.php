<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Tenant;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found. Please create tenants first.');
            return;
        }

        // Units data
        $units = [
            ['name' => 'Cái', 'sort_order' => 1],
            ['name' => 'Chiếc', 'sort_order' => 2],
            ['name' => 'Hộp', 'sort_order' => 3],
            ['name' => 'Chai', 'sort_order' => 4],
            ['name' => 'Lọ', 'sort_order' => 5],
            ['name' => 'Lon', 'sort_order' => 6],
            ['name' => 'Gói', 'sort_order' => 7],
            ['name' => 'Túi', 'sort_order' => 8],
            ['name' => 'Bịch', 'sort_order' => 9],
            ['name' => 'Lốc', 'sort_order' => 10],
            ['name' => 'Thùng', 'sort_order' => 11],
            ['name' => 'Kg', 'sort_order' => 12],
            ['name' => 'Gram', 'sort_order' => 13],
            ['name' => 'Lít', 'sort_order' => 14],
            ['name' => 'Ml', 'sort_order' => 15],
        ];

        // Create units for each tenant
        foreach ($tenants as $tenant) {
            foreach ($units as $unitData) {
                Unit::create([
                    'tenant_id' => $tenant->id,
                    'name' => $unitData['name'],
                    'is_active' => true,
                    'sort_order' => $unitData['sort_order'],
                ]);
            }

            $this->command->info("Created " . count($units) . " units for tenant: {$tenant->name}");
        }

        $this->command->info('Units seeder completed successfully!');
    }
}

