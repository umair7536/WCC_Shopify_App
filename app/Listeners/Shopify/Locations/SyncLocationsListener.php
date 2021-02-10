<?php

namespace App\Listeners\Shopify\Locations;

use App\Events\Shopify\Locations\SyncLocationsFire;
use App\Models\ShopifyLocations;
use App\Models\ShopifyShops;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ZfrShopify\ShopifyClient;
use dispatch;

class SyncLocationsListener implements ShouldQueue
{
    public $queue = 'high';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        echo 'Sync Locations Listener is called ' . "\r\n";
    }

    /**
     * Handle the event.
     *
     * @param  SyncLocationsFire  $event
     * @return void
     */
    public function handle(SyncLocationsFire $event)
    {

        try {
            if($event->account->id) {
                $shop = ShopifyShops::where([
                    'account_id' => $event->account->id
                ])->first();

                if($shop) {
                    $shopifyClient = new ShopifyClient([
                        'private_app' => false,
                        'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                        'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                        'access_token' => $shop->access_token,
                        'shop' => $shop->myshopify_domain
                    ]);

                    $shopify_locations = $shopifyClient->getLocations();

                    if(count($shopify_locations)) {
                        foreach ($shopify_locations as $shopify_location) {
                            $shopify_location = ShopifyLocations::prepareRecord($shopify_location);
                            $shopify_location['location_id'] = $shopify_location['id'];
                            $shopify_location['account_id'] = $shop->account_id;
                            unset($shopify_location['id']);

                            ShopifyLocations::updateOrCreate(
                                [
                                    'account_id' => $shopify_location['account_id'],
                                    'location_id' => $shopify_location['location_id']
                                ],
                                $shopify_location
                            );
                        }
                    }

                    echo 'All locations are synced ' . "\r\n";
                }
            }
        } catch (\Exception $exception) {

        }
    }
}
