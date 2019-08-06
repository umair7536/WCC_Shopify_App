<?php

namespace App\Http\Controllers\Admin;

use App\Models\LeopardsCities;
use App\Models\Consignees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;

class ConsigneesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('consignees_manage')) {
            return abort(401);
        }

        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id
        ])->get()->pluck('name', 'id');

        return view('admin.consignees.index', compact('leopards_cities'));
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
            $Consignees = Consignees::getBulkData($request->get('id'));
            if ($Consignees) {
                foreach ($Consignees as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Consignees::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Consignees::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $Consignees = Consignees::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if ($Consignees) {

            $leopards_cities = LeopardsCities::where([
                'account_id' => Auth::User()->account_id
            ])->get()->keyBy('id');

            foreach ($Consignees as $consignee) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $consignee->id . '"/><span></span></label>',
                    'city_id' => isset($leopards_cities[$consignee->city_id]) ? $leopards_cities[$consignee->city_id]->name : 'N/A',
                    'name' => $consignee->name,
                    'email' => $consignee->email,
                    'phone' => $consignee->phone,
                    'format' => $consignee->format,
                    'actions' => view('admin.consignees.actions', compact('consignee'))->render(),
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
        if (!Gate::allows('consignees_create')) {
            return abort(401);
        }

        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id
        ])->get()->pluck('name', 'id');

        return view('admin.consignees.create', compact('leopards_cities'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('consignees_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (Consignees::createRecord($request, Auth::User()->account_id)) {
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
        if (!Gate::allows('consignees_edit')) {
            return abort(401);
        }

        $consignee = Consignees::getData($id);

        if (!$consignee) {
            return view('error', compact('lead_statuse'));
        }

        $leopards_cities = LeopardsCities::where([
            'account_id' => Auth::User()->account_id
        ])->get()->pluck('name', 'id');

        return view('admin.consignees.edit', compact('consignee', 'leopards_cities'));
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
        if (!Gate::allows('consignees_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (Consignees::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (!Gate::allows('consignees_destroy')) {
            return abort(401);
        }

        Consignees::deleteRecord($id);

        return redirect()->route('admin.consignees.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('consignees_inactive')) {
            return abort(401);
        }
        Consignees::inactiveRecord($id);

        return redirect()->route('admin.consignees.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('consignees_active')) {
            return abort(401);
        }
        Consignees::activeRecord($id);

        return redirect()->route('admin.consignees.index');
    }
}
