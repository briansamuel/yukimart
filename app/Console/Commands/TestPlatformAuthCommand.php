<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestPlatformAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:test-platform-auth {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test platform authentication guard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("🧪 Testing Platform Authentication Guard");
        $this->info("Email: {$email}");
        $this->info("=".str_repeat("=", 50));

        // Test platform guard
        $credentials = ['email' => $email, 'password' => $password];
        
        if (Auth::guard('platform')->attempt($credentials)) {
            $user = Auth::guard('platform')->user();
            $this->info("✅ Platform authentication SUCCESS!");
            $this->info("   User: {$user->full_name}");
            $this->info("   Email: {$user->email}");
            $this->info("   Tenant ID: " . ($user->tenant_id ?? 'NULL (Platform User)'));
            
            // Test roles
            $roles = $user->roles()->whereNull('tenant_id')->get();
            $this->info("   Platform Roles: " . $roles->pluck('name')->implode(', '));
            
            // Logout
            Auth::guard('platform')->logout();
            $this->info("✅ Logout successful");
            
            return 0;
        } else {
            $this->error("❌ Platform authentication FAILED!");
            return 1;
        }
    }
}
