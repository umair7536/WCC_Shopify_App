<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderItemsPartFire;
use App\Helpers\ShopifyHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleOrderItemsPartListener implements ShouldQueue
{
    public $queue = 'singleitems';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  SingleOrderItemsPartFire  $event
     * @return void
     */
    public function handle(SingleOrderItemsPartFire $event)
    {
        if(count($event->order) && count($event->shop)) {
            ShopifyHelper::syncOrderItemsPart($event->order, $event->shop);
        }
    }
}
