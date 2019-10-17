<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookedPackets;
use App\Models\LeopardsCities;
use App\Models\LeopardsSettings;
use App\Models\Shippers;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Auth;
use Config;
use Validator;

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

            $leopards_cities = LeopardsCities::where([
                'account_id' => Auth::User()->account_id,
            ])->whereIn('city_id', $cities)
                ->select('city_id', 'name')
                ->get();
            if($leopards_cities) {
                $leopards_cities = $leopards_cities->keyBy('city_id');
            } else {
                $leopards_cities = [];
            }

            foreach($BookedPackets as $booked_packet) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$booked_packet->id.'"/><span></span></label>',
                    'status' => $status[$booked_packet->status],
                    'order_id' => $booked_packet->order_id,
                    'shipment_type_id' => $shipment_type[$booked_packet->shipment_type_id],
                    'cn_number' => $booked_packet->cn_number,
                    'origin_city' => isset($booked_packet->origin_city, $leopards_cities) ? $leopards_cities[$booked_packet->origin_city]->name : 'n/a',
                    'destination_city' => isset($booked_packet->destination_city, $leopards_cities) ? $leopards_cities[$booked_packet->destination_city]->name : 'n/a',
                    'shipper_name' => $booked_packet->shipper_name,
                    'consignee_name' => $booked_packet->consignee_name,
                    'consignee_phone' => $booked_packet->consignee_phone,
                    'consignee_email' => ($booked_packet->consignee_email) ? $booked_packet->consignee_email : 'n/a',
                    'booking_date' => $booked_packet->booking_date,
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
        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id,
        ])->get();

        if($leopards_cities) {
            $leopards_cities = $leopards_cities->pluck('name', 'city_id');
        } else {
            $leopards_cities = [];
        }

        return view('admin.booked_packets.api', compact('status', 'shipment_type', 'leopards_cities'));
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

            $leopards_cities = LeopardsCities::where([
                'account_id' => Auth::User()->account_id,
            ])->whereIn('city_id', $cities)
                ->select('city_id', 'name')
                ->get();
            if($leopards_cities) {
                $leopards_cities = $leopards_cities->keyBy('city_id');
            } else {
                $leopards_cities = [];
            }

            foreach($BookedPackets as $booked_packet) {
                $records["data"][] = array(
                    'status' => $status[$booked_packet->status],
                    'order_id' => $booked_packet->order_id,
                    'shipment_type_id' => $shipment_type[$booked_packet->shipment_type_id],
                    'cn_number' => $booked_packet->cn_number,
                    'origin_city' => isset($booked_packet->origin_city, $leopards_cities) ? $leopards_cities[$booked_packet->origin_city]->name : 'n/a',
                    'destination_city' => isset($booked_packet->destination_city, $leopards_cities) ? $leopards_cities[$booked_packet->destination_city]->name : 'n/a',
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
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! Gate::allows('booked_packets_create')) {
            return abort(401);
        }

        /**
         * Grab Company Information from LCS System
         */
        $data = $this->getCompanyData(Auth::User()->account_id);
        if(!$data['status']) {
            if (Gate::allows('leopards_settings_manage')) {
                flash('Leopards Credentials are invalid, Please provide correct credentials.')->error()->important();
                return redirect()->route('admin.leopards_settings.index');
            } else {
                flash('Leopards Credentials are invalid, Please provide correct credentials.')->error()->important();
                return redirect()->route('admin.booked_packets.index');
            }
        }

        /**
         * If Order ID is provided then prepare data to automatically be filled
         */
        $data['booked_packet'] = BookedPackets::prepareBookingOrder($request->get('order_id'), Auth::User()->account_id);

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

        return view('admin.booked_packets.create',compact('booking_date', 'default_shipment_type', 'shipment_type', 'volumetric_dimensions_calculated', 'leopards_cities', 'shippers', 'consignees', 'shipper_id', 'consignee_id', 'data'));
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

        $result = BookedPackets::createRecord($request, Auth::User()->account_id);

        if($result['status']) {
            if($result['test_mode']) {
                flash('Test Packet is booked successfully.')->success()->important();
            } else {
                flash('Packet is booked successfully.')->success()->important();
            }

            return response()->json(array(
                'status' => 1,
                'test_mode' => $result['test_mode'],
                'message' => 'Record has been created successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => $result['error_msg'],
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
            'order_id' => 'nullable|numeric',
            'vol_weight_w' => 'nullable|numeric',
            'vol_weight_h' => 'nullable|numeric',
            'vol_weight_l' => 'nullable|numeric',
            'shipper_id' => 'required',
            'origin_city' => 'required|numeric',
            'shipper_name' => 'required',
            'shipper_email' => 'nullable|email',
            'shipper_phone' => 'required',
            'shipper_address' => 'required',
            'consignee_id' => 'required',
            'destination_city' => 'required|numeric',
            'consignee_name' => 'required',
            'consignee_email' => 'required|email',
            'consignee_phone' => 'required',
            'consignee_address' => 'required',
            'comments' => 'required',
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

        $shipment_type = Config::get('constants.shipment_type');

        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id,
        ])->whereIn('city_id', [$booked_packet->origin_city, $booked_packet->destination_city])
            ->select('city_id', 'name')
            ->get();
        if($leopards_cities) {
            $leopards_cities = $leopards_cities->keyBy('city_id');
        } else {
            $leopards_cities = [];
        }

        return view('admin.booked_packets.detail', compact('booked_packet', 'shipment_type', 'leopards_cities'));
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
}
