<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetUserRootCommand extends Command
{
    protected $signature = 'user:set-root {email=yukimart@gmail.com}';
    protected $description = 'Set user as root admin';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('🔧 Setting user as root: ' . $email);

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('User not found: ' . $email);
            return;
        }

        $user->update(['is_root' => 1]);
        
        $this->info('✅ User set as root successfully!');
        $this->line('User: ' . $user->full_name);
        $this->line('Email: ' . $user->email);
        $this->line('Is Root: ' . ($user->is_root ? 'Yes' : 'No'));
    }
}
