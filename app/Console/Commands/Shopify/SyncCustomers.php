<?php

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyJobs;
use App\Models\ShopifyCustomers;
use Illuminate\Console\Command;
use Config;
use ZfrShopify\ShopifyClient;

class SyncCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Customers from Shopify server';

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
                    'type' => 'sync-customers'
                ])
                ->offset(0)
                ->limit(4)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);
                    $result = $this->syncCustomers($payload['offset'], $payload['records_per_page'], $payload['shop']);
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
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }

        return true;
    }


    /**
     * Sync Customers from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function syncCustomers($offset, $records_per_page, $shop) {
        if($shop['access_token']) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

            $customers = $shopifyClient->getCustomers([
                'limit' => $records_per_page,
                'page' => $offset
            ]);

            echo 'Limit: ' . $records_per_page . "\n";
            echo 'Offset: ' . $offset . "\n";

            if(count($customers)) {

                foreach ($customers as $customer) {

                    /*
                     * Prepare record before insert
                     */
                    $customer['customer_id'] = $customer['id'];
                    unset($customer['id']);

                    if(isset($customer['default_address']) && count($customer['default_address'])) {
                        $default_address = $customer['default_address'];
                        unset($default_address['id']);
                        unset($default_address['customer_id']);
                        $customer = array_merge($customer, $default_address);
                        $customer['default_address'] = json_encode($customer['default_address']);
                    }

                    /**
                     * Set Address based on array provided
                     */
                    if(count($customer['addresses'])) {
                        $customer['addresses'] = json_encode($customer['addresses']);
                    }

                    $customer_processed = ShopifyCustomers::prepareRecord($customer);
                    $customer_processed['account_id'] = $shop['account_id'];

                    $customer_record = ShopifyCustomers::where([
                        'customer_id' => $customer_processed['customer_id'],
                        'account_id' => $customer_processed['account_id'],
                    ])->select('id')->first();

                    if($customer_record) {
                        //echo 'Product Updated: ' . $customer_processed['title'] . "\n";
                        ShopifyCustomers::where([
                            'customer_id' => $customer_processed['customer_id'],
                            'account_id' => $customer_processed['account_id'],
                        ])->update($customer_processed);
                    } else {
                        //echo 'Product Created: ' . $customer_processed['title'] . "\n";
                        ShopifyCustomers::create($customer_processed);
                    }
                }

                echo 'Customers data is synced.' . "\n";
            } else {
                echo 'No Customers fetched' . "\n";
            }
        }

        return true;
    }
}
