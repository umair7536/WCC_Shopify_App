<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Orders\SyncOrdersFire;
use App\Models\Accounts;
use App\Models\ShopifyOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Auth;
use Validator;

class ShopifyOrdersController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('shopify_orders_manage')) {
            return abort(401);
        }

        return view('admin.shopify_orders.index');
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
            $ShopifyOrders = ShopifyOrders::getBulkData($request->get('id'));
            if($ShopifyOrders) {
                foreach($ShopifyOrders as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!ShopifyOrders::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ShopifyOrders::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $ShopifyOrders = ShopifyOrders::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($ShopifyOrders) {
            foreach($ShopifyOrders as $shopify_order) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$shopify_order->id.'"/><span></span></label>',
                    'name' => $shopify_order->name,
                    'actions' => view('admin.shopify_orders.actions', compact('shopify_order'))->render(),
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
        if (! Gate::allows('shopify_orders_create')) {
            return abort(401);
        }

        return view('admin.shopify_orders.create',compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('shopify_orders_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyOrders::createRecord($request, Auth::User()->account_id, Auth::User()->id)) {
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
            'title' => 'required',
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
        if (! Gate::allows('shopify_orders_edit')) {
            return abort(401);
        }

        $shopify_order = ShopifyOrders::getData($id);

        if(!$shopify_order) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.shopify_orders.edit', compact('shopify_order'));
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
        if (! Gate::allows('shopify_orders_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyOrders::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('shopify_orders_destroy')) {
            return abort(401);
        }

        ShopifyOrders::deleteRecord($id);

        return redirect()->route('admin.shopify_orders.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('shopify_orders_inactive')) {
            return abort(401);
        }
        ShopifyOrders::inactiveRecord($id);

        return redirect()->route('admin.shopify_orders.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('shopify_orders_active')) {
            return abort(401);
        }
        ShopifyOrders::activeRecord($id);

        return redirect()->route('admin.shopify_orders.index');
    }

    /**
     * Dispatch event for sync custom collections.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncOrders()
    {
        if (! Gate::allows('shopify_orders_manage')) {
            return abort(401);
        }

        event(new SyncOrdersFire(Accounts::find(Auth::User()->account_id)));

        flash('Orders Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.shopify_orders.index');
    }
}
