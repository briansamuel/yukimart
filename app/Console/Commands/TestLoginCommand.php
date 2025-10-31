<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Auth\AuthService;
use App\Repositories\User\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:test-login {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test login functionality for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("🧪 Testing Login for: {$email}");
        $this->info("=".str_repeat("=", 50));

        // 1. Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }

        $this->info("✅ User found: {$user->full_name} (ID: {$user->id})");

        // 2. Check password
        if (Hash::check($password, $user->password)) {
            $this->info("✅ Password is correct");
        } else {
            $this->error("❌ Password is incorrect");
            return 1;
        }

        // 3. Test AuthService
        try {
            $userRepo = new UserRepository();
            $authService = new AuthService($userRepo);
            $result = $authService->login($email, $password);

            $this->info("🔐 AuthService Result:");
            $this->info("   Status: " . ($result['status'] ? '✅ Success' : '❌ Failed'));
            $this->info("   Message: " . $result['msg']);

            if ($result['status']) {
                $this->info("🎉 Login test PASSED!");
                return 0;
            } else {
                $this->error("❌ Login test FAILED!");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception during login test: " . $e->getMessage());
            return 1;
        }
    }
}
