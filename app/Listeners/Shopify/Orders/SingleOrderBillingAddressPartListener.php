<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderBillingAddressPartFire;
use App\Helpers\ShopifyHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleOrderBillingAddressPartListener implements ShouldQueue
{
    public $queue = 'singlebillingaddress';

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
     * @param  SingleOrderBillingAddressPartFire  $event
     * @return void
     */
    public function handle(SingleOrderBillingAddressPartFire $event)
    {
        if(count($event->order) && count($event->shop)) {
            ShopifyHelper::syncBillingAddressPart($event->order, $event->shop);
        }
    }
}
