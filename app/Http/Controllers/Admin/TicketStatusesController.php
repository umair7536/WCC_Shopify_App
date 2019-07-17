<?php

namespace App\Http\Controllers\Admin;

use App\Models\TicketStatuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;

class TicketStatusesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('ticket_statuses_manage')) {
            return abort(401);
        }

        return view('admin.ticket_statuses.index');
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
            $TicketStatuses = TicketStatuses::getBulkData($request->get('id'));
            if($TicketStatuses) {
                foreach($TicketStatuses as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!TicketStatuses::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = TicketStatuses::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $TicketStatuses = TicketStatuses::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($TicketStatuses) {
            foreach($TicketStatuses as $ticket_statuse) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$ticket_statuse->id.'"/><span></span></label>',
                    'name' => $ticket_statuse->name,
                    'show_color' => ($ticket_statuse->show_color) ? 'Yes' : 'No',
                    'color' => ($ticket_statuse->show_color) ? '<span class="btn btn-xs" style="background-color: ' . $ticket_statuse->color . ' !important;">' . $ticket_statuse->color . '</span>' : '',
                    'actions' => view('admin.ticket_statuses.actions', compact('ticket_statuse'))->render(),
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
        if (! Gate::allows('ticket_statuses_create')) {
            return abort(401);
        }

        $show_colors = 0;

        return view('admin.ticket_statuses.create',compact('show_colors'));
    }


    public function sortorder_save(){

        $city = DB::table('ticket_statuses')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_number', 'ASC')->get();
        $itemID=Input::get('itemID');
        $itemIndex=Input::get('itemIndex');
        if($itemID){
            foreach ($city as $cit) {
                $sort=DB::table('ticket_statuses')->where('id', '=', $itemID)->update(array('sort_number' => $itemIndex));
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

        $city = DB::table('ticket_statuses')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderby('sort_number', 'ASC')->get();
        return view('admin.ticket_statuses.sort', compact('city'));
    }
    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('ticket_statuses_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(TicketStatuses::createRecord($request, Auth::User()->account_id)) {
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
        if (! Gate::allows('ticket_statuses_edit')) {
            return abort(401);
        }

        $ticket_statuse = TicketStatuses::getData($id);

        if(!$ticket_statuse) {
            return view('error', compact('lead_statuse'));
        }

        $show_colors = $ticket_statuse->show_color;

        return view('admin.ticket_statuses.edit', compact('ticket_statuse', 'show_colors'));
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
        if (! Gate::allows('ticket_statuses_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(TicketStatuses::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('ticket_statuses_destroy')) {
            return abort(401);
        }

        TicketStatuses::deleteRecord($id);

        return redirect()->route('admin.ticket_statuses.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('ticket_statuses_inactive')) {
            return abort(401);
        }
        TicketStatuses::inactiveRecord($id);

        return redirect()->route('admin.ticket_statuses.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('ticket_statuses_active')) {
            return abort(401);
        }
        TicketStatuses::activeRecord($id);

        return redirect()->route('admin.ticket_statuses.index');
    }
}
