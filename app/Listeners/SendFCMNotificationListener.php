<?php

namespace App\Listeners;

use App\Jobs\SendFCMNotificationJob;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendFCMNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event)
    {
        try {
            // Check if this is a notification created event
            if (isset($event->notification) && $event->notification instanceof Notification) {
                $notification = $event->notification;
            } elseif (isset($event->model) && $event->model instanceof Notification) {
                $notification = $event->model;
            } else {
                return; // Not a notification event
            }

            // Check if notification should be sent via FCM
            $channels = $notification->channels ?? [];
            if (!in_array('push', $channels) && !in_array('fcm', $channels)) {
                return; // FCM not enabled for this notification
            }

            // Dispatch FCM job
            SendFCMNotificationJob::dispatch($notification->id)
                ->delay(now()->addSeconds(5)); // Small delay to ensure notification is saved

            Log::info('FCM notification job dispatched', [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to dispatch FCM notification job', [
                'error' => $e->getMessage(),
                'event' => get_class($event)
            ]);
        }
    }
}
