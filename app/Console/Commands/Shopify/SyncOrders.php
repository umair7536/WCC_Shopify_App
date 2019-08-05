<?php

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyCustomers;
use App\Models\ShopifyJobs;
use App\Models\ShopifyOrderItems;
use App\Models\ShopifyOrders;
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

                    /*
                     * Prepare record before insert
                     */
                    $order['order_id'] = $order['id'];
                    unset($order['id']);
                    $order_processed = ShopifyOrders::prepareRecord($order);
                    $order_processed['account_id'] = $shop['account_id'];
                    if(count($order['customer'])) {
                        $order_processed['customer_id'] = $order['customer']['id'];
                    }

                    $order_record = ShopifyOrders::where([
                        'order_id' => $order_processed['order_id'],
                        'account_id' => $order_processed['account_id'],
                    ])->select('id')->first();

                    if($order_record) {
                        ShopifyOrders::where([
                            'order_id' => $order_processed['order_id'],
                            'account_id' => $order_processed['account_id'],
                        ])->update($order_processed);
                    } else {
                        //echo 'Order Created: ' . $order_processed['title'] . "\n";
                        ShopifyOrders::create($order_processed);
                    }

                    /**
                     * Sync Order Customer
                     */
                    if(count($order['customer'])) {

                        $customer = $order['customer'];

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
                        if(isset($customer['addresses']) && count($customer['addresses'])) {
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


                    /*
                     * Sync Order Images
                     */
                    if(count($order['line_items'])) {

                        $line_items = [];

                        /**
                         * Delete records
                         */
                        ShopifyOrderItems::where(array(
                            'order_id' => $order['order_id'],
                            'account_id' => $shop['account_id'],
                        ))->forceDelete();

                        foreach($order['line_items'] as $line_item) {

                            $line_item['item_id'] = $line_item['id'];
                            unset($line_item['id']);
                            $line_item_processed = ShopifyOrderItems::prepareRecord($line_item);
                            $line_item_processed['account_id'] = $shop['account_id'];
                            $line_item_processed['order_id'] = $order['order_id'];

                            $line_items[$line_item['item_id']] = $line_item_processed;
                        }

                        if(count($line_items)) {
                            ShopifyOrderItems::insert($line_items);
                        }

//                            foreach($order['line_items'] as $line_item) {
//                                dd($line_item);
//                                /*
//                                 * Prepare record before insert
//                                 */
//                                $line_item['item_id'] = $line_item['id'];
//                                unset($line_item['id']);
//                                $line_item_processed = ShopifyOrderItems::prepareRecord($line_item);
//                                $line_item_processed['account_id'] = $shop['account_id'];
//
//                                $line_item_record = ShopifyOrderItems::where([
//                                    'item_id' => $line_item_processed['item_id'],
//                                    'account_id' => $line_item_processed['account_id'],
//                                ])->first();
//
//                                if($line_item_record) {
//                                    ShopifyOrderItems::where([
//                                        'item_id' => $line_item_processed['item_id'],
//                                        'account_id' => $line_item_processed['account_id'],
//                                    ])->update($line_item_processed);
//                                } else {
//                                    ShopifyOrderItems::create($line_item_processed);
//                                }
//                            }
                    }
                }
            } else {
                echo 'No Orders fetched' . "\n";
            }
        }

        return true;
    }
}
