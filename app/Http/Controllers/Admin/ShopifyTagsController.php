<?php

namespace App\Http\Controllers\Admin;

use App\Models\ShopifyProductTags;
use App\Models\ShopifyTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Config;
use Validator;

class ShopifyTagsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('shopify_tags_manage')) {
            return abort(401);
        }

//        $orderBy = 'created_at';
//        $order = 'desc';
//
//        $records = ShopifyTags
//            ::leftjoin('shopify_product_tags', 'shopify_product_tags.tag_id', '=', 'shopify_tags.id')
//            ->limit(10)->offset(0)
//            ->orderBy($orderBy,$order)
//            ->groupBy('shopify_tags.id')
//            ->select('shopify_tags.*', DB::raw('COUNT(shopify_product_tags.id) as products'))
//            ->get();
//        echo '<pre>';
//        print_r($records->toArray());
//        exit;

        return view('admin.shopify_tags.index');
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
            $ShopifyTags = ShopifyTags::getBulkData($request->get('id'));
            if($ShopifyTags) {
                foreach($ShopifyTags as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if(!ShopifyTags::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = ShopifyTags::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $ShopifyTags = ShopifyTags::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if($ShopifyTags) {
            $tagsArray = [];
            foreach($ShopifyTags as $shopify_tag) {
                $tagsArray[] = $shopify_tag->id;
            }

            $totalProducts = ShopifyProductTags::where([
                'account_id' => Auth::User()->account_id
            ])->whereIn('tag_id', $tagsArray)
                ->select('tag_id', DB::raw('COUNT(id) as products'))
                ->groupBy('tag_id')
                ->get()->keyBy('tag_id');

            if($totalProducts) {
                $totalProducts = $totalProducts->toArray();
            } else {
                $totalProducts = [];
            }

            foreach($ShopifyTags as $shopify_tag) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$shopify_tag->id.'"/><span></span></label>',
                    'name' => $shopify_tag->name,
                    'products' => $shopify_tag->products,
//                    'products' => array_key_exists($shopify_tag->id, $totalProducts) ? $totalProducts[$shopify_tag->id]['products'] : 0,
                    'actions' => view('admin.shopify_tags.actions', compact('shopify_tag'))->render(),
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
        if (! Gate::allows('shopify_tags_create')) {
            return abort(401);
        }

        return view('admin.shopify_tags.create',compact('city'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('shopify_tags_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyTags::createRecord($request, Auth::User()->account_id, Auth::User()->id)) {
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
        if (! Gate::allows('shopify_tags_edit')) {
            return abort(401);
        }

        $shopify_tag = ShopifyTags::getData($id);

        if(!$shopify_tag) {
            return view('error', compact('lead_statuse'));
        }

        return view('admin.shopify_tags.edit', compact('shopify_tag'));
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
        if (! Gate::allows('shopify_tags_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if(ShopifyTags::updateRecord($id, $request, Auth::User()->account_id)) {
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
        if (! Gate::allows('shopify_tags_destroy')) {
            return abort(401);
        }

        ShopifyTags::deleteRecord($id);

        return redirect()->route('admin.shopify_tags.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (! Gate::allows('shopify_tags_inactive')) {
            return abort(401);
        }
        ShopifyTags::inactiveRecord($id);

        return redirect()->route('admin.shopify_tags.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (! Gate::allows('shopify_tags_active')) {
            return abort(401);
        }
        ShopifyTags::activeRecord($id);

        return redirect()->route('admin.shopify_tags.index');
    }
}
