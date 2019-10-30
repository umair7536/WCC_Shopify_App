<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Webhooks\CreateWebhooksFire;
use App\Models\Accounts;
use App\Models\ShopifyWebhooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;

class ShopifyWebhooksController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('shopify_webhooks_manage')) {
            return abort(401);
        }

        return view('admin.shopify_webhooks.index');
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
            $ShopifyWebhooks = ShopifyWebhooks::getBulkData($request->get('id'));
            if ($ShopifyWebhooks) {
                foreach ($ShopifyWebhooks as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!ShopifyWebhooks::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ShopifyWebhooks::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $ShopifyWebhooks = ShopifyWebhooks::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if ($ShopifyWebhooks) {
            foreach ($ShopifyWebhooks as $shopify_webhook) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $shopify_webhook->id . '"/><span></span></label>',
                    'address' => $shopify_webhook->address,
                    'topic' => $shopify_webhook->topic,
                    'format' => $shopify_webhook->format,
                    'actions' => view('admin.shopify_webhooks.actions', compact('shopify_webhook'))->render(),
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
        if (!Gate::allows('shopify_webhooks_create')) {
            return abort(401);
        }

        return view('admin.shopify_webhooks.create', compact('city'));
    }


    public function sortorder_save()
    {

        $city = DB::table('shopify_webhooks')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderBy('sort_number', 'ASC')->get();
        $itemID = Input::get('itemID');
        $itemIndex = Input::get('itemIndex');
        if ($itemID) {
            foreach ($city as $cit) {
                $sort = DB::table('shopify_webhooks')->where('id', '=', $itemID)->update(array('sort_number' => $itemIndex));
                $myarray = ['status' => "Data Sort Successfully"];
                return response()->json($myarray);
            }
        } else {
            $myarray = ['status' => "Data Not Sort"];
            return response()->json($myarray);
        }
    }

    public function sortorder()
    {

        $city = DB::table('shopify_webhooks')->whereNull('deleted_at')->where(['account_id' => Auth::User()->account_id])->orderby('sort_number', 'ASC')->get();
        return view('admin.shopify_webhooks.sort', compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('shopify_webhooks_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (ShopifyWebhooks::createRecord($request, Auth::User()->account_id)) {
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
            'topic' => 'required',
            'format' => 'required',
            'address' => 'required',
        ]);
    }

    /**
     * Sync data from
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sync(Request $request)
    {
        if (!Gate::allows('shopify_webhooks_create')) {
            return abort(401);
        }

        if (ShopifyWebhooks::syncData(Auth::User()->account_id)) {
            flash('Records has been synced successfully.')->success()->important();
        } else {
            flash('Something went wrong, please try again later.')->success()->important();
        }
        return redirect()->route('admin.shopify_webhooks.index');
    }

    /**
     * Sync data from
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        if (!Gate::allows('shopify_webhooks_create')) {
            return abort(401);
        }

        /**
         * Dispatch Events
         */
        $account = Accounts::find(Auth::User()->account_id);
        event(new CreateWebhooksFire($account));

        flash('Refresh Webhooks event is fired successfully.')->success()->important();
        return redirect()->route('admin.shopify_webhooks.index');
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('shopify_webhooks_edit')) {
            return abort(401);
        }

        $shopify_webhook = ShopifyWebhooks::getData($id);

        if (!$shopify_webhook) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.shopify_webhooks.edit', compact('shopify_webhook'));
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
        if (!Gate::allows('shopify_webhooks_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if (ShopifyWebhooks::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (!Gate::allows('shopify_webhooks_destroy')) {
            return abort(401);
        }

        ShopifyWebhooks::deleteRecord($id);

        return redirect()->route('admin.shopify_webhooks.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('shopify_webhooks_inactive')) {
            return abort(401);
        }
        ShopifyWebhooks::inactiveRecord($id);

        return redirect()->route('admin.shopify_webhooks.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('shopify_webhooks_active')) {
            return abort(401);
        }
        ShopifyWebhooks::activeRecord($id);

        return redirect()->route('admin.shopify_webhooks.index');
    }
}
