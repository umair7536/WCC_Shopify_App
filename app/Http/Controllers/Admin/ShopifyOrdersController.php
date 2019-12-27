<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Orders\SingleOrderFulfillmentFire;
use App\Events\Shopify\Orders\SyncOrdersFire;
use App\Helpers\ShopifyHelper;
use App\Models\Accounts;
use App\Models\BookedPackets;
use App\Models\LeopardsCities;
use App\Models\LeopardsSettings;
use App\Models\ShopifyCustomers;
use App\Models\ShopifyOrders;
use App\Models\ShopifyShops;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Auth;
use Validator;
use Config;
use ZfrShopify\ShopifyClient;

class ShopifyOrdersController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('shopify_orders_manage')) {
            return abort(401);
        }

        $financial_status = Config::get('constants.financial_status');
        $fulfillment_status = Config::get('constants.fulfillment_status');
        $shipment_types = Config::get('constants.shipment_type');

        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id,
        ])->get();

        if($leopards_cities) {
            $leopards_cities = $leopards_cities->pluck('name', 'name');
        } else {
            $leopards_cities = [];
        }

        return view('admin.shopify_orders.index', compact('financial_status', 'fulfillment_status', 'shipment_types', 'leopards_cities'));
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
            $response = $this->bulkActions($request);
            $records["customActionStatus"] = $response['status']; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = $response['message']; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ShopifyOrders::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $ShopifyOrders = ShopifyOrders::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($ShopifyOrders) {
            /**
             * On result time propare customer
             */
            $customer_ids = array();
            foreach($ShopifyOrders as $shopify_order) {
                $customer_ids[] = $shopify_order->customer_id;
            }

            $customers = ShopifyCustomers::whereIn('customer_id', $customer_ids)
                ->select('customer_id', 'name', 'email', 'phone', 'city', 'address1', 'address2')
                ->get()->keyBy('customer_id');

            $shop = ShopifyShops::where([
                'account_id' => Auth::User()->account_id,
            ])->first();

            foreach($ShopifyOrders as $shopify_order) {

                $customer = [$shopify_order->email];
                if(isset($customers[$shopify_order->customer_id])) {
                    $customer = [
                        ($shop) ? '<a target="_blank" href="https://' . $shop->myshopify_domain . '/admin/customers/' . $shopify_order->customer_id . '">' . $customers[$shopify_order->customer_id]->name . '&nbsp;<i class="fa fa-external-link"></i></a>' : $customers[$shopify_order->customer_id]->name,
                        $shopify_order->email,
                        $customers[$shopify_order->customer_id]->phone,
                    ];
                    $customer = array_filter($customer);
                }

                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$shopify_order->id.'"/><span></span></label>',
                    'name' => $shopify_order->name,
                    'closed_at' => Carbon::parse($shopify_order->created_at)->format('M j, Y h:i A'),
                    'customer_email' => implode('<br/>', $customer),
                    'fulfillment_status' => view('admin.shopify_orders.fulfillment_status', compact('shopify_order'))->render(),
                    'tags' => $shopify_order->tags,
                    'cn_number' => $shopify_order->cn_number,
                    'destination_city' => (isset($customers[$shopify_order->customer_id]) ? trim($customers[$shopify_order->customer_id]['city']) : ''),
                    'consignment_address' => (isset($customers[$shopify_order->customer_id]) ? trim($customers[$shopify_order->customer_id]['address1']) . ' ' . trim($customers[$shopify_order->customer_id]['address2']) : ''),
                    'total_price' => number_format($shopify_order->total_price),
                    'financial_status' => "<span class=\"label label-default\"> " . ucfirst($shopify_order->financial_status) . " </span>",
                    'actions' => view('admin.shopify_orders.actions', compact('shopify_order'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Book a single packet into LCS
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bookPacket(Request $request) {
        if($request->get('id') == '') {
            flash('Order ID is a required field.')->error()->important();
        } else {
            $data['id'] = [$request->get('id')];
            $data['customActionName'] = Config::get('constants.shipment_type_overnight'); //
            $data['customActionType'] = 'group_action'; //
            $data['skip_packet_checking'] = '1'; //

            $request->replace($data);
            $response = $this->bulkActions($request);
            if($response['status'] == 'NO') {
                flash($response['message'])->error()->important();
                return redirect()->route('admin.shopify_orders.index');
            } else {
                flash($response['message'])->success()->important();
                return redirect()->route('admin.shopify_orders.index');
            }
        }
    }

    /**
     * Bulk Book Packets
     *
     * @param Request $request
     * @return array
     */
    private function bulkActions(Request $request) : array {

        $account_id = Auth::User()->account_id;

        /**
         * Verify if correct Shipment Type is choosed or not
         */
        $found = false;
        $shipment_types = Config::get('constants.shipment_type');
        foreach($shipment_types as $shipment_id => $shipment_name) {
            if($request->get('customActionName') == $shipment_id) {
                $found = true;
                break;
            }
        }

        if(!$found) {
            return [
                'status' => 'NO',
                'message' => 'Please book with any of these options [' . implode(', ', $shipment_types) . ']'
            ];
        }
        // Correct shipment type verification ends here

        /**
         * If mode is test then stop booking packet in bulk.
         */
        $leopards_settings = LeopardsSettings::where([
            'account_id' => $account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');

        if($leopards_settings['mode']->data == '1') {
            return [
                'status' => 'NO',
                'message' => 'Test mode is enabled, <b><a target="_blank" href="' . route('admin.leopards_settings.index') . '">Click Here</a></b> to disable test mode.</b>'
            ];
        }

        /**
         * Check Booking Quota, If Quota is acceeding then stop booking
         */
        $result = $this->getCompanyData($account_id);
        if(!$result['status']) {
            return [
                'status' => 'NO',
                'message' => 'Leopards Credentials are invalid, <b><a target="_blank" href="' . route('admin.leopards_settings.index') . '">Click Here</a></b> to setup credentials again.</b>'
            ];
        }

        if (
            $request->get('customActionType') == "group_action"
        ) {
            $ids = $request->get('id');

            /**
             * Check Current Billing Cycle Quota and take action as per response
             */
            $checkQuota = ShopifyHelper::getQuota($account_id);

            if($checkQuota['status']) {
                if($checkQuota['info']) {
                    flash($checkQuota['info'])->info()->important();
                }
            } else {
                flash($checkQuota['error'])->error()->important();
                return [
                    'status' => 'NO',
                    'message' => $checkQuota['error']
                ];
            }

            // Check Booked Packets with provided Orders
            $orders = ShopifyOrders::where([
                'account_id' => $account_id
            ])->whereIn('id', $ids)
                ->select('order_id', 'name', 'order_number', 'customer_id', 'account_id')
                ->get();

            if($orders) {

                // Build Success Message
                $message = 'Below are your results:<br/>';
                $message .= '<ul>';

                $order_numbers = [];
                foreach ($orders as $order) {
                    $order_numbers[] = $order->order_number;
                }

                $booked_packets = BookedPackets::where([
                    'account_id' => $account_id
                ])
                    ->whereIn('order_id', $order_numbers)
                    ->select('order_id', 'cn_number', 'id')
                    ->orderBy('id', 'desc')
                    ->get()->keyBy('order_id');

                foreach ($orders as $order) {
                    /**
                     * Find already booked packet to avoid re-book
                     */
                    if($booked_packets->count()) {
                        if($request->get('skip_packet_checking') != '1') {
                            if(isset($booked_packets[$order->order_number])) {
                                $message .= '<li>Order <b>' . $order->name . '</b> is already booked with <b>' . $booked_packets[$order->order_number]->cn_number . '</b>. To book again <b><a target="_blank" href="' . route('admin.booked_packets.create',['order_id' => $order->order_id]) . '">Click Here</a></b>.</li>';
                                continue;
                            }
                        }
                    }

                    /**
                     * If Order ID is provided then prepare data to automatically be filled
                     */
                    $prepared_packet = BookedPackets::prepareBooking($order->order_id, $request->get('customActionName'), $account_id);
                    if($prepared_packet['status']) {
                        $booking_packet_request = new Request();
                        $booking_packet_request->replace($prepared_packet['packet']);

                        /**
                         * Verify Fields before send
                         */
                        $validator = $this->verifyPacketFields($booking_packet_request);
                        if ($validator->fails()) {
                            $message .= '<li>Order <b>' . $order->name . '</b> has some issues. [' . implode(', ', $validator->messages()->all()) . ']';
                            continue;
                        }

                        $result = BookedPackets::createRecord($booking_packet_request, $account_id);

                        /**
                         * Add Booking information into Order
                         */
                        if($result['status']) {
                            $booked_packet = BookedPackets::where([
                                'account_id' => $account_id,
                                'id' => $result['record_id'],
                            ])->first();

                            ShopifyOrders::where([
                                'order_id' => $order->order_id,
                                'account_id' => $account_id,
                            ])->update(array(
                                'booking_id' => $booked_packet->id,
                                'cn_number' => $booked_packet->cn_number
                            ));

                            $message .= '<li>Order <b>' . $order->name . '</b> has been booked. Assigned CN # is ' . $booked_packet->cn_number;

                            /**
                             * Dispatch to check if Auto Fulfillment is 'true' or 'false'
                             * if 'true' then order will be fulfilled automatically
                             * if 'false' then order will not be fulfilled
                             */
                            event(new SingleOrderFulfillmentFire($order->toArray(), $booked_packet->cn_number));
                        }
                    } else {
                        $message .= '<li>Order <b>' . $order->name . '</b> has consignee city issue. Please select proper city for this packet to book.';
                        continue;
                    }
                }

                $message .= '</ul>';

                return [
                    'status' => 'OK',
                    'message' => $message
                ];
            }
        }

        return [
            'status' => 'NO',
            'message' => 'Something went wrong, please try again later'
        ];
    }

    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyPacketFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'shipment_type_id' => 'required',
            'booking_date' => 'required',
            'packet_pieces' => 'required|numeric',
            'net_weight' => 'required|numeric',
            'collect_amount' => 'required|numeric',
            'order_id' => 'nullable|numeric',
            'vol_weight_w' => 'nullable|numeric',
            'vol_weight_h' => 'nullable|numeric',
            'vol_weight_l' => 'nullable|numeric',
            'shipper_id' => 'required',
            'origin_city' => 'required',
            'shipper_name' => 'required',
            'shipper_email' => 'nullable',
            'shipper_phone' => 'required',
            'shipper_address' => 'required',
            'consignee_id' => 'required',
            'destination_city' => 'required',
            'consignee_name' => 'required',
            'consignee_email' => 'nullable|email',
            'consignee_phone' => 'required',
            'consignee_address' => 'required',
            'comments' => 'required',
        ]);
    }


    /**
     * Get Company Data from LCS.
     *
     * @param int
     * @return array
     */
    private function getCompanyData($account_id)
    {
        $data = array(
            'status' => true,
            'company' => array(),
        );

        $leopards_settings = LeopardsSettings::where([
            'account_id' => $account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');

        if($leopards_settings) {
            foreach($leopards_settings as $leopards_setting) {
                if($leopards_setting->slug == 'company-id' && !$leopards_setting->data) {
                    $data['status'] = false;
                } else if(
                    ($leopards_setting->slug == 'api-key' && !$leopards_setting->data)
                    ||  ($leopards_setting->slug == 'api-password' && !$leopards_setting->data)
                ) {
                    $data['status'] = false;
                }
            }
        }

        try {
            $client = new Client();
            $response = $client->post(env('LCS_URL') . 'common_calls/getCountryById', array(
                'form_params' => array(
                    'company_id' => $leopards_settings['company-id']->data
                )
            ));

            if($response->getStatusCode() == 200) {
                if($response->getBody() != 'null') {
                    $data['company'] = json_decode($response->getBody(), true);
                } else {
                    $data['status'] = false;
                }
            } else {
                $data['status'] = false;
            }
        } catch (\Exception $exception) {
            $data['status'] = false;
        }

        return $data;
    }

    /**
     * Show the form for creating new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('shopify_orders_create')) {
            return abort(401);
        }

        return view('admin.shopify_orders.create',compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('shopify_orders_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyOrders::createRecord($request, Auth::User()->account_id, Auth::User()->id)) {
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
            'title' => 'required',
        ]);
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('shopify_orders_edit')) {
            return abort(401);
        }

        $shopify_order = ShopifyOrders::getData($id);

        if(!$shopify_order) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.shopify_orders.edit', compact('shopify_order'));
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
        if (! Gate::allows('shopify_orders_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyOrders::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('shopify_orders_destroy')) {
            return abort(401);
        }

        ShopifyOrders::deleteRecord($id);

        return redirect()->route('admin.shopify_orders.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('shopify_orders_inactive')) {
            return abort(401);
        }
        ShopifyOrders::inactiveRecord($id);

        return redirect()->route('admin.shopify_orders.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('shopify_orders_active')) {
            return abort(401);
        }
        ShopifyOrders::activeRecord($id);

        return redirect()->route('admin.shopify_orders.index');
    }

    /**
     * Dispatch event for sync custom collections.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncOrders()
    {
        if (! Gate::allows('shopify_orders_manage')) {
            return abort(401);
        }

        event(new SyncOrdersFire(Accounts::find(Auth::User()->account_id)));

        flash('Orders Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.shopify_orders.index');
    }


    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyBookFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'id' => 'required',
            'shop' => 'required',
        ]);
    }

    /**
     * Incoming Order booking preperation
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function book(Request $request)
    {
        if (! Gate::allows('shopify_orders_manage')) {
            return abort(401);
        }

        $validator = $this->verifyBookFields($request);

        if (!$validator->fails()) {

            /**
             * Grab Shop
             */
            $shop = ShopifyShops::where([
                'account_id' => Auth::User()->account_id
            ])->first();

            if($shop) {
                try {

                    /**
                     * Prepare Shopify Request
                     */
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
                    $order = $shopifyClient->getOrder([
                        'id' => (int) $request->get('id')
                    ]);

                    $id = ShopifyHelper::syncSingleOrder($order, $shop->toArray());

                    return redirect()->route('admin.shopify_orders.book_packet',['id' => $id]);

                } catch (\Exception $exception) {}
            }
        }

        flash('Incoming request is not valid.')->error()->important();
        return redirect()->route('admin.shopify_orders.index');
    }

}
