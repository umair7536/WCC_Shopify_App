<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Orders\SingleOrderFire;
use App\Http\Controllers\Controller;
use App\Models\ShopifyOrderItems;
use App\Models\ShopifyOrders;
use App\Models\ShopifyShops;
use ZfrShopify\Validator\WebhookValidator;
use Psr\Http\Message\ServerRequestInterface;
use ZfrShopify\Exception\InvalidRequestException;

class WebhooksController extends Controller
{
    /**
     * Show the form for creating new Permission.
     *
     * @param \Psr\Http\Message\ServerRequestInterface
     * @return \Illuminate\Http\Response
     */
    public function orders(ServerRequestInterface $request)
    {

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

                    switch ($shopify_topic) {
                        case 'orders/delete':
                            /**
                             * Delete records
                             */
                            ShopifyOrderItems::where(array(
                                'order_id' => $order['order_id'],
                                'account_id' => $shop['account_id'],
                            ))->forceDelete();

                            /**
                             * Delete Order
                             */
                            ShopifyOrders::where(array(
                                'order_id' => $order['order_id'],
                                'account_id' => $shop['account_id'],
                            ))->forceDelete();

                            break;
                        default:
                            /**
                             * Dispatch Sync Leopards Cities Event and Delte existing records
                             */
                            event(new SingleOrderFire($order, $shop));
                            break;
                    }
                }

            } catch (InvalidRequestException $exception) {
                // Handle your error heres
            }
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
            return response()->json(['status' => false]);
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
            return response()->json(['status' => false]);
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
            return response()->json(['status' => false]);
        }

        return response()->json(['status' => true]);
    }

}
