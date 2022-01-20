<?php

namespace App\Http\Controllers\Admin;

use App\Models\LeopardsCities;
use App\Models\WccCities;
use App\Models\Shippers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;

class ShippersController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('shippers_manage')) {
            return abort(401);
        }

        $wcc_cities = WccCities::where([
            'account_id' => WccCities::orderBy('id', 'desc')->first()->account_id,
        ])->get()->pluck('name', 'id');

        return view('admin.shippers.index', compact('wcc_cities'));
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
            $Shippers = Shippers::getBulkData($request->get('id'));
            if ($Shippers) {
                foreach ($Shippers as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Shippers::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Shippers::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $Shippers = Shippers::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if ($Shippers) {

            $wcc_cities = WccCities::where([
                'account_id' => WccCities::orderBy('id', 'desc')->first()->account_id,
            ])->get()->keyBy('id');

            foreach ($Shippers as $shipper) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $shipper->id . '"/><span></span></label>',
                    'city_id' => isset($wcc_cities[$shipper->city_id]) ? $wcc_cities[$shipper->city_id]->name : 'N/A',
                    'name' => $shipper->name,
                    'email' => $shipper->email,
                    'phone' => $shipper->phone,
                    'format' => $shipper->format,
                    'actions' => view('admin.shippers.actions', compact('shipper'))->render(),
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
        if (!Gate::allows('shippers_create')) {
            return abort(401);
        }

        $wcc_cities = WccCities::where([
            'account_id' => WccCities::orderBy('id', 'desc')->first()->account_id,
        ])->get()->pluck('name', 'id');

        return view('admin.shippers.create', compact('wcc_cities'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('shippers_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (Shippers::createRecord($request, Auth::User()->account_id)) {
            flash('Record has been created successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => 'Record has been created successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => ['Something went wrong, please try again later.'],
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
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
        ]);
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('shippers_edit')) {
            return abort(401);
        }

        $shipper = Shippers::getData($id);

        if (!$shipper) {
            return view('error', compact('lead_statuse'));
        }

        $wcc_cities = WccCities::where([
            'account_id' => WccCities::orderBy('id', 'desc')->first()->account_id,
        ])->get()->pluck('name', 'id');

        return view('admin.shippers.edit', compact('shipper', 'wcc_cities'));
    }

    /**
     * Update Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('shippers_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (Shippers::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('shippers_destroy')) {
            return abort(401);
        }

        Shippers::deleteRecord($id);

        return redirect()->route('admin.shippers.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('shippers_inactive')) {
            return abort(401);
        }
        Shippers::inactiveRecord($id);

        return redirect()->route('admin.shippers.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('shippers_active')) {
            return abort(401);
        }
        Shippers::activeRecord($id);

        return redirect()->route('admin.shippers.index');
    }
}
