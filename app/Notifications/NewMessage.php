<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use Kreait\Firebase\Messaging\CloudMessage; // The class for building the message
use Kreait\Firebase\Messaging\Notification as FirebaseNotification; // For notification payload

class NewMessage extends Notification
{
    // use Queueable;

    /**
     * Create a new notification instance.
     */
     function __construct(
        public $messageContent,
        public $senderName,
        public int $chatUserId,
        public ?string $imagePath = null) // *** NEW: Accept imagePath ***
    {
        \Log::info("NewMessage notification created for user {$chatUserId} with content: {$messageContent}");
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [
            ApnChannel::class,
            'firebase', // Use the custom FirebaseChannel
        ];
    }

    // New method that your custom FirebaseChannel will call
    public function toFirebase($notifiable): CloudMessage
    {
        // Create the FirebaseNotification instance
        \Log::info("Preparing FCM message for user {$notifiable->id} with token {$notifiable->fcm_device_token}");
        $notification = FirebaseNotification::create(
            "New Message from {$this->senderName}",
            $this->messageContent
        );

        $message = CloudMessage::withTarget('token', $notifiable->fcm_device_token)
            // ->withNotification($notification)
            ->withData([
                'sender_id' => (string) $this->chatUserId,
                'sender_name' => $this->senderName,
                'message_content' => $this->messageContent,
                'type' => 'chat_message',
            ]);

        // *** NEW: Log the full payload being sent to Firebase for verification ***
        // \Log::debug('FCM Payload sent from Laravel:', [
        //     'payload' => json_decode(json_encode($message->jsonSerialize()), true)
        // ]);

        return $message;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toApn($notifiable)
    {
        $message=ApnMessage::create()
            ->badge(1) // Optional: Set app icon badge count
            ->title('Chat: Swiss Made Corp!') // Notification title
            ->body($this->messageContent) // Notification body
            ->custom('chat_user_id', $this->chatUserId) // Custom data for iOS app navigation
            ->sound('default');

        if ($this->imagePath) {
            $message->custom('image_path', $this->imagePath);
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function routeNotificationForApn($notifiable)
    {
        return $notifiable->device_token;
    }
}
