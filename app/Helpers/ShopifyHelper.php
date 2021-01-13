<?php
/**
 * Created by PhpStorm.
 * User: Mustafa Mughal
 * Email: mustafa@developify.net
 * Date: 24/10/2019
 * Time: 12:41 PM
 */

namespace App\Helpers;


use App\Models\BillingAddresses;
use App\Models\BookedPackets;
use App\Models\ShippingAddresses;
use App\Models\ShopifyBillings;
use App\Models\ShopifyCustomers;
use App\Models\ShopifyOrderItems;
use App\Models\ShopifyOrders;
use App\Models\ShopifyPlans;
use App\Models\ShopifyShops;
use Carbon\Carbon;

class ShopifyHelper
{

    /**
     * Sync Single Order function
     *
     * @param $order
     * @param $shop
     * @return bool
     */
    public static function syncSingleOrder(array $order, array $shop) {
        /*
         * Prepare record before insert
        */
        $order['order_id'] = $order['id'];
        unset($order['id']);
        $order_processed = ShopifyOrders::prepareRecord($order);
        $order_processed['account_id'] = $shop['account_id'];
        if(isset($order['customer']) && count($order['customer'])) {
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
            $order_record = ShopifyOrders::create($order_processed);
        }

        /**
         * Sync Shipping Address
         */
        if(isset($order['shipping_address']) && count($order['shipping_address'])) {

            $order['shipping_address']['order_id'] = $order['order_id'];
            $shipping_address = ShippingAddresses::prepareRecord($order['shipping_address']);
            $shipping_address['account_id'] = $shop['account_id'];

            $shipping_address_record = ShippingAddresses::where([
                'order_id' => $shipping_address['order_id'],
                'account_id' => $shipping_address['account_id'],
            ])->select('id')->first();

            if($shipping_address_record) {
                ShippingAddresses::where([
                    'order_id' => $shipping_address['order_id'],
                    'account_id' => $shipping_address['account_id'],
                ])->update($shipping_address);
            } else {
                //echo 'Order Created: ' . $order_processed['title'] . "\n";
                $shipping_address_record = ShippingAddresses::create($shipping_address);
            }
        }

        /**
         * Sync Billing Address
         */
        if(isset($order['billing_address']) && count($order['billing_address'])) {

            $order['billing_address']['order_id'] = $order['order_id'];
            $billing_address = BillingAddresses::prepareRecord($order['billing_address']);
            $billing_address['account_id'] = $shop['account_id'];

            $billing_address_record = BillingAddresses::where([
                'order_id' => $billing_address['order_id'],
                'account_id' => $billing_address['account_id'],
            ])->select('id')->first();

            if($billing_address_record) {
                BillingAddresses::where([
                    'order_id' => $billing_address['order_id'],
                    'account_id' => $billing_address['account_id'],
                ])->update($billing_address);
            } else {
                //echo 'Order Created: ' . $order_processed['title'] . "\n";
                $billing_address_record = BillingAddresses::create($billing_address);
            }
        }


        /**
         * Sync Order Customer
         */
        if(isset($order['customer']) && count($order['customer'])) {

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

        return $order_record->id;
    }

    /**
     * Sync Order Create Data Part Only
     *
     * @param $order
     * @param $shop
     * @return bool
     */
    public static function syncOrderCreatePart(array $order, array $shop) {
        /*
         * Prepare record before insert
        */
        $order['order_id'] = $order['id'];
        unset($order['id']);
        $order_processed = ShopifyOrders::prepareRecord($order);
        $order_processed['account_id'] = $shop['account_id'];
        if(isset($order['customer']) && count($order['customer'])) {
            $order_processed['customer_id'] = $order['customer']['id'];
        }

        //echo 'Order Created: ' . $order_processed['title'] . "\n";
        ShopifyOrders::create($order_processed);
    }


    /**
     * Sync Order Update Data Part Only
     *
     * @param $order
     * @param $shop
     * @return bool
     */
    public static function syncOrderUpdatePart(array $order, array $shop) {
        /*
         * Prepare record before insert
        */
        $order['order_id'] = $order['id'];
        unset($order['id']);
        $order_processed = ShopifyOrders::prepareRecord($order);
        $order_processed['account_id'] = $shop['account_id'];
        if(isset($order['customer']) && count($order['customer'])) {
            $order_processed['customer_id'] = $order['customer']['id'];
        }

        ShopifyOrders::where([
            'order_id' => $order_processed['order_id'],
            'account_id' => $order_processed['account_id'],
        ])->update($order_processed);
    }

    /**
     * Sync Order Addresses Part Only
     *
     * @param $order
     * @param $shop
     * @return bool
     */
    public static function syncAddressesPart(array $order, array $shop) {
        /**
         * Sync Shipping Address
         */
        if(isset($order['shipping_address']) && count($order['shipping_address'])) {

            $order['shipping_address']['order_id'] = $order['order_id'];
            $shipping_address = ShippingAddresses::prepareRecord($order['shipping_address']);
            $shipping_address['account_id'] = $shop['account_id'];

            $shipping_address_record = ShippingAddresses::where([
                'order_id' => $shipping_address['order_id'],
                'account_id' => $shipping_address['account_id'],
            ])->select('id')->first();

            if($shipping_address_record) {
                ShippingAddresses::where([
                    'order_id' => $shipping_address['order_id'],
                    'account_id' => $shipping_address['account_id'],
                ])->update($shipping_address);
            } else {
                ShippingAddresses::create($shipping_address);
            }
        }

        /**
         * Sync Billing Address
         */
        if(isset($order['billing_address']) && count($order['billing_address'])) {

            $order['billing_address']['order_id'] = $order['order_id'];
            $billing_address = BillingAddresses::prepareRecord($order['billing_address']);
            $billing_address['account_id'] = $shop['account_id'];

            $billing_address_record = BillingAddresses::where([
                'order_id' => $billing_address['order_id'],
                'account_id' => $billing_address['account_id'],
            ])->select('id')->first();

            if($billing_address_record) {
                BillingAddresses::where([
                    'order_id' => $billing_address['order_id'],
                    'account_id' => $billing_address['account_id'],
                ])->update($billing_address);
            } else {
                BillingAddresses::create($billing_address);
            }
        }
    }



    /**
     * Sync Order Customer Part Only
     *
     * @param $order
     * @param $shop
     * @return bool
     */
    public static function syncCustomerPart(array $order, array $shop) {
        /**
         * Sync Order Customer
         */
        if(isset($order['customer']) && count($order['customer'])) {

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
                ShopifyCustomers::where([
                    'customer_id' => $customer_processed['customer_id'],
                    'account_id' => $customer_processed['account_id'],
                ])->update($customer_processed);
            } else {
                ShopifyCustomers::create($customer_processed);
            }
        }
    }



    /**
     * Sync Order Items Part Only
     *
     * @param $order
     * @param $shop
     * @return bool
     */
    public static function syncOrderItemsPart(array $order, array $shop) {
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
        }
    }


    /**
     * Get Free Plan
     *
     * @param $account_id
     * @return array
     */
    static public function getFreePlan($account_id) {

        $result = array(
            'status' => false,
            'plan_id' => null,
            'activated_on' => null,
            'shopify_billing_id' => null,
        );

        try {
            /**
             * Grab Free Plan and assign this to User
             */
            $plan = ShopifyPlans::where([
                'slug' => 'free'
            ])->first();
            if($plan) {
                $result['plan_id'] = $plan->id;
            } else {
                return $result;
            }

            $date = Carbon::now()->format('Y-m-d');
            $created_at = Carbon::now()->toDateTimeString();

            /**
             * Create Free Plan entry into Shopify Billings
             */
            $billing_data = array(
                'charge_id' => $account_id,
                'name' => 'Free',
                'api_client_id' => $account_id,
                'price' => 0.00,
                'return_url' => env('APP_URL'),
                'billing_on' => $date,
                'test' => env('SHOPIFY_BILLING_TEST_MODE'),
                'activated_on' => $date,
                'cancelled_on' => null,
                'status' => 'active',
                'trial_days' => 0,
                'trial_ends_on' => $date,
                'decoded_return_url' => env('APP_URL'),
                'confirmation_url' => env('APP_URL'),
                'plan_id' => $plan->id,
                'account_id' => $account_id,
                'created_at' => $created_at,
                'updated_at' => $created_at,
            );

            $billing = ShopifyBillings::create($billing_data, $account_id);

            $result['status'] = true;
            $result['activated_on'] = $date;
            $result['shopify_billing_id'] = $billing->id;

            return $result;
        } catch (\Exception $exception) {
            return $result;
        }
    }


    /**
     * Get Free Plan
     *
     * @param $account_id
     * @return array
     */
    static public function getQuota($account_id) {

        $result = array(
            'status' => true,
            'info' => null,
            'error' => null,
        );

        try {

            /**
             * Fetch Shop Information
             */
            $shop = ShopifyShops::where([
                'account_id' => $account_id
            ])->first();

            if(!$shop || !$shop->activated_on) {
                $result['status'] = false;
                $result['error'] = 'Your shop have issues, please contact with support';
            }

            /**
             * Fetch Plan based on Shop
             */
            $plan = ShopifyPlans::where([
                'id' => $shop->plan_id
            ])->first();

            if(!$plan) {
                $result['status'] = false;
                $result['error'] = 'Your billing plan have issues, please contact with support';
            }


            /**
             * Fetch Booked Packets Quota usage in current billing cycle
             */
            $billing_start = date('Y-m') . '-' . explode('-', $shop->activated_on)[2];
            $billing_end = Carbon::parse($billing_start)->addDays(30)->format('Y-m-d');

            $packets_count = BookedPackets::where([
                'account_id' => $account_id,
                'booking_type' => 2 /** 2 for Live and 1 for Test Packets */
            ])
                ->whereBetween('created_at', [$billing_start." 00:00:00", $billing_end." 23:59:59"])
                ->count();

            if($plan->quota <= $packets_count) {
                $result['status'] = false;
                $result['error'] = 'Your monthly quota of ' . $plan->quota . ' Orders has been consumed. Please upgrade your currrent plan to next plan';
            } else {
                if($plan->quota && $packets_count) {
                    /**
                     * Find quota usage percentage for current billing cycle.
                     */
                    $percent = ($packets_count / $plan->quota) * 100;
                    if($percent >= 90) {
                        $result['info'] = 'Your monthly quota  more than ' . $percent . '% has been used. Upgrade your current plan to continue your services smoothly.';
                    }
                }
            }
        } catch (\Exception $exception) {
            $result['status'] = false;
            $result['error'] = 'Something went wrong, please contact support of this app.';
        }

        return $result;
    }

}