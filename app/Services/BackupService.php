<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\BackupSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class BackupService
{
    protected $backupPath = 'backups';

    public function __construct()
    {
        // Ensure backup directory exists
        if (!Storage::disk('local')->exists($this->backupPath)) {
            Storage::disk('local')->makeDirectory($this->backupPath);
        }
    }

    /**
     * Create manual backup
     */
    public function createManualBackup($name, $description = null, $tables = null)
    {
        $backup = Backup::create([
            'name' => $name,
            'filename' => $this->generateFilename($name),
            'path' => '',
            'type' => 'manual',
            'status' => 'pending',
            'tables' => $tables,
            'description' => $description,
            'created_by' => Auth::id(),
            'started_at' => now()
        ]);

        try {
            $this->executeBackup($backup);
            return ['success' => true, 'backup' => $backup];
        } catch (Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);
            
            Log::error('Backup failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create automatic backup from schedule
     */
    public function createAutoBackup(BackupSchedule $schedule)
    {
        $backup = Backup::create([
            'name' => $schedule->name . ' - ' . now()->format('Y-m-d H:i:s'),
            'filename' => $this->generateFilename($schedule->name),
            'path' => '',
            'type' => 'auto',
            'status' => 'pending',
            'tables' => $schedule->tables,
            'description' => "Tự động từ lịch: {$schedule->name}",
            'schedule_id' => $schedule->id,
            'created_by' => $schedule->created_by,
            'started_at' => now()
        ]);

        try {
            $this->executeBackup($backup);
            $schedule->markAsRun();
            return ['success' => true, 'backup' => $backup];
        } catch (Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);
            
            Log::error('Auto backup failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Execute backup process
     */
    protected function executeBackup(Backup $backup)
    {
        $backup->update(['status' => 'running']);

        $filename = $backup->filename;
        $filePath = $this->backupPath . '/' . $filename;
        $fullPath = Storage::disk('local')->path($filePath);

        // Create backup directory if not exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Get database configuration
        $database = config('database.default');
        $config = config("database.connections.{$database}");

        // Build mysqldump command
        $command = $this->buildMysqldumpCommand($config, $fullPath, $backup->tables);

        // Execute backup
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception('Backup command failed with return code: ' . $returnCode . '. Output: ' . implode("\n", $output));
        }

        // Check if file was created and get size
        if (!file_exists($fullPath)) {
            throw new Exception('Backup file was not created');
        }

        $fileSize = filesize($fullPath);

        // Update backup record
        $backup->update([
            'path' => $filePath,
            'status' => 'completed',
            'file_size' => $fileSize,
            'completed_at' => now()
        ]);

        Log::info("Backup completed successfully: {$backup->name}");
    }

    /**
     * Build mysqldump command
     */
    protected function buildMysqldumpCommand($config, $filePath, $tables = null)
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = "mysqldump";
        $command .= " --host={$host}";
        $command .= " --port={$port}";
        $command .= " --user={$username}";
        
        if ($password) {
            $command .= " --password=" . escapeshellarg($password);
        }

        $command .= " --single-transaction";
        $command .= " --routines";
        $command .= " --triggers";
        $command .= " --add-drop-table";
        $command .= " --extended-insert";
        $command .= " --create-options";

        $command .= " " . escapeshellarg($database);

        // Add specific tables if provided
        if ($tables && is_array($tables) && count($tables) > 0) {
            foreach ($tables as $table) {
                $command .= " " . escapeshellarg($table);
            }
        }

        $command .= " > " . escapeshellarg($filePath);

        return $command;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename($name)
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "{$safeName}_{$timestamp}.sql";
    }

    /**
     * Get all database tables
     */
    public function getAllTables()
    {
        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.' . config('database.default') . '.database');
        $tableKey = "Tables_in_{$database}";

        return collect($tables)->pluck($tableKey)->toArray();
    }

    /**
     * Restore backup
     */
    public function restoreBackup(Backup $backup)
    {
        if (!$backup->fileExists()) {
            throw new Exception('Backup file not found');
        }

        $config = config("database.connections." . config('database.default'));
        $filePath = $backup->full_path;

        $command = $this->buildMysqlRestoreCommand($config, $filePath);

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception('Restore command failed with return code: ' . $returnCode . '. Output: ' . implode("\n", $output));
        }

        Log::info("Backup restored successfully: {$backup->name}");
        return true;
    }

    /**
     * Build mysql restore command
     */
    protected function buildMysqlRestoreCommand($config, $filePath)
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = "mysql";
        $command .= " --host={$host}";
        $command .= " --port={$port}";
        $command .= " --user={$username}";
        
        if ($password) {
            $command .= " --password=" . escapeshellarg($password);
        }

        $command .= " " . escapeshellarg($database);
        $command .= " < " . escapeshellarg($filePath);

        return $command;
    }

    /**
     * Delete old backups based on retention policy
     */
    public function cleanupOldBackups($retentionDays = 30)
    {
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        $oldBackups = Backup::where('created_at', '<', $cutoffDate)
                           ->where('status', 'completed')
                           ->get();

        $deletedCount = 0;
        foreach ($oldBackups as $backup) {
            try {
                $backup->deleteFile();
                $backup->delete();
                $deletedCount++;
            } catch (Exception $e) {
                Log::error("Failed to delete backup {$backup->id}: " . $e->getMessage());
            }
        }

        Log::info("Cleaned up {$deletedCount} old backups");
        return $deletedCount;
    }

    /**
     * Run scheduled backups
     */
    public function runScheduledBackups()
    {
        $schedules = BackupSchedule::shouldRun()->get();
        $results = [];

        foreach ($schedules as $schedule) {
            $result = $this->createAutoBackup($schedule);
            $results[] = [
                'schedule' => $schedule->name,
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Success'
            ];

            // Clean up old backups for this schedule
            if ($result['success']) {
                $this->cleanupOldBackups($schedule->retention_days);
            }
        }

        return $results;
    }
}
