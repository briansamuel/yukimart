<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display backup management page
     */
    public function index()
    {
        $backups = Backup::with('creator')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        $schedules = BackupSchedule::with('creator')
                                  ->orderBy('created_at', 'desc')
                                  ->get();

        $tables = $this->backupService->getAllTables();

        return view('admin.backup.index', compact('backups', 'schedules', 'tables'));
    }

    /**
     * Create manual backup
     */
    public function createManual(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'backup_type' => 'required|in:full,selective',
            'tables' => 'nullable|array',
            'tables.*' => 'string'
        ]);

        try {
            $tables = $request->backup_type === 'selective' ? $request->tables : null;
            
            $result = $this->backupService->createManualBackup(
                $request->name,
                $request->description,
                $tables
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup được tạo thành công!',
                    'backup' => $result['backup']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Manual backup failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup progress (for AJAX polling)
     */
    public function getProgress($id)
    {
        $backup = Backup::findOrFail($id);
        
        return response()->json([
            'status' => $backup->status,
            'progress' => $this->calculateProgress($backup),
            'message' => $this->getProgressMessage($backup)
        ]);
    }

    /**
     * Calculate backup progress percentage
     */
    protected function calculateProgress(Backup $backup)
    {
        switch ($backup->status) {
            case 'pending':
                return 0;
            case 'running':
                // Estimate progress based on time elapsed
                if ($backup->started_at) {
                    $elapsed = now()->diffInSeconds($backup->started_at);
                    // Assume backup takes about 30 seconds on average
                    $estimated = min(90, ($elapsed / 30) * 100);
                    return $estimated;
                }
                return 10;
            case 'completed':
                return 100;
            case 'failed':
                return 0;
            default:
                return 0;
        }
    }

    /**
     * Get progress message
     */
    protected function getProgressMessage(Backup $backup)
    {
        switch ($backup->status) {
            case 'pending':
                return 'Đang chuẩn bị backup...';
            case 'running':
                return 'Đang thực hiện backup...';
            case 'completed':
                return 'Backup hoàn thành thành công!';
            case 'failed':
                return 'Backup thất bại: ' . ($backup->error_message ?? 'Lỗi không xác định');
            default:
                return 'Trạng thái không xác định';
        }
    }

    /**
     * Download backup file
     */
    public function download($id)
    {
        $backup = Backup::findOrFail($id);

        if (!$backup->fileExists()) {
            return back()->with('error', 'File backup không tồn tại!');
        }

        return response()->download($backup->full_path, $backup->filename);
    }

    /**
     * Delete backup
     */
    public function delete($id)
    {
        try {
            $backup = Backup::findOrFail($id);
            $backup->deleteFile();
            $backup->delete();

            return response()->json([
                'success' => true,
                'message' => 'Backup đã được xóa thành công!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore backup
     */
    public function restore($id)
    {
        try {
            $backup = Backup::findOrFail($id);
            $this->backupService->restoreBackup($backup);

            return response()->json([
                'success' => true,
                'message' => 'Khôi phục dữ liệu thành công!'
            ]);
        } catch (Exception $e) {
            Log::error('Backup restore failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create backup schedule
     */
    public function createSchedule(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:hourly,daily,weekly,monthly',
            'time' => 'nullable|date_format:H:i',
            'day_of_week' => 'nullable|integer|between:0,6',
            'day_of_month' => 'nullable|integer|between:1,31',
            'hour_interval' => 'nullable|integer|min:1|max:24',
            'retention_days' => 'required|integer|min:1|max:365',
            'backup_type' => 'required|in:full,selective',
            'tables' => 'nullable|array',
            'description' => 'nullable|string'
        ]);

        try {
            $tables = $request->backup_type === 'selective' ? $request->tables : null;

            $schedule = BackupSchedule::create([
                'name' => $request->name,
                'frequency' => $request->frequency,
                'time' => $request->time,
                'day_of_week' => $request->day_of_week,
                'day_of_month' => $request->day_of_month,
                'hour_interval' => $request->hour_interval,
                'tables' => $tables,
                'retention_days' => $request->retention_days,
                'description' => $request->description,
                'created_by' => Auth::id()
            ]);

            // Calculate first run time
            $schedule->calculateNextRun();

            return response()->json([
                'success' => true,
                'message' => 'Lịch backup đã được tạo thành công!',
                'schedule' => $schedule
            ]);
        } catch (Exception $e) {
            Log::error('Schedule creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo lịch backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update schedule status
     */
    public function toggleSchedule($id)
    {
        try {
            $schedule = BackupSchedule::findOrFail($id);
            $schedule->update(['is_active' => !$schedule->is_active]);

            if ($schedule->is_active) {
                $schedule->calculateNextRun();
            }

            return response()->json([
                'success' => true,
                'message' => $schedule->is_active ? 'Lịch backup đã được kích hoạt!' : 'Lịch backup đã được tạm dừng!',
                'is_active' => $schedule->is_active
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete schedule
     */
    public function deleteSchedule($id)
    {
        try {
            $schedule = BackupSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lịch backup đã được xóa thành công!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa lịch backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup statistics
     */
    public function getStats()
    {
        $stats = [
            'total_backups' => Backup::count(),
            'completed_backups' => Backup::completed()->count(),
            'failed_backups' => Backup::failed()->count(),
            'running_backups' => Backup::running()->count(),
            'total_size' => Backup::completed()->sum('file_size'),
            'active_schedules' => BackupSchedule::active()->count(),
            'last_backup' => Backup::completed()->latest()->first()
        ];

        return response()->json($stats);
    }
}
