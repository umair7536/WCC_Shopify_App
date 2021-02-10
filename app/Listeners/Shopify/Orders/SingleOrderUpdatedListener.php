<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderAddressesPartFire;
use App\Events\Shopify\Orders\SingleOrderCustomerPartFire;
use App\Events\Shopify\Orders\SingleOrderItemsPartFire;
use App\Events\Shopify\Orders\SingleOrderUpdatedFire;
use App\Helpers\ShopifyHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleOrderUpdatedListener implements ShouldQueue
{
    public $queue = 'singleupdated';

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
     * @param  SingleOrderUpdatedFire  $event
     * @return void
     */
    public function handle(SingleOrderUpdatedFire $event)
    {
        if(count($event->order) && count($event->shop)) {
            /**
             * Disptach Order Customer Part
             */
            event(new SingleOrderCustomerPartFire($event->order, $event->shop));

            /**
             * Disptach Order Items Part
             */
            event(new SingleOrderItemsPartFire($event->order, $event->shop));

            /**
             * Disptach Order Addresses Part
             */
            event(new SingleOrderAddressesPartFire($event->order, $event->shop));

            try {
                ShopifyHelper::syncOrderUpdatePart($event->order, $event->shop);
            } catch (\Exception $exception) {

            }
        }
    }
}
