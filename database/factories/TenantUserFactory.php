<?php

namespace Database\Factories;

use App\Models\TenantUser;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantUser>
 */
class TenantUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TenantUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'role' => $this->faker->randomElement([
                TenantUser::ROLE_ADMIN,
                TenantUser::ROLE_MANAGER,
                TenantUser::ROLE_STAFF,
                TenantUser::ROLE_VIEWER
            ]),
            'permissions' => $this->getRandomPermissions(),
            'restrictions' => $this->getRandomRestrictions(),
            'is_active' => $this->faker->boolean(90),
            'is_primary' => false,
            'joined_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'last_access_at' => $this->faker->optional(70)->dateTimeBetween('-30 days', 'now'),
            'access_expires_at' => $this->faker->optional(20)->dateTimeBetween('now', '+1 year'),
            'invitation_status' => TenantUser::INVITATION_ACCEPTED,
            'invitation_token' => null,
            'invitation_sent_at' => null,
            'invitation_expires_at' => null,
            'invited_by' => null,
            'approved_by' => null,
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now')
        ];
    }

    /**
     * Indicate that the user is an owner.
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => TenantUser::ROLE_OWNER,
            'is_primary' => true,
            'permissions' => ['*'], // All permissions
            'restrictions' => []
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => TenantUser::ROLE_ADMIN,
            'permissions' => [
                'users.manage',
                'products.manage',
                'orders.manage',
                'invoices.manage',
                'reports.view',
                'settings.manage'
            ]
        ]);
    }

    /**
     * Indicate that the user is a manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => TenantUser::ROLE_MANAGER,
            'permissions' => [
                'products.manage',
                'orders.manage',
                'invoices.manage',
                'reports.view'
            ]
        ]);
    }

    /**
     * Indicate that the user is staff.
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => TenantUser::ROLE_STAFF,
            'permissions' => [
                'products.view',
                'orders.create',
                'orders.view',
                'invoices.create',
                'invoices.view'
            ]
        ]);
    }

    /**
     * Indicate that the user is a viewer.
     */
    public function viewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => TenantUser::ROLE_VIEWER,
            'permissions' => [
                'products.view',
                'orders.view',
                'invoices.view'
            ]
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_access_at' => $this->faker->dateTimeBetween('-6 months', '-1 month')
        ]);
    }

    /**
     * Indicate that the user has pending invitation.
     */
    public function pendingInvitation(): static
    {
        return $this->state(fn (array $attributes) => [
            'invitation_status' => TenantUser::INVITATION_PENDING,
            'invitation_token' => $this->faker->sha256(),
            'invitation_sent_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'invitation_expires_at' => $this->faker->dateTimeBetween('now', '+7 days'),
            'is_active' => false,
            'joined_at' => null,
            'approved_at' => null
        ]);
    }

    /**
     * Indicate that the user has expired access.
     */
    public function expiredAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_expires_at' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'is_active' => false
        ]);
    }

    /**
     * Get random permissions based on role
     */
    private function getRandomPermissions(): array
    {
        $allPermissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage',
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.manage',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.manage',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.manage',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete', 'customers.manage',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete', 'suppliers.manage',
            'inventory.view', 'inventory.manage',
            'reports.view', 'reports.export',
            'settings.view', 'settings.manage',
            'branches.view', 'branches.manage'
        ];

        return $this->faker->randomElements($allPermissions, $this->faker->numberBetween(3, 10));
    }

    /**
     * Get random restrictions
     */
    private function getRandomRestrictions(): array
    {
        $possibleRestrictions = [
            'no_delete_permissions',
            'limited_branch_access',
            'read_only_reports',
            'no_export_permissions',
            'limited_time_access'
        ];

        return $this->faker->optional(30)->randomElements($possibleRestrictions, $this->faker->numberBetween(0, 2)) ?? [];
    }
}
