<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderAddressesPartFire;
use App\Events\Shopify\Orders\SingleOrderBillingAddressPartFire;
use App\Events\Shopify\Orders\SingleOrderShippingAddressPartFire;
use App\Helpers\ShopifyHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleOrderAddressesPartListener implements ShouldQueue
{
    public $queue = 'singleaddress';

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
     * @param  SingleOrderAddressesPartFire  $event
     * @return void
     */
    public function handle(SingleOrderAddressesPartFire $event)
    {
        if(count($event->order) && count($event->shop)) {
            /**
             * Disptach Order Shipping Address Part
             */
            event(new SingleOrderShippingAddressPartFire($event->order, $event->shop));

            /**
             * Disptach Order Billing Address Part
             */
            event(new SingleOrderBillingAddressPartFire($event->order, $event->shop));

//            ShopifyHelper::syncAddressesPart($event->order, $event->shop);
        }
    }
}
