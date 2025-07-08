<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait UserTimeStamp
{
    /**
     * Boot the trait.
     */
    protected static function bootUserTimeStamp()
    {
        // Set created_by when creating
        static::creating(function (Model $model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        // Set updated_by when updating
        static::updating(function (Model $model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        // Set deleted_by when soft deleting
        static::deleting(function (Model $model) {
            if (Auth::check() && method_exists($model, 'trashed')) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this record.
     */
    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    /**
     * Scope to filter by creator.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to filter by updater.
     */
    public function scopeUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    /**
     * Scope to filter by deleter.
     */
    public function scopeDeletedBy($query, $userId)
    {
        return $query->where('deleted_by', $userId);
    }

    /**
     * Get formatted created by information.
     */
    public function getCreatedByInfoAttribute()
    {
        if ($this->creator) {
            return [
                'user' => $this->creator->name,
                'email' => $this->creator->email,
                'date' => $this->created_at?->format('d/m/Y H:i'),
            ];
        }
        return null;
    }

    /**
     * Get formatted updated by information.
     */
    public function getUpdatedByInfoAttribute()
    {
        if ($this->updater) {
            return [
                'user' => $this->updater->name,
                'email' => $this->updater->email,
                'date' => $this->updated_at?->format('d/m/Y H:i'),
            ];
        }
        return null;
    }

    /**
     * Get formatted deleted by information.
     */
    public function getDeletedByInfoAttribute()
    {
        if ($this->deleter && $this->deleted_at) {
            return [
                'user' => $this->deleter->name,
                'email' => $this->deleter->email,
                'date' => $this->deleted_at?->format('d/m/Y H:i'),
            ];
        }
        return null;
    }

    /**
     * Get audit trail for this record.
     */
    public function getAuditTrailAttribute()
    {
        $trail = [];

        if ($this->created_by_info) {
            $trail[] = [
                'action' => 'created',
                'user' => $this->created_by_info['user'],
                'date' => $this->created_by_info['date'],
            ];
        }

        if ($this->updated_by_info && $this->updated_at != $this->created_at) {
            $trail[] = [
                'action' => 'updated',
                'user' => $this->updated_by_info['user'],
                'date' => $this->updated_by_info['date'],
            ];
        }

        if ($this->deleted_by_info) {
            $trail[] = [
                'action' => 'deleted',
                'user' => $this->deleted_by_info['user'],
                'date' => $this->deleted_by_info['date'],
            ];
        }

        return $trail;
    }

    /**
     * Check if current user can edit this record.
     */
    public function canEdit()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Admin can edit everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Creator can edit their own records
        if ($this->created_by === $user->id) {
            return true;
        }

        // Check if user has specific permission
        if ($user->can('edit_all_' . strtolower(class_basename($this)))) {
            return true;
        }

        return false;
    }

    /**
     * Check if current user can delete this record.
     */
    public function canDelete()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Admin can delete everything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Creator can delete their own records
        if ($this->created_by === $user->id) {
            return true;
        }

        // Check if user has specific permission
        if ($user->can('delete_all_' . strtolower(class_basename($this)))) {
            return true;
        }

        return false;
    }

    /**
     * Get user activity summary.
     */
    public static function getUserActivitySummary($userId, $startDate = null, $endDate = null)
    {
        $query = static::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'created' => (clone $query)->where('created_by', $userId)->count(),
            'updated' => (clone $query)->where('updated_by', $userId)->count(),
            'deleted' => (clone $query)->where('deleted_by', $userId)->count(),
        ];
    }

    /**
     * Get recent activity for a user.
     */
    public static function getRecentActivity($userId, $limit = 10)
    {
        return static::where(function ($query) use ($userId) {
            $query->where('created_by', $userId)
                  ->orWhere('updated_by', $userId)
                  ->orWhere('deleted_by', $userId);
        })
        ->orderBy('updated_at', 'desc')
        ->limit($limit)
        ->get();
    }
}
