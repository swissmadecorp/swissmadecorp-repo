<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseChannel extends Notification
{
    use Queueable;

    protected $messaging;

    /**
     * Create a new notification instance.
     */
    public function __construct(Factory $factory)
    {
        $credentialsPath = config('services.firebase.credentials');
        if (! $credentialsPath) {
            throw new \Exception('Firebase credentials path not configured.');
        }

        $this->messaging = $factory->withServiceAccount($credentialsPath)->createMessaging();
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // *** MODIFIED: Use the new fcm_device_token column ***
        if (! $notifiable->fcm_device_token) {
            Log::warning("FCM: Not sending to user {$notifiable->id} as FCM device token is missing.");
            return;
        }

        $message = $notification->toFirebase($notifiable);
        try {
            // *** Pass the new fcm_device_token to the messaging service ***
            $this->messaging->send($message);
            Log::info("FCM: Message sent to user {$notifiable->id} via FirebaseChannel.");
        } catch (\Throwable $e) {
            Log::error("FCM: Failed to send message to user {$notifiable->id}: " . $e->getMessage());
        }
    }
}
