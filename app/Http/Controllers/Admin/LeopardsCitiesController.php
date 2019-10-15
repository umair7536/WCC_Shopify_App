<?php

namespace App\Http\Controllers\Admin;

use App\Events\Leopards\SyncLeopardsCitiesFire;
use App\Models\Accounts;
use App\Models\LeopardsCities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Auth;
use Validator;

class LeopardsCitiesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('leopards_cities_manage')) {
            return abort(401);
        }

        return view('admin.leopards_cities.index');
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
            $LeopardsCities = LeopardsCities::getBulkData($request->get('id'));
            if($LeopardsCities) {
                foreach($LeopardsCities as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!LeopardsCities::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = LeopardsCities::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $LeopardsCities = LeopardsCities::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($LeopardsCities) {
            foreach($LeopardsCities as $leopards_citie) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$leopards_citie->id.'"/><span></span></label>',
                    'name' => $leopards_citie->name,
                    'actions' => view('admin.leopards_cities.actions', compact('leopards_citie'))->render(),
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
        if (! Gate::allows('leopards_cities_create')) {
            return abort(401);
        }

        return view('admin.leopards_cities.create',compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('leopards_cities_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(LeopardsCities::createRecord($request, Auth::User()->account_id, Auth::User()->id)) {
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
        if (! Gate::allows('leopards_cities_edit')) {
            return abort(401);
        }

        $leopards_citie = LeopardsCities::getData($id);

        if(!$leopards_citie) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.leopards_cities.edit', compact('leopards_citie'));
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
        if (! Gate::allows('leopards_cities_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(LeopardsCities::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('leopards_cities_destroy')) {
            return abort(401);
        }

        LeopardsCities::deleteRecord($id);

        return redirect()->route('admin.leopards_cities.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('leopards_cities_inactive')) {
            return abort(401);
        }
        LeopardsCities::inactiveRecord($id);

        return redirect()->route('admin.leopards_cities.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('leopards_cities_active')) {
            return abort(401);
        }
        LeopardsCities::activeRecord($id);

        return redirect()->route('admin.leopards_cities.index');
    }

    /**
     * Dispatch event for sync custom collections.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncLeopardsCities()
    {
        if (! Gate::allows('leopards_cities_manage')) {
            return abort(401);
        }

        /**
         * Dispatch Sync Leopards Cities Event and Delte existing records
         */
        event(new SyncLeopardsCitiesFire(Accounts::find(Auth::User()->account_id)));


        flash('Leopards Cities Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.leopards_cities.index');
    }
}
