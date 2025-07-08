<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'filename',
        'path',
        'type',
        'status',
        'file_size',
        'tables',
        'description',
        'error_message',
        'started_at',
        'completed_at',
        'schedule_id',
        'created_by'
    ];

    protected $casts = [
        'tables' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer'
    ];

    /**
     * Relationship with User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with BackupSchedule
     */
    public function schedule()
    {
        return $this->belongsTo(BackupSchedule::class, 'schedule_id');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return 'N/A';
        }

        $duration = $this->completed_at->diffInSeconds($this->started_at);
        
        if ($duration < 60) {
            return $duration . ' giây';
        } elseif ($duration < 3600) {
            return round($duration / 60, 1) . ' phút';
        } else {
            return round($duration / 3600, 1) . ' giờ';
        }
    }

    /**
     * Check if backup file exists
     */
    public function fileExists()
    {
        return Storage::disk('local')->exists($this->path);
    }

    /**
     * Get full file path
     */
    public function getFullPathAttribute()
    {
        return Storage::disk('local')->path($this->path);
    }

    /**
     * Delete backup file
     */
    public function deleteFile()
    {
        if ($this->fileExists()) {
            return Storage::disk('local')->delete($this->path);
        }
        return true;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'running' => 'badge-info',
            'completed' => 'badge-success',
            'failed' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'running' => 'Đang chạy',
            'completed' => 'Hoàn thành',
            'failed' => 'Thất bại',
            default => 'Không xác định'
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'manual' => 'Thủ công',
            'auto' => 'Tự động',
            default => 'Không xác định'
        };
    }

    /**
     * Scope for completed backups
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed backups
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for running backups
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }
}
