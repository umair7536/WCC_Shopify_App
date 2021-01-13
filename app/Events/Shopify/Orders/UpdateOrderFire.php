<?php

namespace App\Events\Shopify\Orders;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UpdateOrderFire
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Holds Order data
     *
     */
    public $order;

    /**
     * Holds Order ID
     *
     */
    public $order_id;

    /**
     * Holds Account ID
     *
     */
    public $account_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $order_id, $account_id, array $order)
    {
        $this->order_id = $order_id;
        $this->account_id = $account_id;
        $this->order = $order;
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
