<?php
/**
 * Created by PhpStorm.
 * User: Mustafa Mughal
 * Email: mustafa@developify.net
 * Date: 24/10/2019
 * Time: 12:41 PM
 */

namespace App\Helpers;

use App\Models\BookedPackets;
use App\Models\ShopifyOrders;
use App\Models\ShopifyShops;
use ZfrShopify\ShopifyClient;
use Config;

class LeopardsHelper
{

    public static function markPacketAsPaid(string $invoice_date, string $invoice_number, $track_number, $booked_packet_status) {

        $status = Config::get('constants.status');
        $status_delivered = Config::get('constants.status_delivered');

        $status_id = 0;

        foreach ($status as $key => $value) {
            if(strtolower($booked_packet_status) == strtolower($value)) {
                $status_id = $key;
            }
        }


        if(
                $invoice_date
            &&  $invoice_number
            &&  ($status_delivered == $status_id)
        ) {
            /**
             * Grab Packet information
             */
            $packet = BookedPackets::where([
                'track_number' => $track_number
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

                    if($order->financial_status == 'pending') {
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
                                             * Mark Packet as Paid
                                             */
                                            BookedPackets::where([
                                                'track_number' => $track_number
                                            ])
                                                ->update([
                                                    'marked_paid' => 1
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
        }
    }

}