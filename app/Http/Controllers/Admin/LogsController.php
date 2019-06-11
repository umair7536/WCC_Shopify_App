<?php

namespace App\Http\Controllers\admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use DB;
use Auth;
use Validator;
use App\Models\AuditTrails;
use App\Models\AuditTrailTables;
use App\Models\AuditTrailActions;

class LogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('logs_manage')) {
            return abort(401);
        }
        return view('admin.logs.index');
    }

    /**
     * Display a listing of the logs.
     *
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $audittrails = AuditTrails::getBulkData($request->get('id'));
            if ($audittrails) {
                foreach ($audittrails as $audittrails) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!AuditTrails::isChildExists($audittrails->id, Auth::User()->account_id)) {
                        $audittrails->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }
        // Get Total Records
        $iTotalRecords = AuditTrails::getTotalRecords();

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $audittrails = AuditTrails::getRecords($iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if ($audittrails) {
            foreach ($audittrails as $audittrail) {

                $screen = AuditTrailTables::where('id', '=', $audittrail->audit_trail_table_name)->select('screen')->first();
                $action = AuditTrailActions::where('id', '=', $audittrail->audit_trail_action_name)->select('name')->first();
                $user = User::find($audittrail->user_id);

                $records["data"][] = array(
                    'id' => $audittrail->id,
                    'datetime' => $audittrail->created_at ? \Carbon\Carbon::parse($audittrail->created_at)->format('D M, j Y, H:i:a') : null,
                    'screen' => $screen->screen,
                    'user' => $user->name,
                    'actions' => '<b>' . $action->name . '</b>',
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }
}
