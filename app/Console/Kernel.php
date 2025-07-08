<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Shopee order sync - every 15 minutes if enabled (using jobs)
        if (config('shopee.sync.enabled') && config('shopee.features.order_sync')) {
            $schedule->job(new \App\Jobs\SyncShopeeOrdersJob())
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->onFailure(function () {
                    Log::error('Shopee order sync scheduled job failed');
                });
        }

        // Shopee inventory sync - every hour if enabled (using jobs)
        if (config('shopee.sync.enabled') && config('shopee.features.inventory_sync')) {
            $schedule->job(new \App\Jobs\SyncShopeeInventoryJob())
                ->hourly()
                ->withoutOverlapping()
                ->onFailure(function () {
                    Log::error('Shopee inventory sync scheduled job failed');
                });
        }

        // Check Shopee tokens - twice daily with auto-refresh (using jobs)
        $schedule->job(new \App\Jobs\CheckShopeeTokensJob(true, true))
            ->twiceDaily(9, 21) // 9 AM and 9 PM
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('Shopee token check scheduled job failed');
            });

        // Fallback command-based scheduling (if jobs are not preferred)
        // Shopee order sync - every 15 minutes if enabled
        if (config('shopee.sync.enabled') && config('shopee.features.order_sync') && !config('shopee.sync.use_jobs', true)) {
            $schedule->command('shopee:sync-orders')
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->runInBackground()
                ->onFailure(function () {
                    Log::error('Shopee order sync scheduled task failed');
                });
        }

        // Shopee inventory sync - every hour if enabled
        if (config('shopee.sync.enabled') && config('shopee.features.inventory_sync') && !config('shopee.sync.use_jobs', true)) {
            $schedule->command('shopee:sync-inventory')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground()
                ->onFailure(function () {
                    Log::error('Shopee inventory sync scheduled task failed');
                });
        }

        // Check Shopee tokens - twice daily with auto-refresh
        if (!config('shopee.sync.use_jobs', true)) {
            $schedule->command('shopee:check-tokens --refresh --notify')
                ->twiceDaily(9, 21) // 9 AM and 9 PM
                ->withoutOverlapping()
                ->onFailure(function () {
                    Log::error('Shopee token check scheduled task failed');
                });
        }

        // Backup scheduler - check every minute for due backups
        $schedule->command('backup:run-scheduled')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                Log::error('Scheduled backup task failed');
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
