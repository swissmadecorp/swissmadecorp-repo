<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel; // Ensure PrivateChannel is imported
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $recipientId,
        public int $readerId,
        public array $messageIds) // <<< RENAME senderId to recipientId here
    {

    }

    public function broadcastOn()
    {
        // Broadcast this event on the private channel of the *recipient* (the original sender).
        return [ new PrivateChannel('message.' . $this->recipientId), ]; // <<< THIS IS NOW CORRECT
    }

    public function broadcastAs()
    {
        return 'messages-read';
    }

    public function broadcastWith()
    {
        return [
            'readByUserId' => $this->readerId,
            'sender_id' => $this->recipientId, // <<< THIS IS NOW CORRECT
            'messages' => [
                'ids' => $this->messageIds,
            ],
        ];
    }
}