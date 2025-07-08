<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class NotificationService
{
    /**
     * Send notification to user.
     */
    public function sendToUser($user, $type, $title, $message, $data = [], $options = [])
    {
        try {
            // Check if user has this notification type enabled
            if (!$this->isNotificationEnabled($user, $type)) {
                Log::info('Notification disabled for user', ['user_id' => $user->id, 'type' => $type]);
                return ['success' => true, 'message' => 'Notification disabled for user'];
            }

            // Get user's preferred channels for this notification type
            $channels = $this->getUserChannels($user, $type, $options['channels'] ?? ['web']);

            // Create notification
            $notification = Notification::createForUser($user, $type, $title, $message, $data, [
                'priority' => $options['priority'] ?? 'normal',
                'channels' => $channels,
                'expires_at' => $options['expires_at'] ?? null,
            ]);

            // Send via each channel
            foreach ($channels as $channel) {
                $this->sendViaChannel($notification, $channel);
            }

            // Clear unread count cache
            $this->clearUnreadCountCache($user->id);

            Log::info('Notification sent successfully', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'type' => $type,
                'channels' => $channels
            ]);

            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'data' => $notification
            ];

        } catch (Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToUsers($users, $type, $title, $message, $data = [], $options = [])
    {
        $results = [];
        
        foreach ($users as $user) {
            $results[] = $this->sendToUser($user, $type, $title, $message, $data, $options);
        }

        return $results;
    }

    /**
     * Send notification to all admins.
     */
    public function sendToAdmins($type, $title, $message, $data = [], $options = [])
    {
        $admins = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->get();
        //$admins = User::where('is_root', 1)->get();

        return $this->sendToUsers($admins, $type, $title, $message, $data, $options);
    }

    /**
     * Send via specific channel.
     */
    protected function sendViaChannel($notification, $channel)
    {
        try {
            switch ($channel) {
                case 'web':
                    // Web notifications are already stored in database
                    // Trigger real-time notification if needed
                    $this->sendWebNotification($notification);
                    break;
                    
                case 'email':
                    $this->sendEmailNotification($notification);
                    break;
                    
                case 'sms':
                    $this->sendSMSNotification($notification);
                    break;
                    
                default:
                    Log::warning('Unknown notification channel', ['channel' => $channel]);
            }
        } catch (Exception $e) {
            Log::error('Failed to send notification via channel', [
                'notification_id' => $notification->id,
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send web notification (real-time).
     */
    protected function sendWebNotification($notification)
    {
        // TODO: Implement WebSocket/Pusher broadcasting
        // broadcast(new NotificationSent($notification))->toOthers();
        Log::info('Web notification sent', ['notification_id' => $notification->id]);
    }

    /**
     * Send email notification.
     */
    protected function sendEmailNotification($notification)
    {
        // TODO: Implement email sending
        // Mail::to($notification->notifiable)->send(new NotificationMail($notification));
        Log::info('Email notification sent', ['notification_id' => $notification->id]);
    }

    /**
     * Send SMS notification.
     */
    protected function sendSMSNotification($notification)
    {
        // TODO: Implement SMS sending
        Log::info('SMS notification sent', ['notification_id' => $notification->id]);
    }

    /**
     * Check if notification is enabled for user.
     */
    protected function isNotificationEnabled($user, $type)
    {
        $setting = NotificationSetting::where('user_id', $user->id)
                                     ->where('notification_type', $type)
                                     ->first();

        if (!$setting) {
            // Default to enabled if no setting exists
            return true;
        }

        return $setting->is_enabled;
    }

    /**
     * Get user's preferred channels for notification type.
     */
    protected function getUserChannels($user, $type, $defaultChannels = ['web'])
    {
        $setting = NotificationSetting::where('user_id', $user->id)
                                     ->where('notification_type', $type)
                                     ->first();

        if (!$setting) {
            return $defaultChannels;
        }

        return $setting->channels ?? $defaultChannels;
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($notificationId, $userId = null)
    {
        try {
            $query = Notification::where('id', $notificationId);
            
            if ($userId) {
                $query->forUser($userId);
            }

            $notification = $query->first();
            
            if (!$notification) {
                return ['success' => false, 'message' => 'Notification not found'];
            }

            $notification->markAsRead();
            
            // Clear unread count cache
            if ($notification->notifiable_type === User::class) {
                $this->clearUnreadCountCache($notification->notifiable_id);
            }

            return ['success' => true, 'message' => 'Notification marked as read'];

        } catch (Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Failed to mark notification as read'];
        }
    }

    /**
     * Mark all notifications as read for user.
     */
    public function markAllAsRead($userId)
    {
        try {
            $count = Notification::forUser($userId)
                                ->unread()
                                ->update(['read_at' => now()]);

            // Clear unread count cache
            $this->clearUnreadCountCache($userId);

            return [
                'success' => true,
                'message' => "Marked {$count} notifications as read",
                'count' => $count
            ];

        } catch (Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Failed to mark notifications as read'];
        }
    }

    /**
     * Get unread count for user.
     */
    public function getUnreadCount($userId)
    {
        $cacheKey = "user_unread_notifications_{$userId}";
        
        return Cache::remember($cacheKey, 300, function() use ($userId) {
            return Notification::forUser($userId)->unread()->active()->count();
        });
    }

    /**
     * Clear unread count cache.
     */
    protected function clearUnreadCountCache($userId)
    {
        Cache::forget("user_unread_notifications_{$userId}");
    }

    /**
     * Get user notifications with pagination.
     */
    public function getUserNotifications($userId, $options = [])
    {
        $query = Notification::forUser($userId)->active();

        // Apply filters
        if (isset($options['type'])) {
            $query->ofType($options['type']);
        }

        if (isset($options['unread_only']) && $options['unread_only']) {
            $query->unread();
        }

        // Order by priority and creation date
        $query->orderByRaw("
            CASE priority 
                WHEN 'urgent' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'normal' THEN 3 
                WHEN 'low' THEN 4 
                ELSE 5 
            END, created_at DESC
        ");

        $perPage = $options['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }

    /**
     * Delete notification.
     */
    public function deleteNotification($notificationId, $userId = null)
    {
        try {
            $query = Notification::where('id', $notificationId);
            
            if ($userId) {
                $query->forUser($userId);
            }

            $notification = $query->first();
            
            if (!$notification) {
                return ['success' => false, 'message' => 'Notification not found'];
            }

            $notification->delete();
            
            // Clear unread count cache
            if ($notification->notifiable_type === User::class) {
                $this->clearUnreadCountCache($notification->notifiable_id);
            }

            return ['success' => true, 'message' => 'Notification deleted'];

        } catch (Exception $e) {
            Log::error('Failed to delete notification', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Failed to delete notification'];
        }
    }

    /**
     * Clean up expired notifications.
     */
    public function cleanupExpired()
    {
        try {
            $count = Notification::cleanupExpired();
            
            Log::info('Cleaned up expired notifications', ['count' => $count]);
            
            return ['success' => true, 'message' => "Cleaned up {$count} expired notifications"];

        } catch (Exception $e) {
            Log::error('Failed to cleanup expired notifications', ['error' => $e->getMessage()]);
            
            return ['success' => false, 'message' => 'Failed to cleanup expired notifications'];
        }
    }

    /**
     * Clean up old notifications.
     */
    public function cleanupOld($days = 30)
    {
        try {
            $count = Notification::cleanupOld($days);
            
            Log::info('Cleaned up old notifications', ['count' => $count, 'days' => $days]);
            
            return ['success' => true, 'message' => "Cleaned up {$count} old notifications"];

        } catch (Exception $e) {
            Log::error('Failed to cleanup old notifications', ['error' => $e->getMessage()]);
            
            return ['success' => false, 'message' => 'Failed to cleanup old notifications'];
        }
    }
}
