<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerChatUpdated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $publicChannels = [],
        public array $privateChannels = [],
        public array $payload = [],
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        foreach ($this->publicChannels as $channelName) {
            $channels[] = new Channel($channelName);
        }

        foreach ($this->privateChannels as $channelName) {
            $channels[] = new PrivateChannel($channelName);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'customer-chat.updated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
