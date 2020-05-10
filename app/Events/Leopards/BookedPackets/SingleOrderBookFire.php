<?php

namespace App\Events\Leopards\BookedPackets;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SingleOrderBookFire
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Holds Order data
     *
     */
    public $order;

    /**
     * Holds Shipment Type
     *
     */
    public $shipment_type_id;

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
    public function __construct($order, $shipment_type_id = 10, $account_id)
    {
        $this->order = $order;
        $this->shipment_type_id = $shipment_type_id;
        $this->account_id = $account_id;
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
