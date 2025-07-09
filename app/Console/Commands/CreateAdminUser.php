<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Create or update user
        $user = \App\Models\User::updateOrCreate(
            ['email' => $email],
            [
                'username' => 'admin',
                'full_name' => 'Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'phone' => '0123456789',
                'address' => 'Admin Address',
                'active_code' => '',
                'status' => 'active'
            ]
        );

        // Assign super_admin role
        $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $user->roles()->sync([$superAdminRole->id]);
        }

        $this->info("Admin user created: {$email}");
        return Command::SUCCESS;
    }
}
