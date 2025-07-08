<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BackupSchedule;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RunScheduledBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run-scheduled
                            {--schedule-id= : Run specific schedule ID only}
                            {--dry-run : Show what would be backed up without actually running}
                            {--force : Force run even if not due}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled backup tasks that are due';

    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Checking for scheduled backups...');

        $schedules = $this->getSchedulesToRun();

        if ($schedules->isEmpty()) {
            $this->info('✅ No scheduled backups are due at this time.');
            return 0;
        }

        $this->info("📋 Found {$schedules->count()} scheduled backup(s) to run:");

        foreach ($schedules as $schedule) {
            $this->runSchedule($schedule);
        }

        $this->info('🎉 Scheduled backup check completed!');
        return 0;
    }

    /**
     * Get schedules that should run now
     */
    private function getSchedulesToRun()
    {
        $query = BackupSchedule::where('is_active', true);

        if ($this->option('schedule-id')) {
            $query->where('id', $this->option('schedule-id'));
        }

        if (!$this->option('force')) {
            $now = Carbon::now();
            $query->where(function($q) use ($now) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', $now);
            });
        }

        return $query->get();
    }

    /**
     * Run a specific schedule
     */
    private function runSchedule(BackupSchedule $schedule)
    {
        $this->line("🔄 Running: {$schedule->name} ({$schedule->frequency_label})");

        if ($this->option('dry-run')) {
            $this->line("   📝 Dry run - would backup: " . ($schedule->tables ? implode(', ', $schedule->tables) : 'All tables'));
            return;
        }

        try {
            // Create backup
            $result = $this->backupService->createAutoBackup($schedule);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $backup = $result['backup'];

            // Update schedule
            $schedule->update([
                'last_run_at' => Carbon::now(),
                'next_run_at' => $schedule->calculateNextRun()
            ]);

            $this->info("   ✅ Backup created successfully: {$backup->filename}");

            Log::info("Scheduled backup completed", [
                'schedule_id' => $schedule->id,
                'schedule_name' => $schedule->name,
                'backup_id' => $backup->id,
                'backup_filename' => $backup->filename
            ]);

        } catch (\Exception $e) {
            $this->error("   ❌ Backup failed: {$e->getMessage()}");

            Log::error("Scheduled backup failed", [
                'schedule_id' => $schedule->id,
                'schedule_name' => $schedule->name,
                'error' => $e->getMessage()
            ]);
        }
    }
}
