<?php

namespace App\Events\Shopify\Orders;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SingleOrderUpdatedFire
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Holds Order data
     *
     */
    public $order;

    /**
     * Holds Shop data
     *
     */
    public $shop;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $order, array $shop)
    {
        $this->order = $order;
        $this->shop = $shop;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('shopify');
    }
}
