<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BackupSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'frequency',
        'time',
        'day_of_week',
        'day_of_month',
        'hour_interval',
        'tables',
        'is_active',
        'retention_days',
        'last_run_at',
        'next_run_at',
        'description',
        'created_by'
    ];

    protected $casts = [
        'tables' => 'array',
        'is_active' => 'boolean',
        'time' => 'datetime:H:i',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'retention_days' => 'integer',
        'hour_interval' => 'integer',
        'day_of_week' => 'integer',
        'day_of_month' => 'integer'
    ];

    /**
     * Relationship with User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with Backups
     */
    public function backups()
    {
        return $this->hasMany(Backup::class, 'schedule_id');
    }

    /**
     * Get frequency label
     */
    public function getFrequencyLabelAttribute()
    {
        return match($this->frequency) {
            'hourly' => 'Hàng giờ',
            'daily' => 'Hàng ngày',
            'weekly' => 'Hàng tuần',
            'monthly' => 'Hàng tháng',
            default => 'Không xác định'
        };
    }

    /**
     * Get day of week label
     */
    public function getDayOfWeekLabelAttribute()
    {
        if ($this->day_of_week === null) {
            return null;
        }

        $days = [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy'
        ];

        return $days[$this->day_of_week] ?? 'Không xác định';
    }

    /**
     * Get schedule description
     */
    public function getScheduleDescriptionAttribute()
    {
        switch ($this->frequency) {
            case 'hourly':
                return "Mỗi {$this->hour_interval} giờ";
            
            case 'daily':
                return "Hàng ngày lúc " . $this->time->format('H:i');
            
            case 'weekly':
                return "{$this->day_of_week_label} hàng tuần lúc " . $this->time->format('H:i');
            
            case 'monthly':
                return "Ngày {$this->day_of_month} hàng tháng lúc " . $this->time->format('H:i');
            
            default:
                return 'Không xác định';
        }
    }



    /**
     * Check if schedule should run now
     */
    public function shouldRun()
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->next_run_at) {
            $this->calculateNextRun();
            return false;
        }

        return $this->next_run_at->isPast();
    }

    /**
     * Mark as run and calculate next run
     */
    public function markAsRun()
    {
        $this->update(['last_run_at' => now()]);
        $this->calculateNextRun();
    }

    /**
     * Scope for active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for schedules that should run
     */
    public function scopeShouldRun($query)
    {
        return $query->active()
                    ->where('next_run_at', '<=', now())
                    ->whereNotNull('next_run_at');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'badge-success' : 'badge-secondary';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Hoạt động' : 'Tạm dừng';
    }

    /**
     * Calculate next run time based on frequency
     */
    public function calculateNextRun()
    {
        $now = Carbon::now();

        switch ($this->frequency) {
            case 'hourly':
                return $now->addHour();

            case 'daily':
                $nextRun = $now->copy()->addDay();
                if ($this->time) {
                    [$hour, $minute] = explode(':', $this->time);
                    $nextRun->setTime($hour, $minute, 0);
                }
                return $nextRun;

            case 'weekly':
                $nextRun = $now->copy()->addWeek();
                if ($this->day_of_week !== null) {
                    $nextRun->startOfWeek()->addDays($this->day_of_week);
                }
                if ($this->time) {
                    [$hour, $minute] = explode(':', $this->time);
                    $nextRun->setTime($hour, $minute, 0);
                }
                return $nextRun;

            case 'monthly':
                $nextRun = $now->copy()->addMonth();
                if ($this->day_of_month) {
                    $nextRun->day($this->day_of_month);
                }
                if ($this->time) {
                    [$hour, $minute] = explode(':', $this->time);
                    $nextRun->setTime($hour, $minute, 0);
                }
                return $nextRun;

            default:
                return $now->addDay();
        }
    }
}
