<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeneralSettings;
use App\Models\ShopifyCustomCollections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;

class GeneralSettingsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('general_settings_manage')) {
            return abort(401);
        }

        return view('admin.general_settings.index');
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
            $GeneralSettings = GeneralSettings::getBulkData($request->get('id'));
            if($GeneralSettings) {
                foreach($GeneralSettings as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!GeneralSettings::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = GeneralSettings::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $GeneralSettings = GeneralSettings::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        $custom_collections = ShopifyCustomCollections::where([
            'account_id' => Auth::User()->account_id
        ])->select('title', 'collection_id')->get()->keyBy('collection_id');

        if($GeneralSettings) {
            foreach($GeneralSettings as $general_setting) {
                if($general_setting->slug == 'bookin' || $general_setting->slug == 'repair') {
                    if($general_setting->data) {
                        $names_array = [];
                        $names = explode(', ', $general_setting->data);
                        foreach($names as $single_name) {
                            if(isset($custom_collections[$single_name])) {
                                $names_array[] = $custom_collections[$single_name]->title;
                            }
                        }
                        $names_array = implode(', ', $names_array);
                    } else {
                        $names_array = $general_setting->data;
                    }
                } else {
                    $names_array = $general_setting->data;
                }

                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$general_setting->id.'"/><span></span></label>',
                    'name' => $general_setting->name,
                    'data' => $names_array,
                    'actions' => view('admin.general_settings.actions', compact('general_setting'))->render(),
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
        if (! Gate::allows('general_settings_create')) {
            return abort(401);
        }

        $show_colors = 0;

        return view('admin.general_settings.create',compact('show_colors'));
    }


    public function sortorder_save(){

        $city = DB::table('general_settings')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_number', 'ASC')->get();
        $itemID=Input::get('itemID');
        $itemIndex=Input::get('itemIndex');
        if($itemID){
            foreach ($city as $cit) {
                $sort=DB::table('general_settings')->where('id', '=', $itemID)->update(array('sort_number' => $itemIndex));
                $myarray=['status'=>"Data Sort Successfully"];
                return response()->json($myarray);
            }
        }
        else{
            $myarray=['status'=>"Data Not Sort"];
            return response()->json($myarray);
        }
    }

    public function sortorder(){

        $city = DB::table('general_settings')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderby('sort_number', 'ASC')->get();
        return view('admin.general_settings.sort', compact('city'));
    }
    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('general_settings_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(GeneralSettings::createRecord($request, Auth::User()->account_id)) {
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
            'name' => 'required',
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
        if (! Gate::allows('general_settings_edit')) {
            return abort(401);
        }

        $general_setting = GeneralSettings::getData($id);

        if(!$general_setting) {
            return view('error', compact('lead_statuse'));
        }

        if($general_setting->slug == 'bookin' || $general_setting->slug == 'repair') {
            $general_setting->data = explode(', ', $general_setting->data);
            $custom_collections = ShopifyCustomCollections::where([
                'account_id' => Auth::User()->account_id
            ])->select('title', 'collection_id')->get()->pluck('title', 'collection_id');
        } else {
            $custom_collections = [];
        }

        return view('admin.general_settings.edit', compact('general_setting', 'custom_collections'));
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
        if (! Gate::allows('general_settings_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(GeneralSettings::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('general_settings_destroy')) {
            return abort(401);
        }

        GeneralSettings::deleteRecord($id);

        return redirect()->route('admin.general_settings.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('general_settings_inactive')) {
            return abort(401);
        }
        GeneralSettings::inactiveRecord($id);

        return redirect()->route('admin.general_settings.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('general_settings_active')) {
            return abort(401);
        }
        GeneralSettings::activeRecord($id);

        return redirect()->route('admin.general_settings.index');
    }
}
