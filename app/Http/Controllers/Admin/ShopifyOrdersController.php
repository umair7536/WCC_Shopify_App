<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Orders\SyncOrdersFire;
use App\Helpers\ShopifyHelper;
use App\Models\Accounts;
use App\Models\ShopifyCustomers;
use App\Models\ShopifyOrders;
use App\Models\ShopifyShops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Auth;
use Validator;
use ZfrShopify\ShopifyClient;

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

            /**
             * On result time propare customer
             */
            $customer_ids = array();
            foreach($ShopifyOrders as $shopify_order) {
                $customer_ids[] = $shopify_order->customer_id;
            }

            $customers = ShopifyCustomers::whereIn('customer_id', $customer_ids)
                ->select('customer_id', 'name', 'email', 'phone')
                ->get()->keyBy('customer_id');

            foreach($ShopifyOrders as $shopify_order) {

                $customer = [$shopify_order->email];
                if(isset($customers[$shopify_order->customer_id])) {
                    $customer = [
                        $customers[$shopify_order->customer_id]->name,
                        $shopify_order->email,
                        $customers[$shopify_order->customer_id]->phone,
                    ];
                    $customer = array_filter($customer);
                }

                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$shopify_order->id.'"/><span></span></label>',
                    'name' => $shopify_order->name,
                    'closed_at' => Carbon::parse($shopify_order->created_at)->diffForHumans(),
                    'customer_email' => implode('<br/>', $customer),
                    'financial_status' => $shopify_order->financial_status,
                    'fulfillment_status' => ($shopify_order->fulfillment_status) ? $shopify_order->fulfillment_status : 'pending',
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


    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyBookFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'id' => 'required',
            'shop' => 'required',
        ]);
    }

    /**
     * Incoming Order booking preperation
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function book(Request $request)
    {
        if (! Gate::allows('shopify_orders_manage')) {
            return abort(401);
        }

        $validator = $this->verifyBookFields($request);

        if (!$validator->fails()) {

            /**
             * Grab Shop
             */
            $shop = ShopifyShops::where([
                'account_id' => Auth::User()->account_id
            ])->first();

            if($shop) {
                try {

                    /**
                     * Prepare Shopify Request
                     */
                    $shopifyClient = new ShopifyClient([
                        'private_app' => false,
                        'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                        'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                        'access_token' => $shop->access_token,
                        'shop' => $shop->myshopify_domain
                    ]);

                    /**
                     * Retrieve Order from Shopify
                     */
                    $order = $shopifyClient->getOrder([
                        'id' => (int) $request->get('id')
                    ]);

                    ShopifyHelper::syncSingleOrder($order, $shop->toArray());

                    return redirect()->route('admin.booked_packets.create',['order_id' => $order['id']]);

                } catch (\Exception $exception) {}
            }
        }

        flash('Incoming request is not valid.')->error()->important();
        return redirect()->route('admin.shopify_orders.index');
    }

}
