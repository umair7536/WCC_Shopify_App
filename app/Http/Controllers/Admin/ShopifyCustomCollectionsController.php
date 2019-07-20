<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Products\SyncCollectsFire;
use App\Events\Shopify\Products\SyncCustomCollecionsFire;
use App\Models\Accounts;
use App\Models\ShopifyCollects;
use App\Models\ShopifyCustomCollections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;

class ShopifyCustomCollectionsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('shopify_custom_collections_manage')) {
            return abort(401);
        }

        return view('admin.shopify_custom_collections.index');
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
            $ShopifyCustomCollections = ShopifyCustomCollections::getBulkData($request->get('id'));
            if($ShopifyCustomCollections) {
                foreach($ShopifyCustomCollections as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!ShopifyCustomCollections::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ShopifyCustomCollections::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $ShopifyCustomCollections = ShopifyCustomCollections::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($ShopifyCustomCollections) {
            foreach($ShopifyCustomCollections as $shopify_custom_collection) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$shopify_custom_collection->id.'"/><span></span></label>',
                    'title' => $shopify_custom_collection->title,
                    'actions' => view('admin.shopify_custom_collections.actions', compact('shopify_custom_collection'))->render(),
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
        if (! Gate::allows('shopify_custom_collections_create')) {
            return abort(401);
        }

        return view('admin.shopify_custom_collections.create',compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('shopify_custom_collections_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyCustomCollections::createRecord($request, Auth::User()->account_id, Auth::User()->id)) {
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
        if (! Gate::allows('shopify_custom_collections_edit')) {
            return abort(401);
        }

        $shopify_custom_collection = ShopifyCustomCollections::getData($id);

        if(!$shopify_custom_collection) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.shopify_custom_collections.edit', compact('shopify_custom_collection'));
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
        if (! Gate::allows('shopify_custom_collections_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyCustomCollections::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('shopify_custom_collections_destroy')) {
            return abort(401);
        }

        ShopifyCustomCollections::deleteRecord($id);

        return redirect()->route('admin.shopify_custom_collections.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('shopify_custom_collections_inactive')) {
            return abort(401);
        }
        ShopifyCustomCollections::inactiveRecord($id);

        return redirect()->route('admin.shopify_custom_collections.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('shopify_custom_collections_active')) {
            return abort(401);
        }
        ShopifyCustomCollections::activeRecord($id);

        return redirect()->route('admin.shopify_custom_collections.index');
    }

    /**
     * Dispatch event for sync custom collections.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncCustomCollections()
    {
        if (! Gate::allows('shopify_custom_collections_manage')) {
            return abort(401);
        }

        event(new SyncCustomCollecionsFire(Accounts::find(Auth::User()->account_id)));

        /**
         * Dispatch Collects Event and Delte existing records
         */

        ShopifyCollects::where([
            'account_id' => Auth::User()->account_id
        ])->forceDelete();

        event(new SyncCollectsFire(Accounts::find(Auth::User()->account_id)));

        flash('Custom Collectons Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.shopify_custom_collections.index');
    }
}
