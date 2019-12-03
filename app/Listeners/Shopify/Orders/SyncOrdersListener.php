<?php

namespace App\Listeners\Shopify\Orders;

use App\Events\Shopify\Orders\SyncOrdersFire;
use App\Models\ShopifyJobs;
use App\Models\ShopifyShops;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ZfrShopify\ShopifyClient;
use dispatch;

class SyncOrdersListener implements ShouldQueue
{
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
     * @param  SyncOrdersFire  $event
     * @return void
     */
    public function handle(SyncOrdersFire $event)
    {

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

                $total_records = $shopifyClient->getOrderCount();

                echo 'total records: ' . $total_records;

                if($total_records) {

                    /**
                     * Payload
                     */
                    $payload = array(
                        'shop' => $shop->toArray(),
                    );

                    $shopify_jobs[] = array(
                        'payload' => json_encode($payload),
                        'type' => 'sync-orders',
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'available_at' => Carbon::now()->toDateTimeString(),
                        'account_id' => $shop->account_id,
                    );

                    ShopifyJobs::insert($shopify_jobs);
                }

                echo 'Queue dispatched for sync orders';
            }
        }
    }
}
