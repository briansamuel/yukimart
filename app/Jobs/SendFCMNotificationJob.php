<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFCMNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationId;
    
    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($notificationId)
    {
        $this->notificationId = $notificationId;
        $this->onQueue('notifications'); // Use specific queue for notifications
    }

    /**
     * Execute the job.
     */
    public function handle(FCMService $fcmService)
    {
        try {
            $notification = Notification::find($this->notificationId);
            
            if (!$notification) {
                Log::warning('FCM Job: Notification not found', ['id' => $this->notificationId]);
                return;
            }

            // Check if notification should be sent via FCM
            $channels = $notification->channels ?? [];
            if (!in_array('push', $channels) && !in_array('fcm', $channels)) {
                Log::info('FCM Job: Notification does not include push/fcm channel', ['id' => $this->notificationId]);
                return;
            }

            // Send FCM notification
            $result = $fcmService->sendFromNotification($notification);

            if ($result['success']) {
                Log::info('FCM Job: Notification sent successfully', [
                    'notification_id' => $this->notificationId,
                    'sent_count' => $result['sent_count'] ?? 0,
                    'failed_count' => $result['failed_count'] ?? 0
                ]);

                // Update notification with FCM delivery info
                $notification->update([
                    'data' => array_merge($notification->data ?? [], [
                        'fcm_sent_at' => now()->toISOString(),
                        'fcm_sent_count' => $result['sent_count'] ?? 0,
                        'fcm_failed_count' => $result['failed_count'] ?? 0
                    ])
                ]);
            } else {
                Log::error('FCM Job: Failed to send notification', [
                    'notification_id' => $this->notificationId,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);

                // Mark job as failed if this is the last attempt
                if ($this->attempts() >= $this->tries) {
                    $notification->update([
                        'data' => array_merge($notification->data ?? [], [
                            'fcm_failed_at' => now()->toISOString(),
                            'fcm_error' => $result['message'] ?? 'Unknown error'
                        ])
                    ]);
                }

                throw new \Exception($result['message'] ?? 'FCM notification failed');
            }

        } catch (\Exception $e) {
            Log::error('FCM Job: Exception occurred', [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('FCM Job: Job failed permanently', [
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update notification with failure info
        try {
            $notification = Notification::find($this->notificationId);
            if ($notification) {
                $notification->update([
                    'data' => array_merge($notification->data ?? [], [
                        'fcm_failed_permanently_at' => now()->toISOString(),
                        'fcm_final_error' => $exception->getMessage()
                    ])
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM Job: Failed to update notification with failure info', [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
