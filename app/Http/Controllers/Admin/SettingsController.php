<?php

namespace App\Http\Controllers\Admin;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Config;
use Validator;

class SettingsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('settings_manage')) {
            return abort(401);
        }

        return view('admin.settings.index', compact('settings'));
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
            $Settings = Settings::getBulkData($request->get('id'));
            if($Settings) {
                foreach($Settings as $Setting) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!Settings::isChildExists($Setting->id, Auth::User()->account_id)) {
                        $Setting->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Settings::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $Settings = Settings::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($Settings) {
            foreach($Settings as $setting) {
                $data = $setting->data;
                if ($setting->slug == 'sys-discounts') {
                    $exploded = explode(':', $setting->data);
                    $data = 'Min: ' . $exploded[0] . '%, Max: ' . $exploded[1] . '%';
                }
                if ($setting->slug == 'sys-birthdaypromotion') {
                    $exploded = explode(':', $setting->data);
                    $data = 'Pre Days: ' . $exploded[0] . ', Post Days: ' . $exploded[1];
                }
                $records["data"][] = array(
                    'name' => $setting->name,
                    'data' => $data,
                    'actions' => view('admin.settings.actions', compact('setting'))->render(),
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
        if (! Gate::allows('settings_manage')) {
            return abort(401);
        }

        $setting = new \stdClass();
        $setting->id = null;

        return view('admin.settings.create', compact('setting'));
    }


    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('settings_manage')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(Settings::createRecord($request, Auth::User()->account_id)) {
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
            'data' => 'required',
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
        if (! Gate::allows('settings_edit')) {
            return abort(401);
        }

        $setting = Settings::getData($id);

        if(!$setting) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.settings.edit', compact('setting'));
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
        if (! Gate::allows('settings_edit')) {
            return abort(401);
        }
        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }
        $setting = (Settings::find($id))->toArray();
        if (
            $setting['slug'] == 'sys-discounts'
            && $request->min > $request->max
        ) {
            return response()->json(array(
                'status' => 0,
                'message' => array('Min value is greater than Max value.'),
            ));
        }
        if(Settings::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('settings_manage')) {
            return abort(401);
        }

        $setting = Settings::getData($id);

        if(!$setting) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.settings.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if(Settings::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.settings.index');
        }

        $setting->delete();

        flash('Record has been deleted successfully.')->success()->important();

        return redirect()->route('admin.settings.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('settings_manage')) {
            return abort(401);
        }
        $setting = Settings::getData($id);

        if(!$setting) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.settings.index');
        }

        $setting->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.settings.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('settings_manage')) {
            return abort(401);
        }
        $setting = Settings::getData($id);

        if(!$setting) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.settings.index');
        }

        $setting->update(['active' => 1]);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.settings.index');
    }

}
