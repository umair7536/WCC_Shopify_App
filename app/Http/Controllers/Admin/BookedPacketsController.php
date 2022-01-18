<?php

namespace App\Http\Controllers\Admin;

use App\Events\Leopards\BookedPackets\FullSyncPacketStatusFire;
use App\Events\Shopify\Orders\SingleOrderFulfillmentFire;
use App\Helpers\LeopardsHelper;
use App\Helpers\ShopifyHelper;
use App\Models\Accounts;
use App\Models\BookedPackets;
use App\Models\WccBookedPackets;
use App\Models\JsonOrders;
use App\Models\LeopardsCities;
use App\Models\WccCities;
use App\Models\LeopardsSettings;
use App\Models\LoadSheets;
use App\Models\Shippers;
use App\Models\ShopifyJobs;
use App\Models\ShopifyLocations;
use App\Models\ShopifyOrders;
use App\Models\ShopifyShops;
use Carbon\Carbon;
use Developifynet\LeopardsCOD\LeopardsCOD;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
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
        $shipment_type = Config::get('constants.wcc_shipment_type');
        $wcc_cities = WccCities::where([
            'account_id' => Auth::User()->account_id,
        ])->get();

        if($wcc_cities) {
            $wcc_cities = $wcc_cities->pluck('name', 'city_id');
        } else {
            $wcc_cities = [];
        }

        return view('admin.booked_packets.index', compact('status', 'shipment_type', 'wcc_cities'));
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


//        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
//            $BookedPackets = BookedPackets::getBulkData($request->get('id'));
//            if($BookedPackets) {
//                foreach($BookedPackets as $city) {
//                    // Check if child records exists or not, If exist then disallow to delete it.
//                    if(!BookedPackets::isChildExists($city->id, Auth::User()->account_id)) {
//                        $city->delete();
//                    }
//                }
//            }
//            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
//            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
//        }

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $response = $this->bulkActions($request);
            $records["customActionStatus"] = $response['status']; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = $response['message']; // pass custom message(useful for getting status of group actions)
        }

        /**
         * Handle Packet as Production or Test
         * '1' as Test Mode
         * '2' as Production Mode
         */
        $booking_type = 2;

        // Get Total Records
        $iTotalRecords = BookedPackets::getTotalRecords($request, Auth::User()->account_id, $booking_type);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $BookedPackets = BookedPackets::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $booking_type);

        if($BookedPackets) {

            $cities = [];
            foreach($BookedPackets as $booked_packet) {
                $cities[] = $booked_packet->origin_city;
                $cities[] = $booked_packet->destination_city;
            }

            $shipment_type = Config::get('constants.shipment_type');
            $status = Config::get('constants.status');

            $wcc_cities = WccCities::where([
                'account_id' => Auth::User()->account_id,
            ])->whereIn('city_id', $cities)
                ->select('city_id', 'name')
                ->get();
            if($wcc_cities) {
                $wcc_cities = $wcc_cities->keyBy('city_id');
            } else {
                $wcc_cities = [];
            }

            foreach($BookedPackets as $booked_packet) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$booked_packet->id.'"/><span></span></label>',
                    'status' => $status[$booked_packet->status],
                    'order_id' => $booked_packet->order_id,
                    'shipment_type_id' => $shipment_type[$booked_packet->shipment_type_id],
                    'cn_number' => $booked_packet->cn_number,
//                    'origin_city' => isset($wcc_cities[$booked_packet->origin_city]) ? $wcc_cities[$booked_packet->origin_city]->name : 'n/a',
                    'destination_city' => isset($wcc_cities[$booked_packet->destination_city]) ? $wcc_cities[$booked_packet->destination_city]->name : 'n/a',
//                    'shipper_name' => $booked_packet->shipper_name,
//                    'consignee_name' => $booked_packet->consignee_name,
                    'consignee_phone' => $booked_packet->consignee_phone,
//                    'consignee_email' => ($booked_packet->consignee_email) ? $booked_packet->consignee_email : 'n/a',
                    'booking_date' => $booked_packet->booking_date,
                    'invoice_number' => $booked_packet->invoice_number,
                    'invoice_date' => $booked_packet->invoice_date,
                    'collect_amount' => number_format($booked_packet->collect_amount, 2),
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
     * Display a listing of API Booked Packets.
     *
     * @return \Illuminate\Http\Response
     */
    public function api()
    {
        if (! Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        $status = Config::get('constants.status');
        $shipment_type = Config::get('constants.shipment_type');
        $wcc_cities = WccCities::where([
            'account_id' => Auth::User()->account_id,
        ])->get();

        if($wcc_cities) {
            $wcc_cities = $wcc_cities->pluck('name', 'city_id');
        } else {
            $wcc_cities = [];
        }

        return view('admin.booked_packets.api', compact('status', 'shipment_type', 'wcc_cities'));
    }

    /**
     * Display a listing of Booked Packets.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function apidatatable(Request $request)
    {
        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $response = $this->bulkActions($request);
            $records["customActionStatus"] = $response['status']; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = $response['message']; // pass custom message(useful for getting status of group actions)
        }

        /**
         * Handle Packet as Production or Test
         * '1' as Test Mode
         * '2' as Production Mode
         */
        $booking_type = 1;

        // Get Total Records
        $iTotalRecords = BookedPackets::getTotalRecords($request, Auth::User()->account_id, $booking_type);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $BookedPackets = BookedPackets::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id, $booking_type);

        if($BookedPackets) {

            $cities = [];
            foreach($BookedPackets as $booked_packet) {
                $cities[] = $booked_packet->origin_city;
                $cities[] = $booked_packet->destination_city;
            }

            $shipment_type = Config::get('constants.shipment_type');
            $status = Config::get('constants.status');

            $wcc_cities = WccCities::where([
                'account_id' => Auth::User()->account_id,
            ])->whereIn('city_id', $cities)
                ->select('city_id', 'name')
                ->get();
            if($wcc_cities) {
                $wcc_cities = $wcc_cities->keyBy('city_id');
            } else {
                $wcc_cities = [];
            }

            foreach($BookedPackets as $booked_packet) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$booked_packet->id.'"/><span></span></label>',
                    'status' => $status[$booked_packet->status],
                    'order_id' => $booked_packet->order_id,
                    'shipment_type_id' => $shipment_type[$booked_packet->shipment_type_id],
                    'cn_number' => $booked_packet->cn_number,
                    'origin_city' => isset($wcc_cities[$booked_packet->origin_city]) ? $wcc_cities[$booked_packet->origin_city]->name : 'n/a',
                    'destination_city' => isset($wcc_cities[$booked_packet->destination_city]) ? $wcc_cities[$booked_packet->destination_city]->name : 'n/a',
                    'shipper_name' => $booked_packet->shipper_name,
                    'consignee_name' => $booked_packet->consignee_name,
                    'consignee_phone' => $booked_packet->consignee_phone,
                    'consignee_email' => ($booked_packet->consignee_email) ? $booked_packet->consignee_email : 'n/a',
                    'booking_date' => $booked_packet->booking_date,
                    'collect_amount' => number_format($booked_packet->collect_amount, 2),
                    'actions' => view('admin.booked_packets.apiactions', compact('booked_packet'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Bulk Book Packets Actions
     *
     * @param Request $request
     * @return array
     */
    private function bulkActions(Request $request) : array {

        $account_id = Auth::User()->account_id;

        /**
         * Check Booking Quota, If Quota is acceeding then stop booking
         */
        $result = $this->getCompanyData($account_id);
        if(!$result['status']) {
            return [
                'status' => 'NO',
                'message' => 'Wcc Credentials are invalid, <b><a target="_blank" href="' . route('admin.Wcc_settings.index') . '">Click Here</a></b> to setup credentials again.</b>'
            ];
        }

        if (
            $request->get('customActionType') == "group_action"
            && $request->get('customActionName') == "cancel"
        ) {
            $ids = $request->get('id');

            $booked_packets = BookedPackets::where([
                'account_id' => $account_id
            ])
                ->whereIn('id', $ids)
                ->select('id', 'cn_number', 'account_id')
                ->orderBy('id', 'desc')
                ->get();

            if($booked_packets->count()) {

                /**
                 * Variable to track if any response was successful
                 */
                $any_success = false;

                // Build Success Message
                $message = 'Below are your results:<br/>';
                $message .= '<ul>';

                foreach ($booked_packets as $booked_packet) {
                    $response = BookedPackets::cancelBookedPacket($booked_packet->cn_number, $booked_packet->account_id);
                    if($response['status']) {
                        // Any of the packets get success
                        $any_success = true;

                        $message .= '<li>CN # <b>' . $booked_packet->cn_number . '</b> has been cancelled.';
                        /**
                         * Update Packet status to Cancel
                         */
                        $booked_packet->update(['status' => Config::get('constants.status_cancel')]);
                    } else {
                        $message .= '<li>CN # <b>' . $booked_packet->cn_number . '</b> has some issues. [' . $response['message'] . ']';
                    }
                }

                $message .= '</ul>';

                return [
                    'status' => ($any_success) ? 'OK' : 'NO',
                    'message' => $message
                ];
            }
        }

        if (
            $request->get('customActionType') == "group_action"
            && $request->get('customActionName') == "fulfill"
        ) {
            $ids = $request->get('id');

            $booked_packets = BookedPackets::where([
                'account_id' => $account_id
            ])
                ->whereIn('id', $ids)
                ->select('id', 'cn_number', 'account_id', 'order_id', 'order_number')
                ->orderBy('id', 'desc')
                ->get();

            if($booked_packets->count()) {

                /**
                 * Fulfill this order
                 */
                $shop = ShopifyShops::where([
                    'account_id' => $account_id
                ])->first();

                /**
                 * Fetch default Inventory Location ID
                 */
                $inventory_location = LeopardsSettings::getDefaultInventoryLocation($account_id);

                /**
                 * Variable to track if any response was successful
                 */
                $any_success = false;

                // Build Success Message
                $message = 'Below are your results:<br/>';
                $message .= '<ul>';

                foreach ($booked_packets as $booked_packet) {
                    echo "test";
                    exit();
                    $order = ShopifyOrders::where([
                        'account_id' => $booked_packet->account_id,
                        'order_number' => $booked_packet->order_number,
                    ])
                        ->select('id', 'order_id', 'account_id', 'order_number')
                        ->first();

                    if($shop && $inventory_location && $order) {

                        $shopifyClient = new ShopifyClient([
                            'private_app' => false,
                            'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                            'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                            'access_token' => $shop->access_token,
                            'shop' => $shop->myshopify_domain
                        ]);

                        try {
                            $fullfilments = array();
                            $fullfilments = $shopifyClient->getFulfillments([
                                'order_id' => (int) $order['order_id']
                            ]);

                            if(count($fullfilments)) {
                                $message .= '<li><b>Order: ' . $booked_packet->order_id . ' CN #: ' . $booked_packet->cn_number . '</b> is already fulfilled.';
                            } else {
                                try {
                                    $fulfillment = $shopifyClient->createFulfillment(array(
                                        'order_id' => (int) $order['order_id'],
                                        'location_id' => $inventory_location,
                                        'tracking_number' => $booked_packet->cn_number,
                                        'tracking_company' => 'WCC',
                                        'notify_customer' => true,
                                        'tracking_urls' => array(
                                            route('track', $booked_packet->cn_number)
                                        ),
                                    ));

                                    // Packet has been fulfilled
                                    $message .= '<li><b>Order: ' . $booked_packet->order_id . ' CN #: ' . $booked_packet->cn_number . '</b> marked as fulfilled.';

                                    $any_success = true;
                                } catch (\Exception $exception) {
                                    $message .= '<li><b>Order: ' . $booked_packet->order_id . ' CN #: ' . $booked_packet->cn_number . '</b> has warehouse issue.';
                                }
                            }
                        } catch (\Exception $exception) {}
                    } else {
                        $message .= '<li><b>Order: ' . $booked_packet->order_id . ' CN #: ' . $booked_packet->cn_number . '</b> can not be fulfilled.';
                    }
                }

                $message .= '</ul>';

                return [
                    'status' => ($any_success) ? 'OK' : 'NO',
                    'message' => $message
                ];
            }
        }


        if (
            $request->get('customActionType') == "group_action"
            && $request->get('customActionName') == "loadsheet"
        ) {
            $ids = $request->get('id');

            $status = Config::get('constants.status');

            $booked_packets = BookedPackets::where([
                'account_id' => $account_id
            ])
                ->whereIn('id', $ids)
                ->select('id', 'cn_number', 'status', 'account_id', 'order_id')
                ->orderBy('id', 'desc')
                ->get();

            if($booked_packets->count()) {

                /**
                 * Variable to track if any response was successful
                 */
                $any_success = false;

                // Build Success Message
                $message = 'Below are your results:<br/>';
                $message .= '<ul>';

                $cn_numbers = [];
                $packets = [];

                foreach ($booked_packets as $booked_packet) {
                    if($booked_packet['status'] == '0') {
                        $cn_numbers[] = $booked_packet['cn_number'];
                        $packets[] = $booked_packet;
                    } else {
                        $message .= '<li>CN # <b>' . $booked_packet->cn_number . '</b> must have in ' . $status[0] . ' state.';
                    }
                }

                if(count($cn_numbers)) {
                    $response = BookedPackets::generateLoadSheet($cn_numbers, $booked_packet->account_id);
                    if($response['status']) {
                        // Any of the packets get success
                        $any_success = true;

                        /**
                         * Create Load Sheet Data
                         */
                        $load_sheet = LoadSheets::createLoadSheet($response['load_sheet_id'], $packets, $account_id);

                        /**
                         * Change Packet Status to 'Pickup Request Sent'
                         */
                        BookedPackets::where([
                            'account_id' => $account_id
                        ])
                            ->whereIn('cn_number', $cn_numbers)
                            ->update([
                                'status' => Config::get('constants.status_pickup_request_sent')
                            ]);

                        $message .= '<li>Load Sheet # <b>' . $load_sheet->load_sheet_id . '</b> is generated successfully. ' . '<a href="' . route('admin.load_sheets.download',[$load_sheet->id]) . '"><b>Click here</b></a>' . ' to download it or visit ' . '<a href="' . route('admin.load_sheets.index') . '"><b>Load Sheets</b></a>' . ' menu to visit all sheets.</li>';
                    } else {
                        $message .= '<li>System failed to generate Load Sheet, Please try again later.</li>';
                    }
                }

                $message .= '</ul>';

                return [
                    'status' => ($any_success) ? 'OK' : 'NO',
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
     * Get Company Data from WCC.
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

        $wcc_settings = LeopardsSettings::where([
            'account_id' => $account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');

        if($wcc_settings) {
            foreach($wcc_settings as $wcc_setting) {
                if(
                    ($wcc_setting->slug == 'username' && !$wcc_setting->data)
                    ||  ($wcc_setting->slug == 'password' && !$wcc_setting->data)
                ) {
                    $data['status'] = false;
                }
            }
        }

        $data['company']['company_name_eng'] = $wcc_settings['shipper-name']->data;
        $data['company']['company_email'] = $wcc_settings['shipper-email']->data;
        $data['company']['company_phone'] = $wcc_settings['shipper-phone']->data;
        $data['company']['company_address1_eng'] = $wcc_settings['shipper-address']->data;
        $data['company']['tbl_lcs_city_city_id'] = $wcc_settings['shipper-city']->data;

        return $data;
    }

    /**
     * Show the form for creating new Permission.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! Gate::allows('booked_packets_create')) {
            return abort(401);
        }

        /**
         * Grab Company Information from WCC System
         */
        $data = $this->getCompanyData(Auth::User()->account_id);
        if(!$data['status']) {
            if (Gate::allows('leopards_settings_manage')) {
                flash('WCC Credentials are invalid, Please provide correct credentials.')->error()->important();
                return redirect()->route('admin.leopards_settings.index');
            } else {
                flash('WCC Credentials are invalid, Please provide correct credentials.')->error()->important();
                return redirect()->route('admin.booked_packets.index');
            }
        }

        /**
         * If Json Based Order is present, make process faster
         */
        if($request->get('json_order') != '') {
            try {
                /**
                 * Store Data in JSON Data table
                 */
                $json_order = JsonOrders::where([
                    'account_id' => Auth::User()->account_id,
                    'id' => base64_decode($request->get('json_order'))
                ])->first();
                if(!$json_order) {
                    throw new \Exception('Order ID Not found');
                } else {
                    $order = json_decode($json_order->json_data, true);
                }
                if(
                    (
                        isset($order['id']) && isset($order['customer'])
                    ) && count($order['customer'])
                ) {
                    /**
                     * If Order ID is provided then prepare data to automatically be filled
                     */
                    $data['booked_packet'] = BookedPackets::prepareJSONBookingOrder($order, Auth::User()->account_id);
                } else {
                    throw new \Exception('Order ID Not found');
                }
            } catch (\Exception $exception) {
                /**
                 * If Order ID is provided then prepare data to automatically be filled
                 */
                $data['booked_packet'] = BookedPackets::prepareBookingOrder($request->get('order_id'), Auth::User()->account_id);
            }
        } else {
            /**
             * If Order ID is provided then prepare data to automatically be filled
             */
            $data['booked_packet'] = BookedPackets::prepareBookingOrder($request->get('order_id'), Auth::User()->account_id);
        }

        $booking_date = Carbon::now()->format('Y-m-d');

        // Default shipment selection
        $default_shipment_type = '2';

        /**
         * Manage Shipment Type
         */
        $shipment_type = Config::get('constants.wcc_shipment_type');
        $wcc_cities = WccCities::where([
            'account_id' => Auth::User()->account_id,
        ])->where(['city_id'=>'KHI'])
            ->orderBy('name', 'asc')
            ->get();
        if($wcc_cities) {
            $wcc_cities = $wcc_cities->pluck('name', 'city_id');
        } else {
            $wcc_cities = [];
        }

        // Volumetric Dimensions Calculated
        $volumetric_dimensions_calculated = 'N/A';

        $shippers = [];
        $shippers = (['self' => 'Self'] + $shippers + ['other' => 'Other']);

        /**
         * Manage Consignees
         */
        $consignee_id = 'other';
        $consignees = ['other' => 'Other'];

        return view('admin.booked_packets.create',compact('booking_date', 'default_shipment_type', 'shipment_type', 'volumetric_dimensions_calculated', 'wcc_cities', 'shippers', 'consignees', 'consignee_id', 'data'));
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
//        $st=$this->book_wcc_packet($request);
        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }





        $result = BookedPackets::createRecord($request, Auth::User()->account_id);

        if($result['status']) {

            if($result['test_mode']) {
                flash('Test Packet is booked successfully.')->success()->important();
            } else {
                flash('Packet is booked successfully.')->success()->important();
            }

            $order = ShopifyOrders::where([
                'account_id' => Auth::User()->account_id,
                'order_number' => $result['record']->order_number
            ])->first();

            if($order) {

                /**
                 * Dispatch to check if Auto Fulfillment is 'true' or 'false'
                 * if 'true' then order will be fulfilled automatically
                 * if 'false' then order will not be fulfilled
                 */
                event(new SingleOrderFulfillmentFire($order->toArray(), $result['record']->cn_number));
            }

            return response()->json(array(
                'status' => 1,
                'test_mode' => $result['test_mode'],
                'slip_link' => $result['record']->slip_link,
                'message' => 'Record has been created successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => [$result['error_msg']],
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
            'shipment_type_id' => 'required',
            'booking_date' => 'required',
            'packet_pieces' => 'required|numeric',
            'net_weight' => 'required|numeric',
            'collect_amount' => 'required|numeric',
            'order_number' => 'required|numeric',
            'order_id' => 'nullable',
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
            'product_description'=>'required',
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

        $booked_packet1 = BookedPackets::where([
            ['id','=',$id],
            ['account_id','=', Auth::User()->account_id]
        ])->first();

        $booked_packet = BookedPackets::getData($id);

        if(!$booked_packet) {
            return view('error');
        }

        $shipment_type = Config::get('constants.wcc_shipment_type');
        $wcc_cities = WccCities::where([
        'city_id'=>'KHI'
        ])->value('name');

        // if($wcc_cities) {
        //     $wcc_cities = $wcc_cities->keyBy('city_id');
        // } else {
        //     $wcc_cities = [];
        // }


        return view('admin.booked_packets.detail', compact('booked_packet', 'shipment_type', 'wcc_cities'));
    }

    /**
     * Show Lead detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function fulfill($id)
    {
        if (!Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        $booked_packet = BookedPackets::where([
            ['id','=',$id],
            ['account_id','=', Auth::User()->account_id]
        ])->first();


        if(!$booked_packet) {
            return view('error');
        }


        $shopify_order = ShopifyOrders::where([
            ['cn_number','=',$booked_packet->cn_number],
            ['account_id','=', Auth::User()->account_id]
        ])
            ->select('order_id')
            ->first();
        



        /**
         * Fetch default Inventory Location ID
         */
        $inventory_location = LeopardsSettings::getDefaultInventoryLocation(Auth::User()->account_id);

        /**
         * Fulfill this order
         */
        $shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();



        $fullfilments = array();

        if($shop) {


            try {
                $shopifyClient = new ShopifyClient([
                    'private_app' => false,
                    'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                    'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                    'access_token' => $shop->access_token,
                    'shop' => $shop->myshopify_domain
                ]);

                $fullfilments = $shopifyClient->getFulfillments([
                    'order_id' => (int) $shopify_order->order_id
                ]);
            } catch (\Exception $exception) {

            }
        }

        $val_num= ShopifyOrders::where([
            ['cn_number','=',$booked_packet->cn_number],
            ['account_id','=', Auth::User()->account_id]
        ])
            ->select('order_number')
            ->first();
        $booked_packet['order_number']= $val_num->order_number;



        if(count($fullfilments)) {
            return view('admin.booked_packets.already_fulfilled', compact('booked_packet', 'shopify_locations', 'fullfilments', 'shopify_order'));
        } else {
            $shopify_locations = ShopifyLocations::where([
                'account_id' => Auth::User()->account_id
            ])->get()->pluck('name', 'location_id');
            // var_dump($shopify_order);
            // var_dump($fullfilments);


            return view('admin.booked_packets.fulfill', compact('booked_packet', 'shopify_locations', 'fullfilments', 'shopify_order', 'inventory_location'));
        }
    }
    public function book_wcc_packet(Request $request)
    {
        $username= LeopardsSettings::where('account_id', Auth::User()->account_id)->where('name','User ID')->get();
        $password=LeopardsSettings::where('account_id', Auth::User()->account_id)->where('name','Password')->get();

        $packet_piece=$request->input('packet_pieces');
        $weight=$request->input('net_weight');
        $cod_amount=$request->input('collect_amount');
        $order_id=$request->input('order_id');
        $service_type=$request->input('shipment_type_id');

        $shiper_name=$request->input('shipper_name');
        $shiper_add=$request->input('shipper_address');
        $shiper_phone=$request->input('shipper_phone');
        $shiper_email=$request->input('shipper_email');

        $orign_city=$request->input('origin_city');
        $des_city=$request->input('destination_city');

        $con_name=$request->input('consignee_name');
        $con_add=$request->input('consignee_address');
        $con_email=$request->input('consignee_email');
        $con_phone=$request->input('consignee_phone');
        $con_remarks=$request->input('comments');
        $product_description=$request->input('product_description');

        $special_handling=$request->input('SpecialHandling');
        $insurance_value=$request->input('InsuranceValue');
        echo $username[0]->data.'<br>';
        echo $password[0]->data.'<br>';

        if (!$special_handling)
            $special_handling=0;

        $f=Config::get('constants.wcc_shipment_type');
        if ($service_type==2)
            $service_type='COD';
        echo $weight."<br>".$packet_piece."<br>".$cod_amount."<br>".$order_id."<br>".$service_type."<br>".$shiper_name."<br>".$shiper_add."<br>".$shiper_phone."<br>".$shiper_email."<br>".$orign_city."<br>".$des_city."<br>".$con_name."<br>".$con_add."<br>".$con_email."<br>".$con_phone."<br>".$con_remarks."<br>".$product_description."<br>".$special_handling."<br>".$insurance_value;

        return true;
    }
    /**
     * Store Fulfillment.
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function savefulfillment(Request $request, $id)
    {
        if (! Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        $validator = $this->verifyFulfillment($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $booked_packet = BookedPackets::where([
            'track_number' => $request->get('track_number')
        ])->first();

        if(!$booked_packet) {
            return response()->json(array(
                'status' => 0,
                'message' => ['Invalid packet provided'],
            ));
        }

        /**
         * Fulfill this order
         */
        $shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();

        $fulfillment = array();

        if($shop) {
            try {
                $shopifyClient = new ShopifyClient([
                    'private_app' => false,
                    'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                    'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                    'access_token' => $shop->access_token,
                    'shop' => $shop->myshopify_domain
                ]);

                $fulfillment = $shopifyClient->createFulfillment(array(
                    'order_id' => (int) $request->get('order_id'),
                    'location_id' => $request->get('location_id'),
                    'tracking_number' => $request->get('track_number'),
                    'tracking_company' => 'Wcc',
                    'notify_customer' => ($request->get('notify_customer') == '1') ? true : false,
                    'tracking_urls' => array(
                        $request->get('tracking_url')
                    ),
                ));

                flash('Order is fulfilled successfully.')->success()->important();

                return response()->json(array(
                    'status' => 1,
                    'message' => 'Order is fulfilled successfully.',
                ));
            } catch (\Exception $exception) {
                $error = ['Error in fulfilment, Please select proper location where this inventory exists'];
                $message = $exception->getMessage();
                $message = json_decode(substr($message, strpos($message, '{')), true);
                if(is_array($message)) {
                    if(isset($message['errors']) && isset($message['errors']['base'])) {
                        $error = $message['errors']['base'];
                    }
                }

                flash('Order is fulfilled successfully.')->success()->important();

                return response()->json(array(
                    'status' => 0,
                    'message' => $error,
                ));

            }
        }
    }

    /**
     * Validate fulfillment fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyFulfillment(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'order_number' => 'required|numeric',
            'location_id' => 'required',
            'track_number' => 'required',
            'tracking_url' => 'required',
        ]);
    }


    /**
     * Track booked Packet.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function track($id)
    {
        if (!Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        $booked_packet = BookedPackets::getData($id);

        if(!$booked_packet) {
            return view('error');
        }

        $track_history = [];

        /**
         * Load Wcc Settings
         */
        $wcc_settings = LeopardsSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');


        try {
            $wcc = new LeopardsCODClient();

            $response = $wcc->trackPacket(array(
                'api_key' => $wcc_settings['api-key']->data,                                           // API Key provided by LCS
                'api_password' => $wcc_settings['api-password']->data,                                 // API Password provided by LCS
                'enable_test_mode' => ($booked_packet->booking_type == '1') ? true : false,                 // [Optional] default value is 'false', true|false to set mode test or live
                'track_numbers' => $booked_packet->track_number
            ));

            if($response['status']) {
                if(isset($response['packet_list']) && count($response['packet_list'])) {

                    $packet = $response['packet_list'][0];
                    if(count($packet['Tracking Detail'])) {
                        $track_history = array_reverse($packet['Tracking Detail']);
                    } else {
                        $track_history = [];
                    }

                    /**
                     * Update Packet Status
                     */
                    $status = Config::get('constants.status');
                    $status_delivered = Config::get('constants.status_delivered');
                    $status_id = 0;

                    foreach ($status as $key => $value) {
                        if(strtolower($packet['booked_packet_status']) == strtolower($value)) {
                            $status_id = $key;
                        }
                    }

                    if(
                            array_key_exists('invoice_number', $packet)
                        &&  array_key_exists('invoice_date', $packet)
                    ) {
                        BookedPackets::where([
                            'track_number' => $packet['track_number']
                        ])->update(array(
                            'status' => $status_id,
                            'invoice_number' => $packet['invoice_number'],
                            'invoice_date' => $packet['invoice_date']
                        ));

                        if(
                                $packet['invoice_date']
                            &&  $packet['invoice_number']
                            &&  ($status_delivered == $status_id)
                        ) {

                            /**
                             * Put Change Paid Status into Jobs
                             */
                            $payload = array(
                                'invoice_date' => $packet['invoice_date'],
                                'invoice_number' => $packet['invoice_number'],
                                'track_number' => $packet['track_number'],
                                'booked_packet_status' => $packet['booked_packet_status'],
                            );

                            ShopifyJobs::insert(array(
                                'payload' => json_encode($payload),
                                'type' => 'mark-order-status',
                                'created_at' => Carbon::now()->toDateTimeString(),
                                'available_at' => Carbon::now()->toDateTimeString(),
                                'account_id' => Auth::User()->account_id,
                            ));
                        }

                    } else {
                        BookedPackets::where([
                            'track_number' => $packet['track_number']
                        ])->update(array(
                            'status' => $status_id
                        ));
                    }
                }
            }

        } catch (\Exception $exception) {

        }

        $shipment_type = Config::get('constants.shipment_type');

        $wcc_cities = WccCities::where([
            'account_id' => Auth::User()->account_id,
        ])->whereIn('city_id', [$booked_packet->origin_city, $booked_packet->destination_city])
            ->select('city_id', 'name')
            ->get();
        if($wcc_cities) {
            $wcc_cities = $wcc_cities->keyBy('city_id');
        } else {
            $wcc_cities = [];
        }

        return view('admin.booked_packets.track', compact('booked_packet', 'shipment_type', 'wcc_cities', 'track_history'));
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

        return redirect()->back();
    }

    /**
     * Cancel Record from LCS.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        if (! Gate::allows('booked_packets_create')) {
            return abort(401);
        }

        BookedPackets::cancelPacket($id);

        return redirect()->route('admin.booked_packets.index');
    }

    /**
     * Dispatch event for sync products.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncStatus()
    {
        if (! Gate::allows('booked_packets_manage')) {
            return abort(401);
        }

        event(new FullSyncPacketStatusFire(Accounts::find(Auth::User()->account_id)));

        flash('Booked Packet Statuses Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.booked_packets.index');
    }








}
