<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderShippingAddressPartFire;
use App\Helpers\ShopifyHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleOrderShippingAddressPartListener implements ShouldQueue
{
    public $queue = 'singleshippingaddress';

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
     * @param  SingleOrderShippingAddressPartFire  $event
     * @return void
     */
    public function handle(SingleOrderShippingAddressPartFire $event)
    {
        if(count($event->order) && count($event->shop)) {
            ShopifyHelper::syncShippingAddressPart($event->order, $event->shop);
        }
    }
}
