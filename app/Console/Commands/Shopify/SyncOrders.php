<?php

namespace App\Console\Commands\Shopify;

use App\Helpers\ShopifyHelper;
use App\Models\ShopifyJobs;
use Illuminate\Console\Command;
use ZfrShopify\ShopifyClient;

class SyncOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Orders from Shopify server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $jobs = ShopifyJobs
                ::where([
                    'attempts' => 0,
                    'type' => 'sync-orders'
                ])
                ->offset(0)
                ->limit(4)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);
                    $result = $this->syncOrders($payload['offset'], $payload['records_per_page'], $payload['shop']);
                    echo 'Result is: ' . ($result) ? 'true' : 'false';
                    if($result) {
                        ShopifyJobs::where([
                            'id' => $job->id
                        ])->delete();
                    }
                }
            }

        } catch(\Exception $e) {
            echo "\n";
            echo $e->getLine() . "\n";
            echo $e->getMessage() . "\n";
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }

        return true;
    }


    /**
     * Sync Orders from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function syncOrders($offset, $records_per_page, $shop) {
        if($shop['access_token']) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

            $orders = $shopifyClient->getOrders([
                'limit' => $records_per_page,
                'page' => $offset
            ]);

            echo 'Limit: ' . $records_per_page . "\n";
            echo 'Offset: ' . $offset . "\n";

            if(count($orders)) {

                foreach ($orders as $order) {

                    ShopifyHelper::syncSingleOrder($order, $shop);
                }
            } else {
                echo 'No Orders fetched' . "\n";
            }
        }

        return true;
    }
}
