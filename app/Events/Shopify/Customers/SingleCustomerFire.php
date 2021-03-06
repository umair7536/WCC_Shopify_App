<?php

namespace App\Events\Shopify\Customers;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SingleCustomerFire
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Holds Order data
     *
     */
    public $customer;

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
    public function __construct(array $customer, array $shop)
    {
        $this->customer = $customer;
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
