<?php

namespace App\Listeners\Shopify\Customers;

use App\Events\Shopify\Customers\SingleCustomerFire;
use App\Models\ShopifyCustomers;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SingleCustomerListener implements ShouldQueue
{
    public $queue = 'customer';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  SingleCustomerFire  $event
     * @return void
     */
    public function handle(SingleCustomerFire $event)
    {
        if(count($event->customer) && count($event->shop)) {

            $customer = $event->customer;
            $shop = $event->shop;

            /**
             * Sync Order Customer
             */
            if(isset($customer['id']) && count($customer)) {
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
    }
}
