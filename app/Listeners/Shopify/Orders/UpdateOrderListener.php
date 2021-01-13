<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\UpdateOrderFire;
use App\Models\ShopifyOrders;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOrderListener implements ShouldQueue
{
    public $queue = 'updateorder';

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
     * @param  UpdateOrderFire  $event
     * @return void
     */
    public function handle(UpdateOrderFire $event)
    {
        if($event->order_id && $event->account_id && count($event->order)) {
            /**
             * Update SMS Status
             * To check all statuses go to config/constants.php and check 'sms_status' array
             */
            ShopifyOrders::where([
                'account_id' => $event->account_id,
                'order_id' => $event->order_id,
            ])->update($event->order);
        }
    }
}
