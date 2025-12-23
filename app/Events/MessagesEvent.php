<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue; // Import this
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesEvent implements ShouldBroadcast, ShouldQueue // Add ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatMessage $chatMessage; // Pass the full message object

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $recipientId,
        public int $senderId,
        public $message,
        public $createdAt = null) // Optional timestamp, defaults to now)
    {
        //
    }

     /**
     * The event's broadcast name.
     * This defines the exact event name the client will listen for.
     */
    public function broadcastAs()
    {
        return 'new-message'; // A clear and explicit event name (e.g., 'new-message')
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('message.'. $this->recipientId),
        ];
    }

    public function broadcastWith() {
        // return $this->chatMessage->toArray();
        return [
            "message" => $this->message,
            "sender_id" => $this->senderId,
            'receiver_id' => $this->recipientId,
            'created_at' => $this->createdAt
        ];
    }
}
