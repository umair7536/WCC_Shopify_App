<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SingleOrderFulfillmentFire;
use App\Helpers\ShopifyHelper;
use App\Models\LeopardsSettings;
use App\Models\ShopifyShops;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ZfrShopify\ShopifyClient;

class SingleOrderFulfillmentListener implements ShouldQueue
{
    public $queue = 'fulfillment';

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
     * @param  SingleOrderFulfillmentFire  $event
     * @return void
     */
    public function handle(SingleOrderFulfillmentFire $event)
    {
        if(count($event->order)) {

            $account_id = $event->order['account_id'];

            /**
             * Fulfill this order
             */
            $shop = ShopifyShops::where([
                'account_id' => $account_id
            ])->first();

            /**
             * Fetch default Inventory Location ID
             */
            $inventory_location = LeopardsSettings::getDefaultInventoryLocation($account_id);
            $fulfillment_status = LeopardsSettings::isAutoFulfillmentEnabled($account_id);

            echo 'Location: ' . $inventory_location . "<br/>";
            echo 'Status: ' . $fulfillment_status . "<br/>";


            $fulfillment = array();

            if($shop && $inventory_location && $fulfillment_status) {

                $shopifyClient = new ShopifyClient([
                    'private_app' => false,
                    'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                    'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                    'access_token' => $shop->access_token,
                    'shop' => $shop->myshopify_domain
                ]);

                try {
                    $fullfilments = array();

                    $fullfilments = $shopifyClient->getFulfillments([
                        'order_id' => (int) $event->order['order_id']
                    ]);

                    if(count($fullfilments)) {
                        // Fulfillment is already made
//                        echo 'Fulfillments are: ';
//                        echo '<pre>';
//                        print_r($fullfilments);
//                        exit;
                        echo 'fulfillments are available';
                    } else {
                        try {
                            $fulfillment = $shopifyClient->createFulfillment(array(
                                'order_id' => (int) $event->order['order_id'],
                                'location_id' => $inventory_location,
                                'tracking_number' => $event->cn_number,
                                'tracking_company' => 'Leopards',
                                'notify_customer' => true,
                                'tracking_urls' => array(
                                    route('track', $event->cn_number)
                                ),
                            ));

                            // Packet has been fulfilled
                            echo 'fulfilled';
                        } catch (\Exception $exception) {
                            echo $exception->getLine() . ' - ' . $exception->getMessage();
                        }
                    }
                } catch (\Exception $exception) {}
            }
        }
    }
}
