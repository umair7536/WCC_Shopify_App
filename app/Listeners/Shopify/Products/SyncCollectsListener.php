<?php

namespace App\Listeners\Shopify\Products;

use App\Events\Shopify\Products\SyncCollectsFire;
use App\Models\ShopifyJobs;
use App\Models\ShopifyShops;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ZfrShopify\ShopifyClient;
use dispatch;

class SyncCollectsListener implements ShouldQueue
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
     * @param  SyncCollectsFire  $event
     * @return void
     */
    public function handle(SyncCollectsFire $event)
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

                $total_records = $shopifyClient->getCollectCount();

                echo 'total records: ' . $total_records;

                if($total_records) {
                    $records_per_page = 250;

                    $total_calls = ceil($total_records / $records_per_page);

                    if($total_calls) {

                        $shopify_jobs = [];

                        for($i = 1; $i <= $total_calls; $i++) {
                            $offset = $i;

                            /**
                             * Payload
                             */
                            $payload = array(
                                'offset' => $offset,
                                'records_per_page' => $records_per_page,
                                'shop' => $shop->toArray(),
                            );

                            $shopify_jobs[$i] = array(
                                'payload' => json_encode($payload),
                                'type' => 'sync-collects',
                                'created_at' => Carbon::now()->toDateTimeString(),
                                'available_at' => Carbon::now()->toDateTimeString(),
                                'account_id' => $shop->account_id,
                            );
                        }

                        ShopifyJobs::insert($shopify_jobs);
                    }
                }

                echo 'Queue dispatched for sync products';
            }
        }
    }
}