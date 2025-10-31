<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Auth\PlatformUserProvider;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestPlatformProviderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:test-platform-provider {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test platform user provider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info("🧪 Testing Platform User Provider");
        $this->info("Email: {$email}");
        $this->info("=".str_repeat("=", 50));

        // 1. Test direct User model query
        $this->info("1. Direct User Model Query:");
        $user = User::whereNull('tenant_id')->where('email', $email)->first();
        if ($user) {
            $this->info("   ✅ User found: {$user->full_name}");
            $this->info("   📧 Email: {$user->email}");
            $this->info("   🆔 ID: {$user->id}");
            $this->info("   🏢 Tenant ID: " . ($user->tenant_id ?? 'NULL'));
        } else {
            $this->error("   ❌ User not found in direct query");
            return 1;
        }

        // 2. Test PlatformUserProvider
        $this->info("\n2. Platform User Provider Test:");
        try {
            $provider = new PlatformUserProvider(app('hash'), User::class);
            
            // Test retrieveByCredentials
            $credentials = ['email' => $email];
            $foundUser = $provider->retrieveByCredentials($credentials);
            
            if ($foundUser) {
                $this->info("   ✅ Provider found user: {$foundUser->full_name}");
                $this->info("   📧 Email: {$foundUser->email}");
                $this->info("   🆔 ID: {$foundUser->id}");
                $this->info("   🏢 Tenant ID: " . ($foundUser->tenant_id ?? 'NULL'));
            } else {
                $this->error("   ❌ Provider did not find user");
            }

            // Test validateCredentials
            if ($foundUser) {
                $isValid = $provider->validateCredentials($foundUser, ['email' => $email, 'password' => '123456']);
                $this->info("   🔐 Password validation: " . ($isValid ? '✅ Valid' : '❌ Invalid'));
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Provider error: " . $e->getMessage());
        }

        // 3. Test Auth Guard
        $this->info("\n3. Auth Guard Test:");
        try {
            $guard = Auth::guard('platform');
            $this->info("   📋 Guard: " . get_class($guard));
            $this->info("   📋 Provider: " . get_class($guard->getProvider()));
            
            // Test attempt
            $credentials = ['email' => $email, 'password' => '123456'];
            $result = $guard->attempt($credentials);
            $this->info("   🔐 Auth attempt: " . ($result ? '✅ Success' : '❌ Failed'));
            
            if ($result) {
                $authUser = $guard->user();
                $this->info("   👤 Authenticated user: {$authUser->full_name}");
                $guard->logout();
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Guard error: " . $e->getMessage());
        }

        // 4. Test config
        $this->info("\n4. Configuration Test:");
        $guards = config('auth.guards');
        $providers = config('auth.providers');
        
        $this->info("   📋 Platform guard config:");
        if (isset($guards['platform'])) {
            $this->info("      Driver: " . $guards['platform']['driver']);
            $this->info("      Provider: " . $guards['platform']['provider']);
        } else {
            $this->error("      ❌ Platform guard not configured");
        }
        
        $this->info("   📋 Platform provider config:");
        if (isset($providers['platform'])) {
            $this->info("      Driver: " . $providers['platform']['driver']);
            $this->info("      Model: " . $providers['platform']['model']);
        } else {
            $this->error("      ❌ Platform provider not configured");
        }

        return 0;
    }
}
