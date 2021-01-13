<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderCustomerPartFire;
use App\Helpers\ShopifyHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleOrderCustomerPartListener implements ShouldQueue
{
    public $queue = 'singlecustomer';

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
     * @param  SingleOrderCustomerPartFire  $event
     * @return void
     */
    public function handle(SingleOrderCustomerPartFire $event)
    {
        if(count($event->order) && count($event->shop)) {
            ShopifyHelper::syncCustomerPart($event->order, $event->shop);
        }
    }
}
