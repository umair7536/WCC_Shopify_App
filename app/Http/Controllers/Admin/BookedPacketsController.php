<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Products\SyncCustomersFire;
use App\Models\Accounts;
use App\Models\BookedPackets;
use App\Models\LeopardsCities;
use App\Models\Shippers;
use App\Models\ShopifyShops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Config;
use Validator;
use ZfrShopify\ShopifyClient;

class BookedPacketsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        $status = Config::get('constants.status');
        $shipment_type = Config::get('constants.shipment_type');
        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id,
        ])->get();

        if($leopards_cities) {
            $leopards_cities = $leopards_cities->pluck('name', 'city_id');
        } else {
            $leopards_cities = [];
        }

        return view('admin.booked_packets.index', compact('status', 'shipment_type', 'leopards_cities'));
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
            $BookedPackets = BookedPackets::getBulkData($request->get('id'));
            if($BookedPackets) {
                foreach($BookedPackets as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!BookedPackets::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = BookedPackets::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $BookedPackets = BookedPackets::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($BookedPackets) {

            foreach($BookedPackets as $booked_packet) {

            }

            foreach($BookedPackets as $booked_packet) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$booked_packet->id.'"/><span></span></label>',
                    'first_name' => $booked_packet->first_name,
                    'last_name' => $booked_packet->last_name,
                    'email' => $booked_packet->email,
                    'phone' => $booked_packet->phone,
                    'city' => $booked_packet->city,
                    'province' => $booked_packet->province,
                    'actions' => view('admin.booked_packets.actions', compact('booked_packet'))->render(),
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
        if (! Gate::allows('booked_packets_create')) {
            return abort(401);
        }

        $booking_date = Carbon::now()->format('Y-m-d');

        // Default shipment selection
        $default_shipment_type = '10';

        /**
         * Manage Shipment Type
         */
        $shipment_type = Config::get('constants.shipment_type');
        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id,
        ])->get();
        if($leopards_cities) {
            $leopards_cities = $leopards_cities->pluck('name', 'city_id');
        } else {
            $leopards_cities = [];
        }

        // Volumetric Dimensions Calculated
        $volumetric_dimensions_calculated = 'N/A';

        /**
         * Manage Shippers
         */
        $shipper_id = 'self';
        $shippers = Shippers::where([
            'account_id' => Auth::User()->account_id,
        ])
            ->select('id', 'name')
            ->get();
        if($shippers) {
            $shippers = $shippers->pluck('name', 'id')->toArray();
        } else {
            $shippers = [];
        }
        $shippers = (['self' => 'Self'] + $shippers + ['other' => 'Other']);

        /**
         * Manage Consignees
         */
        $consignee_id = 'other';
        $consignees = ['other' => 'Other'];

        return view('admin.booked_packets.create',compact('booking_date', 'default_shipment_type', 'shipment_type', 'volumetric_dimensions_calculated', 'leopards_cities', 'shippers', 'consignees', 'shipper_id', 'consignee_id'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('booked_packets_create')) {
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

            $customer_processed = BookedPackets::prepareRecord($customer);
            $customer_processed['account_id'] = $shop['account_id'];

            $customer_record = BookedPackets::where([
                'customer_id' => $customer_processed['customer_id'],
                'account_id' => $customer_processed['account_id'],
            ])->select('id')->first();

            if($customer_record) {
                BookedPackets::where([
                    'customer_id' => $customer_processed['customer_id'],
                    'account_id' => $customer_processed['account_id'],
                ])->update($customer_processed);
            } else {
                //echo 'Product Created: ' . $customer_processed['title'] . "\n";
                BookedPackets::create($customer_processed);
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
        if (!Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        $booked_packet = BookedPackets::getData($id);

        if(!$booked_packet) {
            return view('error', compact('lead_statuse'));
        }

//        $customer_variants = BookedPacketVariants::where([
//            'customer_id' => $booked_packet->customer_id,
//            'customer_id' => $booked_packet->customer_id,
//        ]);

        return view('admin.booked_packets.detail', compact('booked_packet'));
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('booked_packets_edit')) {
            return abort(401);
        }

        $booked_packet = BookedPackets::getData($id);

        if(!$booked_packet) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.booked_packets.edit', compact('booked_packet'));
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
        if (! Gate::allows('booked_packets_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $booked_packet = BookedPackets::getData($id);

        if(!$booked_packet) {
            return response()->json(array(
                'status' => 0,
                'message' => ['Provided Customer not found in our database.'],
            ));
        }

        /**
         * Set Customer's Basic Informaton
         */
        $shopifyCustomer = array(
            'id' => (int) $booked_packet['customer_id'],
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

            $customer_processed = BookedPackets::prepareRecord($customer);
            $customer_processed['account_id'] = $shop['account_id'];

            $customer_record = BookedPackets::where([
                'customer_id' => $customer_processed['customer_id'],
                'account_id' => $customer_processed['account_id'],
            ])->select('id')->first();

            if($customer_record) {
                BookedPackets::where([
                    'customer_id' => $customer_processed['customer_id'],
                    'account_id' => $customer_processed['account_id'],
                ])->update($customer_processed);
            } else {
                //echo 'Product Created: ' . $customer_processed['title'] . "\n";
                BookedPackets::create($customer_processed);
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
        if (! Gate::allows('booked_packets_destroy')) {
            return abort(401);
        }

        BookedPackets::deleteRecord($id);

        return redirect()->route('admin.booked_packets.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('booked_packets_inactive')) {
            return abort(401);
        }
        BookedPackets::inactiveRecord($id);

        return redirect()->route('admin.booked_packets.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('booked_packets_active')) {
            return abort(401);
        }
        BookedPackets::activeRecord($id);

        return redirect()->route('admin.booked_packets.index');
    }

    /**
     * Dispatch event for sync customers.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncCustomers()
    {
        if (! Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        event(new SyncCustomersFire(Accounts::find(Auth::User()->account_id)));

        flash('Customers Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.booked_packets.index');
    }
}
