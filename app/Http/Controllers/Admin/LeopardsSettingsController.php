<?php

namespace App\Http\Controllers\Admin;

use App\Models\LeopardsSettings;
use App\Models\ShopifyCustomCollections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
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

        return view('admin.leopards_settings.index');
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
            $LeopardsSettings = LeopardsSettings::getBulkData($request->get('id'));
            if($LeopardsSettings) {
                foreach($LeopardsSettings as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!LeopardsSettings::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = LeopardsSettings::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $LeopardsSettings = LeopardsSettings::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        $custom_collections = ShopifyCustomCollections::where([
            'account_id' => Auth::User()->account_id
        ])->select('title', 'collection_id')->get()->keyBy('collection_id');

        if($LeopardsSettings) {
            foreach($LeopardsSettings as $leopards_setting) {
                if($leopards_setting->slug == 'bookin' || $leopards_setting->slug == 'repair') {
                    if($leopards_setting->data) {
                        $names_array = [];
                        $names = explode(', ', $leopards_setting->data);
                        foreach($names as $single_name) {
                            if(isset($custom_collections[$single_name])) {
                                $names_array[] = $custom_collections[$single_name]->title;
                            }
                        }
                        $names_array = implode(', ', $names_array);
                    } else {
                        $names_array = $leopards_setting->data;
                    }
                } else {
                    $names_array = $leopards_setting->data;
                }

                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$leopards_setting->id.'"/><span></span></label>',
                    'name' => $leopards_setting->name,
                    'data' => ($leopards_setting->slug == 'mode') ? ($leopards_setting->data ? 'Test Mode' : 'Production') : $leopards_setting->data,
                    'actions' => view('admin.leopards_settings.actions', compact('leopards_setting'))->render(),
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
        if (! Gate::allows('leopards_settings_create')) {
            return abort(401);
        }

        $show_colors = 0;

        return view('admin.leopards_settings.create',compact('show_colors'));
    }


    public function sortorder_save(){

        $city = DB::table('leopards_settings')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_number', 'ASC')->get();
        $itemID=Input::get('itemID');
        $itemIndex=Input::get('itemIndex');
        if($itemID){
            foreach ($city as $cit) {
                $sort=DB::table('leopards_settings')->where('id', '=', $itemID)->update(array('sort_number' => $itemIndex));
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

        $city = DB::table('leopards_settings')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderby('sort_number', 'ASC')->get();
        return view('admin.leopards_settings.sort', compact('city'));
    }
    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('leopards_settings_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(LeopardsSettings::createRecord($request, Auth::User()->account_id)) {
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
        if (! Gate::allows('leopards_settings_edit')) {
            return abort(401);
        }

        $leopards_setting = LeopardsSettings::getData($id);

        if(!$leopards_setting) {
            return view('error', compact('lead_statuse'));
        }

        if($leopards_setting->slug == 'bookin' || $leopards_setting->slug == 'repair') {
            $leopards_setting->data = explode(', ', $leopards_setting->data);
            $custom_collections = ShopifyCustomCollections::where([
                'account_id' => Auth::User()->account_id
            ])->select('title', 'collection_id')->get()->pluck('title', 'collection_id');
        } else {
            $custom_collections = [];
        }

        return view('admin.leopards_settings.edit', compact('leopards_setting', 'custom_collections'));
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

        if(LeopardsSettings::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('leopards_settings_destroy')) {
            return abort(401);
        }

        LeopardsSettings::deleteRecord($id);

        return redirect()->route('admin.leopards_settings.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('leopards_settings_inactive')) {
            return abort(401);
        }
        LeopardsSettings::inactiveRecord($id);

        return redirect()->route('admin.leopards_settings.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('leopards_settings_active')) {
            return abort(401);
        }
        LeopardsSettings::activeRecord($id);

        return redirect()->route('admin.leopards_settings.index');
    }
}
