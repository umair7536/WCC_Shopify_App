<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Products\SyncCustomersFire;
use App\Models\Accounts;
use App\Models\ShopifyCustomers;
use App\Models\ShopifyShops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Config;
use Validator;
use ZfrShopify\ShopifyClient;

class ShopifyCustomersController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('shopify_customers_manage')) {
            return abort(401);
        }

        return view('admin.shopify_customers.index');
    }

    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $ShopifyCustomers = ShopifyCustomers::getBulkData($request->get('id'));
            if($ShopifyCustomers) {
                foreach($ShopifyCustomers as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!ShopifyCustomers::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ShopifyCustomers::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $ShopifyCustomers = ShopifyCustomers::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($ShopifyCustomers) {

            foreach($ShopifyCustomers as $shopify_customer) {

            }

            foreach($ShopifyCustomers as $shopify_customer) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$shopify_customer->id.'"/><span></span></label>',
                    'first_name' => $shopify_customer->first_name,
                    'last_name' => $shopify_customer->last_name,
                    'email' => $shopify_customer->email,
                    'phone' => $shopify_customer->phone,
                    'city' => $shopify_customer->city,
                    'province' => $shopify_customer->province,
                    'actions' => view('admin.shopify_customers.actions', compact('shopify_customer'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('shopify_customers_create')) {
            return abort(401);
        }

        return view('admin.shopify_customers.create',compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('shopify_customers_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        /**
         * Set Customer's Basic Informaton
         */
        $shopifyCustomer = array(
          'first_name' => $request->get('first_name'),
          'last_name' => $request->get('last_name'),
          'email' => $request->get('email'),
          'phone' => $request->get('phone'),
          'verified_email' => ($request->get('email_verified') != '' && $request->get('email_verified') == '1') ? true : false,
        );

        /**
         * Set Customer's Registraton
         */
        if($request->get('password_confirmation') != '' && $request->get('password_confirmation') == '1') {
            $shopifyCustomer['password'] = $request->get('password');
            $shopifyCustomer['password_confirmation'] = $request->get('password');
            $shopifyCustomer['send_email_welcome'] = ($request->get('password_confirmation') != '' && $request->get('password_confirmation') == '1') ? true : false;
        }

        /**
         * Set Customer's Address
         */
        $shopifyCustomer['addresses'][0] = array(
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'address1' => $request->get('address1'),
            'city' => $request->get('city'),
            'province' => $request->get('province'),
            'phone' => $request->get('phone'),
            'zip' => $request->get('zip'),
            'country' => $request->get('country'),
        );

        /**
         * Set Optional Fields
         */
        if($request->get('company') != '') {
            $shopifyCustomer['addresses'][0]['company'] = $request->get('company');
        }
        if($request->get('address2') != '') {
            $shopifyCustomer['addresses'][0]['address2'] = $request->get('address2');
        }

        /**
         * Create Customer in Shopify
         */
        $shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();

        $customer = [];

        if($shop) {
            try {
                $shopifyClient = new ShopifyClient([
                    'private_app' => false,
                    'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                    'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                    'access_token' => $shop->access_token,
                    'shop' => $shop->myshopify_domain
                ]);

                $customer = $shopifyClient->createCustomer($shopifyCustomer);
            } catch (\Exception $e) {
                return response()->json(array(
                    'status' => 0,
//                    'message' => ['Email/ Phone is already in use or Country/ Province is wrong entered.'],
                    'message' => [$e->getMessage()],
                ));
            }
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => ['Shop is not connected, Please contact App Administrator.'],
            ));
        }

        if(count($customer)) {
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
                ShopifyCustomers::where([
                    'customer_id' => $customer_processed['customer_id'],
                    'account_id' => $customer_processed['account_id'],
                ])->update($customer_processed);
            } else {
                //echo 'Product Created: ' . $customer_processed['title'] . "\n";
                ShopifyCustomers::create($customer_processed);
            }

            flash('Record has been created successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => 'Record has been created successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => 'Something went wrong, please try again later.',
            ));
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
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ]);
    }

    /**
     * Show Lead detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        if (!Gate::allows('shopify_customers_manage')) {
            return abort(401);
        }

        $shopify_customer = ShopifyCustomers::getData($id);

        if(!$shopify_customer) {
            return view('error', compact('lead_statuse'));
        }

//        $customer_variants = ShopifyCustomerVariants::where([
//            'customer_id' => $shopify_customer->customer_id,
//            'customer_id' => $shopify_customer->customer_id,
//        ]);

        return view('admin.shopify_customers.detail', compact('shopify_customer'));
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('shopify_customers_edit')) {
            return abort(401);
        }

        $shopify_customer = ShopifyCustomers::getData($id);

        if(!$shopify_customer) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.shopify_customers.edit', compact('shopify_customer'));
    }

    /**
     * Update Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('shopify_customers_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $shopify_customer = ShopifyCustomers::getData($id);

        if(!$shopify_customer) {
            return response()->json(array(
                'status' => 0,
                'message' => ['Provided Customer not found in our database.'],
            ));
        }

        /**
         * Set Customer's Basic Informaton
         */
        $shopifyCustomer = array(
            'id' => (int) $shopify_customer['customer_id'],
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
//            'verified_email' => ($request->get('email_verified') != '' && $request->get('email_verified') == '1') ? true : false,
        );

        /**
         * Set Customer's Registraton
         */
//        if($request->get('password_confirmation') != '' && $request->get('password_confirmation') == '1') {
//            $shopifyCustomer['password'] = $request->get('password');
//            $shopifyCustomer['password_confirmation'] = $request->get('password');
//            $shopifyCustomer['send_email_welcome'] = ($request->get('password_confirmation') != '' && $request->get('password_confirmation') == '1') ? true : false;
//        }

        /**
         * Set Customer's Address
         */
//        $shopifyCustomer['addresses'][0] = array(
//            'first_name' => $request->get('first_name'),
//            'last_name' => $request->get('last_name'),
//            'address1' => $request->get('address1'),
//            'city' => $request->get('city'),
//            'province' => $request->get('province'),
//            'phone' => $request->get('phone'),
//            'zip' => $request->get('zip'),
//            'country' => $request->get('country'),
//        );


        /**
         * Create Customer in Shopify
         */
        $shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();

        $customer = [];

        if($shop) {
            try {
                $shopifyClient = new ShopifyClient([
                    'private_app' => false,
                    'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                    'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                    'access_token' => $shop->access_token,
                    'shop' => $shop->myshopify_domain
                ]);

                $customer = $shopifyClient->updateCustomer($shopifyCustomer);
            } catch (\Exception $e) {
                return response()->json(array(
                    'status' => 0,
//                    'message' => ['Email/ Phone is already in use or Country/ Province is wrong entered.'],
                    'message' => [$e->getMessage()],
                ));
            }
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => ['Shop is not connected, Please contact App Administrator.'],
            ));
        }

        if(count($customer)) {
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
                ShopifyCustomers::where([
                    'customer_id' => $customer_processed['customer_id'],
                    'account_id' => $customer_processed['account_id'],
                ])->update($customer_processed);
            } else {
                //echo 'Product Created: ' . $customer_processed['title'] . "\n";
                ShopifyCustomers::create($customer_processed);
            }

            flash('Record has been updated successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => 'Record has been updated successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => 'Something went wrong, please try again later.',
            ));
        }
    }


    /**
     * Remove Permission from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('shopify_customers_destroy')) {
            return abort(401);
        }

        ShopifyCustomers::deleteRecord($id);

        return redirect()->route('admin.shopify_customers.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('shopify_customers_inactive')) {
            return abort(401);
        }
        ShopifyCustomers::inactiveRecord($id);

        return redirect()->route('admin.shopify_customers.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('shopify_customers_active')) {
            return abort(401);
        }
        ShopifyCustomers::activeRecord($id);

        return redirect()->route('admin.shopify_customers.index');
    }

    /**
     * Dispatch event for sync customers.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncCustomers()
    {
        if (! Gate::allows('shopify_customers_manage')) {
            return abort(401);
        }

        event(new SyncCustomersFire(Accounts::find(Auth::User()->account_id)));

        flash('Customers Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.shopify_customers.index');
    }
}
