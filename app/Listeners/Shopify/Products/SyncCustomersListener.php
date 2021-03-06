<?php

namespace App\Listeners\Shopify\Products;

use App\Events\Shopify\Products\SyncCustomersFire;
use App\Models\ShopifyJobs;
use App\Models\ShopifyShops;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ZfrShopify\ShopifyClient;
use dispatch;

class SyncCustomersListener implements ShouldQueue
{
    public $queue = 'high';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        echo 'Sync Customers Listener is called ' . "\r\n";
    }

    /**
     * Handle the event.
     *
     * @param  SyncCustomersFire  $event
     * @return void
     */
    public function handle(SyncCustomersFire $event)
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

                $total_records = $shopifyClient->getCustomerCount();

                echo 'total records: ' . $total_records . "\r\n";

                if($total_records) {

                    /**
                     * Payload
                     */
                    $payload = array(
                        'shop' => $shop->toArray(),
                    );

                    $shopify_jobs[] = array(
                        'payload' => json_encode($payload),
                        'type' => 'sync-customers',
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'available_at' => Carbon::now()->toDateTimeString(),
                        'account_id' => $shop->account_id,
                    );

                    ShopifyJobs::insert($shopify_jobs);
                }

                echo 'Queue dispatched for sync customers' . "\r\n";
            }
        }
    }
}
