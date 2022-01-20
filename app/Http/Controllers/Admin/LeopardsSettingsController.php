<?php

namespace App\Http\Controllers\Admin;

use App\Events\Leopards\SyncLeopardsCitiesFire;
use App\Helpers\Leopards;
use App\Models\Accounts;
use App\Models\LeopardsCities;
use App\Models\WccSettings;
use App\Models\WccCities;
use App\Models\LeopardsSettings;
use App\Models\ShopifyLocations;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Config;
use Validator;

class LeopardsSettingsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('leopards_settings_manage')) {
            return abort(401);
        }




        $shopify_locations = ShopifyLocations::where([
            'account_id' => Auth::User()->account_id
        ])
            ->select('location_id', 'name')
            ->get()->keyBy('location_id');

        if($shopify_locations) {
            $shopify_locations = $shopify_locations->toArray();
        } else {
            $shopify_locations = [];
        }



        $leopards_settings = WccSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');


        // Orignal Code it get shiper city based on account_id present in WccCities DB

/*
        if($leopards_settings['shipper-type']->data == 'other') {
            $wcc_cities = WccCities::where([
                'account_id' => Auth::User()->account_id,
            ])->whereIn('city_id', [$leopards_settings['shipper-city']->data])
                ->select('city_id', 'name')
                ->get();
*/


        // this code copy of above but in which i have set account_id 3 because in wcccities DB only account_id 3 present
        if($leopards_settings['shipper-type']->data == 'other') {
            $wcc_cities = WccCities::where([
                'account_id' => Auth::User()->account_id,    // Bydeafult set 3
            ])->whereIn('city_id', [$leopards_settings['shipper-city']->data])
                ->select('city_id', 'name')
                ->get();

            if($wcc_cities) {
                $wcc_cities = $wcc_cities->keyBy('city_id')->toArray();
            } else {
                $wcc_cities = [];
            }
        } else {
            $wcc_cities = [];
        }

        return view('admin.wcc_settings.company', compact('leopards_settings', 'shopify_locations', 'wcc_cities'));
    }

    /**
     * Show the form for creating new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('leopards_settings_edit')) {
            return abort(401);
        }

        $account_id = Auth::User()->account_id;

        $shopify_locations = ShopifyLocations::where([
            'account_id' => $account_id
        ])->get();



        if($shopify_locations) {
            $shopify_locations = $shopify_locations->pluck('name', 'location_id');
        } else {
            $shopify_locations = [];
        }


        $leopards_settings = WccSettings::where([
            'account_id' => $account_id
        ])
            ->orderBy('id', 'asc')
            ->get();


        $inventory_location = WccSettings::getDefaultInventoryLocation($account_id);

        $fulfillment_status = WccSettings::isAutoFulfillmentEnabled($account_id);
        ($fulfillment_status) ? $fulfillment_status = '1' : '0';
        $mark_status = WccSettings::isAutoMarkPaidEnabled($account_id);
        ($mark_status) ? $mark_status = '1' : '0';


        /**
         * Manage Shipment Type
         */
//        echo Auth::User()->account_id;
//        exit();
        $shipment_type = Config::get('constants.shipment_type');
        $wcc_cities = WccCities::where([
            'account_id' => WccCities::orderBy('id', 'desc')->first()->account_id,
            'account_id' => Auth::User()->account_id,
        ])
            ->orderBy('name', 'asc')
            ->get();
        if($wcc_cities) {
            $wcc_cities = $wcc_cities->pluck('name', 'city_id');
        } else {
            $wcc_cities = [];
        }

        return view('admin.wcc_settings.create',compact('leopards_settings', 'shopify_locations', 'inventory_location', 'fulfillment_status', 'mark_status', 'wcc_cities'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('leopards_settings_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);
//        echo $validator;
//        exit();
        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        /**
         * Verify Account
         */


        $response = $this->verifyAccount($request, Auth::User()->account_id);

        if (!$response['status']) {
            return response()->json(array(
                'status' => 0,
                'message' => ( isset($response['message']) && $response['message']) ? [$response['message']] : ['User ID and Password are incorrect, Please enter correct credentials.'],
            ));
        } else {
            $data = $request->all();
            $request = new Request();
            $request->replace($data);
        }
        $data = $request->all();
        $request = new Request();
        $request->replace($data);
        if(WccSettings::createRecord($request, Auth::User()->account_id)) {
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
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'mode' => 'required',
            'username' => 'required',
            'password' => 'required',
//            'api-key' => 'required',
//            'api-password' => 'required',
            'auto-fulfillment' => 'required',
            'inventory-location' => 'required',
            'shipper-type' => 'required',
        ]);
    }

    /**
     * Handle the event.
     *
     * @param  Request  $event
     * @return array
     */
    private function verifyAccount(Request $request, $account_id)
    {
        $data = array(
            'status' => true,
            'company_id' => 0,
        );

        /**
         * Grab Cities List
         */

        try {
            $username=$request->get('username');
            $password=$request->get('password');
            $req=file_get_contents('http://web.api.wcc.com.pk:3001/api/General/GetCityList?username='.$username.'&password='.$password);
            $j_data=json_decode($req);
            if($j_data=='1' || $j_data==true || $j_data==1)
            {
                return $data;
            }
        }
        catch (\Exception $e){
            $data['status'] = false;
            $data['message'] = 'Your Username or Password incorrect.';

            return $data;

        }


        // Leopard Verification

/*
        $leopard_client = new LeopardsCODClient();
        $cities = $leopard_client->getAllCities(array(
            'api_key' => $request->get('api-key'),
            'api_password' => $request->get('api-password'),
            'enable_test_mode' => false
        ));
        if(!$cities['status']) {
            $data['status'] = false;
            $data['message'] = 'Your API Key and API Passwords are incorrect.';

            return $data;
        }


        // * Dispatch Sync Leopards Cities Event and Delte existing records

        event(new SyncLeopardsCitiesFire(Accounts::find($account_id)));

        return $data;
 */


    }
}
