<?php

namespace App\Console\Commands\Shopify;

use App\Helpers\ShopifyHelper;
use App\Models\ShopifyJobs;
use Carbon\Carbon;
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
                    'is_processing' => 0,
                    'type' => 'sync-orders'
                ])
                ->offset(0)
                ->limit(1)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {

                    /**
                     * Put current job in processing state
                     */
                    ShopifyJobs::where([
                        'id' => $job->id
                    ])->update([
                        'is_processing' => 1,
                    ]);

                    $payload = json_decode($job->payload, true);
                    $result = $this->syncOrders($payload['shop']);

                    echo 'Result is: ' . ($result) ? 'true' : 'false';

                    if($result) {
                        ShopifyJobs::where([
                            'id' => $job->id
                        ])->delete();
                    } else {
                        ShopifyJobs::where([
                            'id' => $job->id
                        ])->update(array(
                            'attempts' => 1
                        ));
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
    }


    /**
     * Sync Orders from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function syncOrders($shop) {
        if($shop['access_token']) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

//            $orders = $shopifyClient->getOrdersIterator([
//                'since_id' => 0
//            ]);

            $orders = $shopifyClient->getOrdersIterator([
                'created_at_min' => Carbon::now()->subDays(14)->format('Y-m-d') . ' 00:00:00'
            ]);

            foreach ($orders as $order) {
                if(!isset($order['id'])) {
                    break;
                }

                ShopifyHelper::syncSingleOrder($order, $shop);
            }
        }

        return true;
    }
}
