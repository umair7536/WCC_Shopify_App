<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookedPackets;
use App\Models\LoadSheetPackets;
use App\Models\LoadSheets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Config;
use Validator;

class LoadSheetsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('load_sheets_manage')) {
            return abort(401);
        }

        return view('admin.load_sheets.index', compact('load_sheets'));
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
            $LoadSheets = LoadSheets::getBulkData($request->get('id'));
            if($LoadSheets) {
                foreach($LoadSheets as $LoadSheet) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!LoadSheets::isChildExists($LoadSheet->id, Auth::User()->account_id)) {
                        $LoadSheet->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = LoadSheets::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $LoadSheets = LoadSheets::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($LoadSheets) {
            foreach($LoadSheets as $load_sheet) {
                $records["data"][] = array(
                    'load_sheet_id' => $load_sheet->load_sheet_id,
                    'total_packets' => $load_sheet->total_packets,
                    'created_at' => Carbon::parse($load_sheet->created_at)->format('M j, Y h:i A'),
                    'actions' => view('admin.load_sheets.actions', compact('load_sheet'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show Detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        if (!Gate::allows('load_sheets_manage')) {
            return abort(401);
        }

        $load_sheet = LoadSheets::getData($id);

        if(!$load_sheet) {
            return view('error');
        }

        $load_sheet_packets = LoadSheetPackets::where([
            'load_sheet_id' => $load_sheet->id,
            'account_id' => Auth::User()->account_id,
        ])->get();

        return view('admin.load_sheets.detail', compact('load_sheet', 'load_sheet_packets'));
    }

    /**
     * Download Sheet
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        if (!Gate::allows('load_sheets_manage')) {
            return abort(401);
        }

        $load_sheet = LoadSheets::getData($id);

        if(!$load_sheet) {
            return view('error');
        }

        BookedPackets::downloadLoadSheet($load_sheet->load_sheet_id, $load_sheet->account_id);
    }

}
