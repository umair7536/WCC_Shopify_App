<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ShopifyHelper;
use App\Models\ShopifyBillings;
use App\Models\ShopifyPlans;
use App\Models\ShopifyShops;
use App\Models\Tickets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;
use ZfrShopify\ShopifyClient;

class ShopifyBillingsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('shopify_billings_manage')) {
            return abort(401);
        }

        $plans = ShopifyPlans
                    ::where([
                        'active' => 1,
                    ])
                    ->orderBy('sort_number', 'asc')->get();

        $shopify_shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();

        $ticket_count = Tickets::where([
            'account_id' => Auth::User()->account_id
        ])->count();

        if($shopify_shop) {
            $shopify_shop = $shopify_shop->toArray();
            if(!$shopify_shop['plan_id']) {
                $shopify_shop['plan_id'] = 1;
            }
        } else {
            $shopify_shop = array(
                'plan_id' => 1
            );
        }

        return view('admin.shopify_billings.index', compact('plans', 'shopify_shop', 'ticket_count'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            flash('Plan ID is not provided.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

        try {

            $plan = ShopifyPlans::where([
                'id' => decrypt($request->get('plan_id'))
            ])->first();

            if(!$plan) {
                flash('Provided plan does not exists in our system.')->error()->important();
                return redirect()->route('admin.shopify_billings.index');
            }

        } catch (\Exception $exception) {
            flash('Due to security reasons, you request is not processed.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

        $shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();

        if(!$shop) {
            flash('Shop is not present, please contact support..')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        } else {
            $shop = $shop->toArray();
        }

        /**
         * Free Plan case
         */
        if(!$plan['price']) {

            $recurringApplicationCharge = ShopifyBillings::where(array(
                'account_id' => Auth::User()->account_id,
                'status' => 'active',
            ))->where('price', '!=', 0.00)
                ->latest('id')->first();

            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

            try {

                /**
                 * Any charge is present there in system before?
                 * If yes, then let the system change the package
                 */
                if($recurringApplicationCharge) {
                    $recurringApplicationCharge = $shopifyClient->getRecurringApplicationCharge(array(
                        'id' => (int) $recurringApplicationCharge->charge_id
                    ));

                    if(isset($recurringApplicationCharge['id']) && $recurringApplicationCharge['id']) {
                        // Delete Old recurring plan if exists
                        $shopifyClient->deleteRecurringApplicationCharge(array(
                            'id' => (int) $recurringApplicationCharge['id']
                        ));

                        ShopifyBillings::where(array(
                            'account_id' => Auth::User()->account_id,
                            'charge_id' => $recurringApplicationCharge['id'],
                        ))->update(['status' => 'cancelled']);

//                        if($recurringApplicationCharge['test'] || $recurringApplicationCharge['test'] == 'true') {
//                            // Do nothing as this is test charge
//                        } else {
//                            if($recurringApplicationCharge['status'] == 'accepted') {
//                                // Do nothing as this is accepted but not active yet
//                            } else {
//                                // Delete Old recurring plan if exists
//                                $shopifyClient->deleteRecurringApplicationCharge(array(
//                                    'id' => (int) $recurringApplicationCharge->charge_id
//                                ));
//                            }
//                        }
                    }
                }

                /**
                 * Grab Free Plan and assign this to User
                 */
                $plan_data = ShopifyHelper::getFreePlan(Auth::User()->account_id);
                if($plan_data['status']) {
                    ShopifyShops::where(array(
                        'account_id' => Auth::User()->account_id,
                        'id' => $shop['id'],
                    ))->update(array(
                        'plan_id' => $plan_data['plan_id'],
                        'activated_on' => $plan_data['activated_on'],
                        'shopify_billing_id' => $plan_data['shopify_billing_id']
                    ));
                }

                flash('Your plan has been changed successfully.')->success()->important();
                return redirect()->route('admin.shopify_billings.index');
            } catch (\Exception $e) {
                flash('Unable to connect with Shopify, please try again later.')->error()->important();
                return redirect()->route('admin.shopify_billings.index');
            }
        }


        /**
         * Non Free Plan case
         */

        $shopifyClient = new ShopifyClient([
            'private_app' => false,
            'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
            'version' => env('SHOPIFY_API_VERSION'), // Put API Version
            'access_token' => $shop['access_token'],
            'shop' => $shop['myshopify_domain']
        ]);

        try {
            $recurringApplicationCharge = $shopifyClient->createRecurringApplicationCharge(array(
                'name' => $plan['name'],
                'price' => $plan['price'],
                'test' => env('SHOPIFY_BILLING_TEST_MODE'),
                'return_url' => url(route('admin.shopify_billings.callback')),
            ));

            if(isset($recurringApplicationCharge['id']) && $recurringApplicationCharge['id']) {
                $recurringApplicationCharge['charge_id'] = $recurringApplicationCharge['id'];
                unset($recurringApplicationCharge['id']);
                // Set Account ID
                $recurringApplicationCharge['account_id'] = Auth::User()->account_id;
                // Set Plan ID
                $recurringApplicationCharge['plan_id'] = $plan['id'];

                /**
                 * Create Billing before going to shopify
                 */
                ShopifyBillings::insert($recurringApplicationCharge);

                return redirect($recurringApplicationCharge['confirmation_url']);
            } else {
                flash('Unable to charge you in Shopify, please try again later.')->error()->important();
                return redirect()->route('admin.shopify_billings.index');
            }
        } catch (\Exception $e) {
            flash('Unable to connect with Shopify, please try again later.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

    }

    /**
     * Shopify Billing Callback
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'charge_id' => 'required',
        ]);

        if ($validator->fails()) {
            flash('Charge is not provided.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

        $shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();

        if(!$shop) {
            flash('Shop is not present, please contact support..')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        } else {
            $shop = $shop->toArray();
        }

        $shopifyClient = new ShopifyClient([
            'private_app' => false,
            'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
            'version' => env('SHOPIFY_API_VERSION'), // Put API Version
            'access_token' => $shop['access_token'],
            'shop' => $shop['myshopify_domain']
        ]);

        try {
            $recurringApplicationCharge = $shopifyClient->getRecurringApplicationCharge(array(
                'id' => (int) $request->get('charge_id')
            ));

            if(isset($recurringApplicationCharge['id']) && $recurringApplicationCharge['id']) {

                if($recurringApplicationCharge['status'] == 'accepted') {

                    $recurringCharge = ShopifyBillings::where(array(
                        'account_id' => Auth::User()->account_id,
                        'charge_id' => $recurringApplicationCharge['id'],
                    ))->latest('id')->first();

                    if(!$recurringCharge) {
                        flash('Provided charge does not exists in our system.')->error()->important();
                        return redirect()->route('admin.shopify_billings.index');
                    }

                    /**
                     * Approve this charge into shopify apps
                     */

                    $activateApplicationCharge = $shopifyClient->activateRecurringApplicationCharge(array(
                        'id' => (int) $recurringApplicationCharge['id']
                    ));

                    /**
                     * Update Billing record
                     */
                    $activateApplicationCharge['charge_id'] = $activateApplicationCharge['id'];
                    unset($activateApplicationCharge['id']);
                    // Set Account ID
                    $activateApplicationCharge['account_id'] = Auth::User()->account_id;
                    // Set Plan ID
                    $activateApplicationCharge['plan_id'] = $recurringCharge->plan_id;

                    ShopifyBillings::where(array(
                        'account_id' => Auth::User()->account_id,
                        'charge_id' => $recurringApplicationCharge['id'],
                    ))->update($activateApplicationCharge);

                    /**
                     * Change Store Plan ID
                     */
                    ShopifyShops::where(array(
                        'account_id' => Auth::User()->account_id,
                        'id' => $shop['id'],
                    ))->update(array(
                        'plan_id' => $recurringCharge->plan_id,
                        'activated_on' => Carbon::parse($recurringCharge->activated_on)->format('Y-m-d'),
                        'shopify_billing_id' => $recurringCharge->id
                    ));

                    flash('Your plan has been changed successfully.')->success()->important();
                    return redirect()->route('admin.shopify_billings.index');
                } else {
                    flash('We are sorry that you didn\'t change plan.')->error()->important();
                    return redirect()->route('admin.shopify_billings.index');
                }
            } else {
                flash('Unable to charge you in Shopify, please try again later.')->error()->important();
                return redirect()->route('admin.shopify_billings.index');
            }
        } catch (\Exception $e) {
            flash('We are sorry that you cannot change plan at this moment.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

    }

    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'plan_id' => 'required',
        ]);
    }
}
