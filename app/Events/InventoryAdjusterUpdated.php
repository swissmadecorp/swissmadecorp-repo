<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryAdjusterUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public ?int $productId = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('inventory-adjuster'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'product_id' => $this->productId,
        ];
    }
}
