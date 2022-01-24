<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\Leopards\SyncLeopardsCitiesFire;
use App\Helpers\Leopards;
use App\Models\Accounts;
use App\Models\WccCities;
use App\Models\WccSettings;
use App\Models\LeopardsSettings;
use App\Models\ShopifyLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use DB;
use Auth;
use Config;
use Validator;


class WccSettingsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('Wcc_settings_manage')) {
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



        $wcc_settings = WccSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');


        // Orignal Code it get shiper city based on account_id present in WccCities DB



        // this code copy of above but in which i have set account_id 3 because in wcccities DB only account_id 3 present
        if($wcc_settings['shipper-type']->data == 'other') {
            $wcc_cities = WccCities::where([
                'account_id' => WccCities::orderBy('id', 'desc')->first()->account_id,    // Bydeafult set 3
            ])->whereIn('city_id', [$wcc_settings['shipper-city']->data])
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

        return view('admin.wcc_settings.company', compact('wcc_settings', 'shopify_locations', 'wcc_cities'));
    }

    /**
     * Show the form for creating new Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('Wcc_settings_edit')) {
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


        $wcc_settings = WccSettings::where([
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

        $shipment_type = Config::get('constants.wcc_shipment_type');
        $wcc_cities = WccCities::where([
            'account_id' => WccCities::orderBy('id', 'desc')->first()->account_id,
            'city_id'=>'KHI',
        ])
            ->orderBy('name', 'asc')
            ->get();
        echo $wcc_cities;
        if($wcc_cities) {
            $wcc_cities = $wcc_cities->pluck('name', 'city_id');
        } else {
            $wcc_cities = [];
        }

        return view('admin.wcc_settings.create',compact('wcc_settings', 'shopify_locations', 'inventory_location', 'fulfillment_status', 'mark_status', 'wcc_cities'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('Wcc_settings_edit')) {
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
        $data["mode"]="0";
        $data["auto-mark-paid"]="0";
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
            // 'mode' => 'required',
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
         * Account verification of Wcc User
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


    }

    public function test_cities()
    {
            $username=env('WCC_USERNAME');
            $password=env('WCC_PASSWORD');

            $req=file_get_contents('http://web.api.wcc.com.pk:3001/api/General/GetCityList?username='.$username.'&password='.$password);
            $j_data=json_decode($req);
            if($j_data=='1' || $j_data==true || $j_data==1)
            {

                $wcc_cities = [];
                $sort_number = 0;
                // var_dump($j_data);
                foreach($j_data->Data as $data)
                {  
                    $wcc_cities[] = array(
                        'name' => $data->CityName,
                        'city_id' => $data->CityCode,
                        'account_id' => 2,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    );

                }


                var_dump($wcc_cities);
                
                exit();
            }

            echo "No City Present";
            exit();

    }



}
