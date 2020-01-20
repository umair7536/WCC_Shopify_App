<?php

namespace App\Console\Commands\Leopards;

use App\Helpers\ShopifyHelper;
use App\Models\BookedPackets;
use App\Models\ShopifyJobs;
use App\Models\ShopifyOrders;
use App\Models\ShopifyProductImages;
use App\Models\ShopifyProductOptions;
use App\Models\ShopifyProducts;
use App\Models\ShopifyProductTags;
use App\Models\ShopifyProductVariants;
use App\Models\ShopifyShops;
use App\Models\ShopifyTags;
use Carbon\Carbon;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use Illuminate\Console\Command;
use Config;
use ZfrShopify\ShopifyClient;

class SyncPacketStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcs:sync-packet-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Packet Status from LCS server';

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
                    'type' => 'sync-packet-status'
                ])
                ->offset(0)
                ->limit(4)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);

                    $result = $this->syncPacketStatus($payload['offset'], $payload['records_per_page'], $payload['leopards'], $job->account_id);
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
    }


    /**
     * Sync Products from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function syncPacketStatus($offset, $records_per_page, $lcs, $account_id) {

        $status_sync = Config::get('constants.status_sync');
        $status = Config::get('constants.status');

        $booked_packets = BookedPackets::where([
            'account_id' => $account_id,
            'booking_type' => 2 /** '1' for Test, '2' for Live Packets */
        ])
            ->whereIn('status', $status_sync)
            ->limit($records_per_page)
            ->offset($offset)
            ->select('id', 'track_number')
            ->get()->pluck('track_number', 'id');

        try {

            if($booked_packets->count()) {

                $leopards = new LeopardsCODClient();

                $response = $leopards->trackPacket(array(
                    'api_key' => $lcs['api_key'],               // API Key provided by LCS
                    'api_password' => $lcs['api_password'],     // API Password provided by LCS
                    'enable_test_mode' => false,                // [Optional] default value is 'false', true|false to set mode test or live
                    'track_numbers' => implode(',', $booked_packets->toArray())
                ));

                if($response['status']) {
                    if(isset($response['packet_list']) && count($response['packet_list'])) {
                        foreach ($response['packet_list'] as $booked_packet) {

                            $status_id = 0;

                            foreach ($status as $key => $value) {
                                if(strtolower($booked_packet['booked_packet_status']) == strtolower($value)) {
                                    $status_id = $key;
                                }
                            }

                            if(
                                    array_key_exists('invoice_number', $booked_packet)
                                &&  array_key_exists('invoice_date', $booked_packet)
                            ) {
                                BookedPackets::where([
                                    'track_number' => $booked_packet['track_number']
                                ])->update(array(
                                    'status' => $status_id,
                                    'invoice_number' => $booked_packet['invoice_number'],
                                    'invoice_date' => $booked_packet['invoice_date']
                                ));


                                /**
                                 * Mark as Paid
                                 */
                                if(
                                    (
                                            $booked_packet['invoice_date']
                                        &&  $booked_packet['invoice_number']
                                    )
                                        &&
                                    (
                                            array_key_exists('aut_mark_paid', $lcs)
                                        &&  $lcs['aut_mark_paid'] == '1'
                                    )
                                ) {
                                    /**
                                     * Grab Packet information
                                     */
                                    $packet = BookedPackets::where([
                                        'track_number' => $booked_packet['track_number']
                                    ])
                                        ->select('order_number', 'account_id')
                                        ->first();
                                    if($packet) {
                                        /**
                                         * Grab Order from Online Store
                                         */
                                        $order = ShopifyOrders::where([
                                            'account_id' => $packet['account_id'],
                                            'order_number' => $packet['order_number'],
                                        ])->first();
                                        if($order) {
                                            try {
                                                $shop = ShopifyShops::where([
                                                    'account_id' => $packet['account_id']
                                                ])->first();

                                                $shopifyClient = new ShopifyClient([
                                                    'private_app' => false,
                                                    'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                                                    'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                                                    'access_token' => $shop->access_token,
                                                    'shop' => $shop->myshopify_domain
                                                ]);

                                                /**
                                                 * Retrieve Order from Shopify
                                                 */
                                                $shopifyOrder = $shopifyClient->getOrder([
                                                    'id' => (int) $order->order_id
                                                ]);
                                                $shopifyOrder['order_id'] = $shopifyOrder['id'];
                                                $shopifyOrder['account_id'] = $order->account_id;

                                                if(
                                                        $shopifyOrder['processing_method'] == 'manual'
                                                    &&  $shopifyOrder['financial_status'] == 'pending'
                                                ) {
                                                    $transactions = $shopifyClient->getTransactions([
                                                        'order_id' => (int) $shopifyOrder['order_id']
                                                    ]);

                                                    if(count($transactions)) {
                                                        foreach ($transactions as $transaction) {
                                                            if(
                                                                    $transaction['kind'] == 'sale'
                                                                &&  $transaction['status'] == 'pending'
                                                            ) {
                                                                /**
                                                                 * Update Transaction
                                                                 */
                                                                $shopifyClient->createTransaction([
                                                                    'order_id' => (int) $transaction['order_id'],
                                                                    'kind' => 'capture',
                                                                    'gateway' => $transaction['gateway'],
                                                                    'amount' => $transaction['amount'],
                                                                    'parent_id' => (int) $transaction['id'],
                                                                    'status' => 'success',
                                                                    'currency' => $transaction['currency'],
                                                                ]);

                                                                /**
                                                                 * Sync Single Order into system
                                                                 */
                                                                $shopifyOrder = $shopifyClient->getOrder([
                                                                    'id' => (int) $order['order_id']
                                                                ]);
                                                                $shopifyOrder['order_id'] = $shopifyOrder['id'];
                                                                ShopifyHelper::syncSingleOrder($shopifyOrder, $shop->toArray());
                                                            }
                                                        }
                                                    }
                                                }
                                            } catch (\Exception $exception) {}
                                        }
                                    }
                                }
                            } else {
                                BookedPackets::where([
                                    'track_number' => $booked_packet['track_number']
                                ])->update(array(
                                    'status' => $status_id
                                ));
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {

        }

        return true;
    }
}
