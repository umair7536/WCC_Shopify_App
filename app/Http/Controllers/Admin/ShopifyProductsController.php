<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Products\SyncProductsFire;
use App\Models\Accounts;
use App\Models\ShopifyProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Config;
use Validator;

class ShopifyProductsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('shopify_products_manage')) {
            return abort(401);
        }

        return view('admin.shopify_products.index');
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
            $ShopifyProducts = ShopifyProducts::getBulkData($request->get('id'));
            if($ShopifyProducts) {
                foreach($ShopifyProducts as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!ShopifyProducts::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ShopifyProducts::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $ShopifyProducts = ShopifyProducts::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($ShopifyProducts) {

            foreach($ShopifyProducts as $shopify_product) {

            }

            foreach($ShopifyProducts as $shopify_product) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$shopify_product->id.'"/><span></span></label>',
                    'image_src' => view('admin.shopify_products.image', compact('shopify_product'))->render(),
                    'title' => $shopify_product->title,
                    'inventory' => 'Coming Soon',
                    'product_type' => $shopify_product->product_type,
                    'vendor' => $shopify_product->vendor,
                    'actions' => view('admin.shopify_products.actions', compact('shopify_product'))->render(),
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
        if (! Gate::allows('shopify_products_create')) {
            return abort(401);
        }

        return view('admin.shopify_products.create',compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('shopify_products_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyProducts::createRecord($request, Auth::User()->account_id, Auth::User()->id)) {
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
     * Show Lead detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        if (!Gate::allows('shopify_products_manage')) {
            return abort(401);
        }

        $shopify_product = ShopifyProducts::getData($id);

        if(!$shopify_product) {
            return view('error', compact('lead_statuse'));
        }

//        $product_variants = ShopifyProductVariants::where([
//            'product_id' => $shopify_product->product_id,
//            'product_id' => $shopify_product->product_id,
//        ]);

        return view('admin.shopify_products.detail', compact('shopify_product'));
    }


    /**
     * Show the form for editing Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('shopify_products_edit')) {
            return abort(401);
        }

        $shopify_product = ShopifyProducts::getData($id);

        if(!$shopify_product) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.shopify_products.edit', compact('shopify_product'));
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
        if (! Gate::allows('shopify_products_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyProducts::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('shopify_products_destroy')) {
            return abort(401);
        }

        ShopifyProducts::deleteRecord($id);

        return redirect()->route('admin.shopify_products.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('shopify_products_inactive')) {
            return abort(401);
        }
        ShopifyProducts::inactiveRecord($id);

        return redirect()->route('admin.shopify_products.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('shopify_products_active')) {
            return abort(401);
        }
        ShopifyProducts::activeRecord($id);

        return redirect()->route('admin.shopify_products.index');
    }

    /**
     * Dispatch event for sync products.
     *
     * @return \Illuminate\Http\Response
     */
    public function syncProducts()
    {
        if (! Gate::allows('shopify_products_manage')) {
            return abort(401);
        }

        event(new SyncProductsFire(Accounts::find(Auth::User()->account_id)));

        flash('Products Sync Event is dispatched successfully.')->success()->important();

        return redirect()->route('admin.shopify_products.index');
    }
}
