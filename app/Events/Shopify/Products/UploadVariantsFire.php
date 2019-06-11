<?php

namespace App\Events\Shopify\Products;

use App\Models\Accounts;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UploadVariantsFire
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Holds App\Models\Accounts $account object
     *
     */
    public $account;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Accounts $account)
    {
        $this->account = $account;
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
