<?php

namespace App\Http\Controllers\Admin;

use App\Events\Leopards\SyncLeopardsCitiesFire;
use App\Helpers\Leopards;
use App\Models\Accounts;
use App\Models\LeopardsSettings;
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

        $leopards_settings = LeopardsSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.leopards_settings.company', compact('leopards_settings'));
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

        $leopards_settings = LeopardsSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.leopards_settings.create',compact('leopards_settings'));
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
            $data['company-id'] = $response['company_id'];
            $request = new Request();
            $request->replace($data);
        }

        if(LeopardsSettings::createRecord($request, Auth::User()->account_id)) {
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
            'api-key' => 'required',
            'api-password' => 'required',
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
            'status' => false,
            'company_id' => false,
        );

        /**
         * Grab Cities List
         */
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

        /**
         * Grab Company ID
         */
        $leopards = new Leopards(array(
            'username' => $request->get('username'),
            'password' => $request->get('password'),
            'api_key' => $request->get('api-key'),
            'api_password' => $request->get('api-password'),
        ));

        $companyDetail = $leopards->getCompanyCode();

        if($companyDetail['status']) {
            $data['company_id'] = $companyDetail['companyId'];
            $data['status'] = true;

            /**
             * Dispatch Sync Leopards Cities Event and Delte existing records
             */
            event(new SyncLeopardsCitiesFire(Accounts::find($account_id)));
        }

        return $data;
    }
}
