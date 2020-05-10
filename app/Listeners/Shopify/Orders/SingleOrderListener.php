<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderFire;
use App\Helpers\ShopifyHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleOrderListener implements ShouldQueue
{
    public $queue = 'single';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        echo 'I have been called';
    }

    /**
     * Handle the event.
     *
     * @param  SingleOrderFire  $event
     * @return void
     */
    public function handle(SingleOrderFire $event)
    {
        if(count($event->order) && count($event->shop)) {
            /**
             * Sync Single Order into system
             */
            ShopifyHelper::syncSingleOrder($event->order, $event->shop);
        }
    }
}
