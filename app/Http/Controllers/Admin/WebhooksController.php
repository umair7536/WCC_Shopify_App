<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Events\Shopify\Customers\SingleCustomerFire;
use App\Events\Shopify\Orders\SingleOrderCancelledFire;
use App\Events\Shopify\Orders\SingleOrderCreateFire;
use App\Events\Shopify\Orders\SingleOrderFire;
use App\Events\Shopify\Orders\SingleOrderFulfilledFire;
use App\Events\Shopify\Orders\SingleOrderUpdatedFire;
use App\Helpers\ShopifyHelper;
use App\Http\Controllers\Controller;
use App\Models\BillingAddresses;
use App\Models\BookedPackets;
use App\Models\LeopardsSettings;
use App\Models\WccSettings;
use App\Models\ShippingAddresses;
use App\Models\ShopifyCustomers;
use App\Models\ShopifyOrderItems;
use App\Models\ShopifyOrders;
use App\Models\ShopifyShops;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use ZfrShopify\Validator\WebhookValidator;
use Psr\Http\Message\ServerRequestInterface;
use ZfrShopify\Exception\InvalidRequestException;
use Config;

class WebhooksController extends Controller
{
    /**
     * Webhook for App Uninstall.
     *
     * @param \Psr\Http\Message\ServerRequestInterface
     * @return \Illuminate\Http\Response
     */
    public function app(ServerRequestInterface $request)
    {

        $shopify_topic = $request->getHeaderLine('X-Shopify-Topic');
        $shopify_test_mode = $request->getHeaderLine('X-Shopify-Test');
        $shopify_hmac_sha256 = $request->getHeaderLine('X-Shopify-Hmac-Sha256');
        $shopify_shop_domain = $request->getHeaderLine('X-Shopify-Shop-Domain');

        /**
         * Check if packet is coming from test mode
         */
        if(!$shopify_test_mode || $shopify_test_mode == 'false') {
            try {

                $validator = new WebhookValidator();
                $validator->validateWebhook($request, env('SHOPIFY_APP_SHARED_SECRET'));

                switch ($shopify_topic) {
                    case 'app/uninstalled':

                        ShopifyShops::where([
                            'myshopify_domain' => $shopify_shop_domain
                        ])->update(array(
                            'access_token' => null,
                            'installed' => 0,
                        ));

                        break;
                    default:
                        break;
                }

            } catch (InvalidRequestException $exception) {
                return response()->json(['status' => false, "message" => "Authentication Failed"], 401);
            }
        }

        return response()->json(['status' => true]);
    }

    /**
     * Show the form for creating new Permission.
     *
     * @param \Psr\Http\Message\ServerRequestInterface
     * @return \Illuminate\Http\Response
     */
    public function orders(ServerRequestInterface $request)
    {

        try {
            $shopify_topic = $request->getHeaderLine('X-Shopify-Topic');
            $shopify_test_mode = $request->getHeaderLine('X-Shopify-Test');
            $shopify_hmac_sha256 = $request->getHeaderLine('X-Shopify-Hmac-Sha256');
            $shopify_shop_domain = $request->getHeaderLine('X-Shopify-Shop-Domain');
            $shopify_order_id = $request->getHeaderLine('X-Shopify-Order-ID');
 
            /**
             * Check if packet is coming from test mode
             */
            if(!$shopify_test_mode || $shopify_test_mode == 'false') {
                try {

                    $validator = new WebhookValidator();
                    $validator->validateWebhook($request, env('SHOPIFY_APP_SHARED_SECRET'));

                    $shop = ShopifyShops::where([
                        'myshopify_domain' => $shopify_shop_domain
                    ])->first();

                    if($shop) {

                        $shop = $shop->toArray();
                        $order = json_decode($request->getBody(), true);
                        if(isset($order['order_edit'])) {

                        } else {
                            $order['order_id'] = $order['id'];
                        }

                        switch ($shopify_topic) {
                            case 'orders/edited':
                                break;
                            case 'orders/delete':
                                /**
                                 * Delete Addresses
                                 */
                                ShippingAddresses::where(array(
                                    'order_id' => $order['id'],
                                    'account_id' => $shop['account_id'],
                                ))->forceDelete();

                                BillingAddresses::where(array(
                                    'order_id' => $order['id'],
                                    'account_id' => $shop['account_id'],
                                ))->forceDelete();

                                /**
                                 * Delete records
                                 */
                                ShopifyOrderItems::where(array(
                                    'order_id' => $order['id'],
                                    'account_id' => $shop['account_id'],
                                ))->forceDelete();

                                /**
                                 * Delete Order
                                 */
                                ShopifyOrders::where(array(
                                    'order_id' => $order['id'],
                                    'account_id' => $shop['account_id'],
                                ))->forceDelete();

                                break;
                            case 'orders/create':
                                event(new SingleOrderFire($order, $shop));
                                break;
                            case 'orders/fulfilled':
                                event(new SingleOrderFulfilledFire($order, $shop));
                                break;
                            case 'orders/cancelled':
                                event(new SingleOrderCancelledFire($order, $shop));
                                break;
                            case 'orders/updated':
                                event(new SingleOrderUpdatedFire($order, $shop));
                                break;
                            default:
                                /**
                                 * Dispatch Sync wcc Cities Event and Delte existing records
                                 */
                                event(new SingleOrderFire($order, $shop));
                                /**
                                 * Sync Single Order into system
                                 */
//                                ShopifyHelper::syncSingleOrder($order, $shop);
                                break;
                        }
                    }

                } catch (InvalidRequestException $exception) {
                    return response()->json(['status' => false, "message" => "Authentication Failed"], 401);
                }
            }
        } catch (\Exception $exception) {}

        return response()->json(['status' => true]);
    }


    /**
     * Customers Webhook
     *
     * @param \Psr\Http\Message\ServerRequestInterface
     * @return \Illuminate\Http\Response
     */
    public function customers(ServerRequestInterface $request)
    {

        try {
            $shopify_topic = $request->getHeaderLine('X-Shopify-Topic');
            $shopify_test_mode = $request->getHeaderLine('X-Shopify-Test');
            $shopify_hmac_sha256 = $request->getHeaderLine('X-Shopify-Hmac-Sha256');
            $shopify_shop_domain = $request->getHeaderLine('X-Shopify-Shop-Domain');

            /**
             * Check if packet is coming from test mode
             */
            if(!$shopify_test_mode || $shopify_test_mode == 'false') {
                try {

                    $validator = new WebhookValidator();
                    $validator->validateWebhook($request, env('SHOPIFY_APP_SHARED_SECRET'));

                    $shop = ShopifyShops::where([
                        'myshopify_domain' => $shopify_shop_domain
                    ])->first();

                    if($shop) {

                        $shop = $shop->toArray();
                        $customer = json_decode($request->getBody(), true);

                        switch ($shopify_topic) {
                            case 'customers/delete':
                                /**
                                 * Delete Order
                                 */
                                ShopifyOrders::where(array(
                                    'customer_id' => $customer['id'],
                                    'account_id' => $shop['account_id'],
                                ))->forceDelete();

                                break;
                            default:

                                /**
                                 * Dispatch Sync Single Customer Event
                                 */
                                event(new SingleCustomerFire($customer, $shop));
                                break;
                        }
                    }

                } catch (InvalidRequestException $exception) {
                    return response()->json(['status' => false, "message" => "Authentication Failed"], 401);
                }
            }
        } catch (\Exception $exception) {
        }

        return response()->json(['status' => true]);
    }


    /**
     * Customer Get Data Request
     *
     * @param \Psr\Http\Message\ServerRequestInterface
     * @return \Illuminate\Http\Response
     */
    public function customersDataRequest(ServerRequestInterface $request) {

        try {

            $validator = new WebhookValidator();
            $validator->validateWebhook($request, env('SHOPIFY_APP_SHARED_SECRET'));

            $shopify_shop_domain = $request->getHeaderLine('X-Shopify-Shop-Domain');

            $shop = ShopifyShops::where([
                'myshopify_domain' => $shopify_shop_domain
            ])->first();

            if($shop) {

                $shop = $shop->toArray();
                $payload = json_decode($request->getBody(), true);

            }

        } catch (InvalidRequestException $exception) {
            return response()->json(['status' => false, "message" => "Authentication Failed"], 401);
        }

        return response()->json(['status' => true]);
    }


    /**
     * Customer Remove Data Request
     *
     * @param \Psr\Http\Message\ServerRequestInterface
     * @return \Illuminate\Http\Response
     */
    public function customersRedact(ServerRequestInterface $request) {

        try {

            $validator = new WebhookValidator();
            $validator->validateWebhook($request, env('SHOPIFY_APP_SHARED_SECRET'));

            $shopify_shop_domain = $request->getHeaderLine('X-Shopify-Shop-Domain');

            $shop = ShopifyShops::where([
                'myshopify_domain' => $shopify_shop_domain
            ])->first();

            if($shop) {

                $shop = $shop->toArray();
                $payload = json_decode($request->getBody(), true);

            }

        } catch (InvalidRequestException $exception) {
            return response()->json(['status' => false, "message" => "Authentication Failed"], 401);
        }

        return response()->json(['status' => true]);
    }


    /**
     * Shop Remove Data Request
     *
     * @param \Psr\Http\Message\ServerRequestInterface
     * @return \Illuminate\Http\Response
     */
    public function shopRedact(ServerRequestInterface $request) {

        try {

            $validator = new WebhookValidator();
            $validator->validateWebhook($request, env('SHOPIFY_APP_SHARED_SECRET'));

            $shopify_shop_domain = $request->getHeaderLine('X-Shopify-Shop-Domain');

            $shop = ShopifyShops::where([
                'myshopify_domain' => $shopify_shop_domain
            ])->first();

            if($shop) {

                $shop = $shop->toArray();
                $payload = json_decode($request->getBody(), true);

            }

        } catch (InvalidRequestException $exception) {
            return response()->json(['status' => false, "message" => "Authentication Failed"], 401);
        }

        return response()->json(['status' => true]);
    }


    /**
     * Track booked Packet.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function track($track_number = false)
    {
        if(!$track_number) {
            echo 'No tracking number provided'; exit;
        }

        $booked_packet = BookedPackets::where([
            'track_number' => $track_number
        ])->first();

        if(!$booked_packet) {
            echo 'Incorrect tracking number provided'; exit;
        }

        /**
         * Load wcc Settings
         */
        $wcc_settings = WccSettings::where([
            'account_id' => $booked_packet->account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');


        try {
            $username= WccSettings::where('account_id', Auth::User()->account_id)->where('name','User ID')->get();
            $password=WccSettings::where('account_id', Auth::User()->account_id)->where('name','Password')->get();
            $track_order_api='http://web.api.wcc.com.pk:3001/api/General/Tracking?username='.$username[0]->data.'&password='.$password[0]->data.'&CNNO='.$track_number;
            // $create_order_api=str_replace(' ','&nbsp;',$create_order_api);
            $track_order_api=str_replace(' ','%20',$track_order_api);

            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
            curl_setopt($ch, CURLOPT_URL, $track_order_api);
            $data = curl_exec($ch);
            curl_close($ch);
//            echo $data;
            $data=json_decode($data);
            
            if ($data->IsSuccessful)
            {
                echo 'Tracking Present';
                exit();
            }
            else
            {
                echo 'No Tracking Present';
                exit();
            }

            // $packet = array();
            // $track_history = [];

            // if($response['status']) {
            //     if(isset($response['packet_list']) && count($response['packet_list'])) {

            //         $packet = $response['packet_list'][0];
            //         $track_history = array_reverse($packet['Tracking Detail']);

            //         /**
            //          * Update Packet Status
            //          */
            //         $status = Config::get('constants.status');
            //         $status_id = 0;

            //         foreach ($status as $key => $value) {
            //             if(strtolower($packet['booked_packet_status']) == strtolower($value)) {
            //                 $status_id = $key;
            //             }
            //         }

            //         if(
            //                 array_key_exists('invoice_number', $packet)
            //             &&  array_key_exists('invoice_date', $packet)
            //         ) {
            //             BookedPackets::where([
            //                 'track_number' => $packet['track_number']
            //             ])->update(array(
            //                 'status' => $status_id,
            //                 'invoice_number' => $packet['invoice_number'],
            //                 'invoice_date' => $packet['invoice_date']
            //             ));
            //         } else {
            //             BookedPackets::where([
            //                 'track_number' => $packet['track_number']
            //             ])->update(array(
            //                 'status' => $status_id
            //             ));
            //         }
            //     }
            // }

        } catch (\Exception $exception) {
            echo $track_number;
            echo 'Something went wrong, please contact support'; exit;
        }

        return view('admin.booked_packets.lcs', compact('packet', 'track_history'));
    }

}
