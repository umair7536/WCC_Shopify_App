<?php

namespace App\Http\Controllers\Admin;

use App\Models\ShopifyCustomers;
use App\Models\ShopifyProducts;
use App\Models\ShopifyProductVariants;
use App\Models\ShopifyShops;
use App\Models\TicketProducts;
use App\Models\TicketRepairs;
use App\Models\Tickets;
use App\Models\TicketStatuses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use ZfrShopify\ShopifyClient;

class TicketsController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('tickets_manage')) {
            return abort(401);
        }

        $ticket_statuses = TicketStatuses::getTicketStatuses();
        $ticket_statuses->prepend('Select a Status', '');

        return view('admin.tickets.index', compact('ticket_statuses'));
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
            $Tickets = Tickets::getBulkData($request->get('id'));
            if ($Tickets) {
                foreach ($Tickets as $city) {
                    // Check if child records exists or not, If exist then disallow to delete it.
                    if (!Tickets::isChildExists($city->id, Auth::User()->account_id)) {
                        $city->delete();
                    }
                }
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        // Get Total Records
        $iTotalRecords = Tickets::getTotalRecords($request, Auth::User()->account_id);


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $Tickets = Tickets::getRecords($request, $iDisplayStart, $iDisplayLength, Auth::User()->account_id);

        if ($Tickets) {

            $ticket_ids = array();
            foreach ($Tickets as $ticket) {
                $ticket_ids[] = $ticket->id;
            }

            $ticket_products = TicketProducts::whereIn('ticket_id', $ticket_ids)->select('serial_number', 'ticket_id')->get();
            $ticket_products_mapping = array();
            if ($ticket_products) {
                foreach ($ticket_products as $ticket_product) {
                    $ticket_products_mapping[$ticket_product->ticket_id][] = $ticket_product->serial_number;
                }
            }
//            echo '<pre>';
//            print_r($ticket_products_mapping);
//            exit;

            foreach ($Tickets as $ticket) {

                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $ticket->id . '"/><span></span></label>',
                    'number' => $ticket->number,
                    'customer_name' => $ticket->first_name . ' ' . $ticket->last_name . '<br/>' . $ticket->email . (($ticket->phone) ? '<br/>' . $ticket->phone : ''),
                    'total_products' => $ticket->total_products,
                    'serial_number' => isset($ticket_products_mapping[$ticket->id]) ? implode('<br/>', $ticket_products_mapping[$ticket->id]) : '',
                    'ticket_status_id' => view('admin.tickets.ticket_status', compact('ticket'))->render(),
                    'created_at' => Carbon::parse($ticket->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.tickets.actions', compact('ticket'))->render(),
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
        if (!Gate::allows('tickets_create')) {
            return abort(401);
        }

//        $products = ShopifyProducts::where([
//            'account_id' => Auth::User()->account_id
//        ])->get()->toArray();

        $products = array();

        $ticket_statuses = TicketStatuses::where([
            'account_id' => Auth::User()->account_id,
            'active' => 1,
        ])->orderBy('sort_number', 'asc')
            ->get()
            ->pluck('name', 'id');

        $status = ['' => 'Select a Status'];

        if ($ticket_statuses) {
            $ticket_statuses = ($status + $ticket_statuses->toArray());
        } else {
            $ticket_statuses = $status;
        }

        $ticket_status = TicketStatuses::where([
            'account_id' => Auth::User()->account_id,
            'slug' => 'open'
        ])->first();

        if ($ticket_status) {
            $ticket_status_id = $ticket_status->id;
        } else {
            $ticket_status_id = 0;
        }

        return view('admin.tickets.create', compact('products', 'ticket_statuses', 'ticket_status_id'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('tickets_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        $customerId = 0;

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if ($request->get('customer_confirmation') != '' && $request->get('customer_confirmation') == '1') {
            /**
             * Set Customer's Basic Informaton
             */
            $shopifyCustomer = array(
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'verified_email' => true
            );

            /**
             * Create Customer in Shopify
             */
            $shop = ShopifyShops::where([
                'account_id' => Auth::User()->account_id
            ])->first();

            $customer = [];

            if ($shop) {
                try {
                    $shopifyClient = new ShopifyClient([
                        'private_app' => false,
                        'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                        'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                        'access_token' => $shop->access_token,
                        'shop' => $shop->myshopify_domain
                    ]);

                    $customer = $shopifyClient->createCustomer($shopifyCustomer);

                } catch (\Exception $e) {
                    return response()->json(array(
                        'status' => 0,
                        'message' => [$e->getMessage()],
                    ));
                }
            } else {
                return response()->json(array(
                    'status' => 0,
                    'message' => ['Shop is not connected, Please contact App Administrator.'],
                ));
            }

            if (count($customer)) {
                /**
                 * Merge Customer ID with Request Object
                 */
                $request->merge(['customer_id' => $customer['id']]);


                $customer['customer_id'] = $customer['id'];
                unset($customer['id']);

                if (isset($customer['default_address']) && count($customer['default_address'])) {
                    $default_address = $customer['default_address'];
                    unset($default_address['id']);
                    unset($default_address['customer_id']);
                    $customer = array_merge($customer, $default_address);
                    $customer['default_address'] = json_encode($customer['default_address']);
                }

                /**
                 * Set Address based on array provided
                 */
                if (count($customer['addresses'])) {
                    $customer['addresses'] = json_encode($customer['addresses']);
                }

                $customer_processed = ShopifyCustomers::prepareRecord($customer);
                $customer_processed['account_id'] = $shop['account_id'];

                $customer_record = ShopifyCustomers::where([
                    'customer_id' => $customer_processed['customer_id'],
                    'account_id' => $customer_processed['account_id'],
                ])->select('id')->first();

                if ($customer_record) {
                    ShopifyCustomers::where([
                        'customer_id' => $customer_processed['customer_id'],
                        'account_id' => $customer_processed['account_id'],
                    ])->update($customer_processed);
                } else {
                    //echo 'Product Created: ' . $customer_processed['title'] . "\n";
                    ShopifyCustomers::create($customer_processed);
                }
            } else {
                return response()->json(array(
                    'status' => 0,
                    'message' => ['Something went wrong, please try again later.'],
                ));
            }
        } else {
            $customerId = $request->get('customer_id');
        }

        if (Tickets::createRecord($request, Auth::User()->account_id)) {
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
            'customer_id' => 'sometimes|nullable',
            'first_name' => 'sometimes|nullable',
            'last_name' => 'sometimes|nullable',
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable',
            'total_products' => 'required|numeric|min:1',
            'product_id' => 'required|array',
            'variant_id' => 'required|array',
            'serial_number' => 'required|array',
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
        if (!Gate::allows('tickets_edit')) {
            return abort(401);
        }

        $ticket = Tickets::findOrFail($id);

        if (!$ticket) {
            return view('error', compact('lead_statuse'));
        }

        $shopify_customer = ShopifyCustomers::where([
            'customer_id' => $ticket->customer_id,
            'account_id' => Auth::User()->account_id
        ])->first();

        $products = ShopifyProducts::where([
            'account_id' => Auth::User()->account_id
        ])->get()->toArray();

        $ticket_statuses = TicketStatuses::where([
            'account_id' => Auth::User()->account_id,
            'active' => 1,
        ])->orderBy('sort_number', 'asc')
            ->get()
            ->pluck('name', 'id');

        $status = ['' => 'Select a Status'];

        if ($ticket_statuses) {
            $ticket_statuses = ($status + $ticket_statuses->toArray());
        } else {
            $ticket_statuses = $status;
        }

        $relationships = TicketProducts::where(array(
            'ticket_id' => $ticket->id
        ))->select('product_id')->get();

        $ticket_products = collect(new ShopifyProducts());

        if ($relationships->count()) {
            $ticket_products = ShopifyProducts
                ::join('ticket_products', 'ticket_products.product_id', '=', 'shopify_products.product_id')
                ->whereIn('shopify_products.product_id', $relationships)
                ->where(['shopify_products.account_id' => Auth::User()->account_id])
                ->select(
                    'shopify_products.*',
                    'ticket_products.id',
                    'ticket_products.serial_number',
                    'ticket_products.variant_id',
                    'ticket_products.customer_feedback'
                )
                ->groupBy('ticket_products.id')
                ->get()->getDictionary();
        }

        /**
         * Product Repairs
         */
        $repair_relationships = TicketRepairs::where(array(
            'ticket_id' => $ticket->id
        ))->select('product_id')->get();

        $ticket_repairs = collect(new ShopifyProducts());

        if ($repair_relationships->count()) {
            $ticket_repairs = ShopifyProducts
                ::join('ticket_repairs', 'ticket_repairs.product_id', '=', 'shopify_products.product_id')
                ->whereIn('shopify_products.product_id', $repair_relationships)
                ->where(['shopify_products.account_id' => Auth::User()->account_id])
                ->select(
                    'shopify_products.*',
                    'ticket_repairs.id',
                    'ticket_repairs.serial_number',
                    'ticket_repairs.variant_id',
                    'ticket_repairs.customer_feedback'
                )
                ->groupBy('ticket_repairs.id')
                ->get()->getDictionary();
        }

        return view('admin.tickets.edit', compact('ticket', 'ticket_products', 'ticket_repairs', 'relationships', 'repair_relationships', 'products', 'ticket_statuses', 'shopify_customer'));
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
        if (!Gate::allows('tickets_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        $customerId = 0;

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        if ($request->get('customer_confirmation') != '' && $request->get('customer_confirmation') == '1') {
            /**
             * Set Customer's Basic Informaton
             */
            $shopifyCustomer = array(
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'verified_email' => true
            );

            /**
             * Create Customer in Shopify
             */
            $shop = ShopifyShops::where([
                'account_id' => Auth::User()->account_id
            ])->first();

            $customer = [];

            if ($shop) {
                try {
                    $shopifyClient = new ShopifyClient([
                        'private_app' => false,
                        'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                        'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                        'access_token' => $shop->access_token,
                        'shop' => $shop->myshopify_domain
                    ]);

                    $customer = $shopifyClient->createCustomer($shopifyCustomer);

                } catch (\Exception $e) {
                    return response()->json(array(
                        'status' => 0,
                        'message' => [$e->getMessage()],
                    ));
                }
            } else {
                return response()->json(array(
                    'status' => 0,
                    'message' => ['Shop is not connected, Please contact App Administrator.'],
                ));
            }

            if (count($customer)) {
                /**
                 * Merge Customer ID with Request Object
                 */
                $request->merge(['customer_id' => $customer['id']]);


                $customer['customer_id'] = $customer['id'];
                unset($customer['id']);

                if (isset($customer['default_address']) && count($customer['default_address'])) {
                    $default_address = $customer['default_address'];
                    unset($default_address['id']);
                    unset($default_address['customer_id']);
                    $customer = array_merge($customer, $default_address);
                    $customer['default_address'] = json_encode($customer['default_address']);
                }

                /**
                 * Set Address based on array provided
                 */
                if (count($customer['addresses'])) {
                    $customer['addresses'] = json_encode($customer['addresses']);
                }

                $customer_processed = ShopifyCustomers::prepareRecord($customer);
                $customer_processed['account_id'] = $shop['account_id'];

                $customer_record = ShopifyCustomers::where([
                    'customer_id' => $customer_processed['customer_id'],
                    'account_id' => $customer_processed['account_id'],
                ])->select('id')->first();

                if ($customer_record) {
                    ShopifyCustomers::where([
                        'customer_id' => $customer_processed['customer_id'],
                        'account_id' => $customer_processed['account_id'],
                    ])->update($customer_processed);
                } else {
                    //echo 'Product Created: ' . $customer_processed['title'] . "\n";
                    ShopifyCustomers::create($customer_processed);
                }
            } else {
                return response()->json(array(
                    'status' => 0,
                    'message' => ['Something went wrong, please try again later.'],
                ));
            }
        } else {
            $customerId = $request->get('customer_id');
        }

        if (Tickets::updateRecord($id, $request, Auth::User()->account_id)) {
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
     * Show Lead detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        if (!Gate::allows('tickets_manage')) {
            return abort(401);
        }
        $ticket = Tickets::findOrFail($id);

        if (!$ticket) {
            return view('error', compact('lead_statuse'));
        }

        $relationships = TicketProducts::where(array(
            'ticket_id' => $ticket->id
        ))->select('product_id')->get();

        $ticket_products = array();
        $ticket_products = collect(new ShopifyProducts());

        if ($relationships->count()) {
            $ticket_products = ShopifyProducts::join('ticket_products', 'ticket_products.product_id', '=', 'shopify_products.product_id')->whereIn('shopify_products.product_id', $relationships)->where(['shopify_products.account_id' => Auth::User()->account_id])->select('shopify_products.*', 'ticket_products.id', 'ticket_products.serial_number', 'ticket_products.customer_feedback')->groupBy('ticket_products.id')->get()->getDictionary();
        }

        return view('admin.tickets.detail', compact('ticket', 'ticket_products', 'relationships'));
    }


    /**
     * Remove Permission from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('tickets_destroy')) {
            return abort(401);
        }

        Tickets::DeleteRecord($id);

        return redirect()->route('admin.tickets.index');

    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('tickets_inactive')) {
            return abort(401);
        }
        Tickets::InactiveRecord($id);

        return redirect()->route('admin.tickets.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('tickets_active')) {
            return abort(401);
        }

        Tickets::activeRecord($id);

        return redirect()->route('admin.tickets.index');
    }

    /*
     * Function get the variable to search in database to get the patient
     *
     * */
    public function getCustomer(Request $request)
    {
        $name = $request->q;
        $account_id = Auth::User()->account_id;

        if (is_numeric($name)) {
            $patient = ShopifyCustomers::where([
                ['account_id', '=', $account_id],
                ['phone', 'LIKE', "%{$name}%"]
            ])->select('name', 'customer_id', 'phone')->get();
        } else {
            $patient = ShopifyCustomers::where([
                ['account_id', '=', $account_id],
                ['name', 'LIKE', "%{$name}%"]
            ])
                ->orWhere([
                    ['account_id', '=', $account_id],
                    ['email', 'LIKE', "%{$name}%"]
                ])
                ->select('name', 'customer_id', 'phone')->get();
        }

        return response()->json($patient);
    }

    /**
     * Load all Ticket Statuses.
     *
     * @param mixed Request $request
     * @return mixed
     */
    public function showTicketStatuses(Request $request)
    {
        if (!Gate::allows('tickets_manage')) {
            return abort(401);
        }

        $ticket = Tickets::findOrFail($request->get('id'));

        $ticket_statuses = TicketStatuses::getTicketStatuses();
        $ticket_statuses->prepend('Select a Status', '');

        $ticket_status = TicketStatuses::where('id', '=', $ticket->ticket_status_id)->first();

        return view('admin.tickets.ticket_status_popup', compact('ticket', 'ticket_statuses', 'ticket_status'));
    }

    /**
     * Update Lead Status
     *
     * @param  mixed \Illuminate\Http\Request $request
     * @return mixed \Illuminate\Http\Response
     */
    public function storeTicketStatuses(Request $request)
    {
        $data = $request->all();
        $ticket = Tickets::findOrFail($request->get('id'));

        DB::table('tickets')
            ->where('id', $ticket->id)
            ->update([
                'ticket_status_id' => $data['ticket_status_id']
            ]);

        return response()->json(['status' => 1]);
    }

    /**
     * Show Lead detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function createDraftOrder($id)
    {
        if (!Gate::allows('tickets_manage')) {
            return abort(401);
        }
        $ticket = Tickets::findOrFail($id);

        if (!$ticket) {
            return view('error', compact('lead_statuse'));
        }

        $relationships = TicketProducts::where(array(
            'ticket_id' => $ticket->id
        ))->select('product_id')->get();

        $product_variants = ShopifyProductVariants
            ::where(array(
                'account_id' => Auth::User()->account_id,
                'position' => '1',
            ))
            ->whereIn('product_id', $relationships)
            ->select('variant_id', 'product_id', 'price')
            ->get();

        if ($product_variants) {

            $line_items = array();

            foreach($product_variants as $product_variant) {
                $product = ShopifyProducts::where(array(
                    'account_id' => Auth::User()->account_id,
                    'product_id' => $product_variant->product_id,
                ))->first();

                if(!$product) {
                    flash('One or more products not found.')->error()->important();
                    return back()->withInput();
                }

                $line_items[] = array(
                    'variant_id' => $product_variant->variant_id,
//                    'title' => $product->title,
//                    'price' => 0.00,
                    'quantity' => 1,
                );
            }

            $shop = ShopifyShops::where([
                'account_id' => Auth::User()->account_id
            ])->first();

            if ($shop) {

                try {
                    $shopifyClient = new ShopifyClient([
                        'private_app' => false,
                        'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                        'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                        'access_token' => $shop->access_token,
                        'shop' => $shop->myshopify_domain
                    ]);

                    $draftOrder = array(
                        'line_items' => $line_items,
                        'customer' => array(
                            'id' => $ticket->customer_id
                        ),
                        'use_customer_default_address' => true
                    );

//                    echo '<pre>';
//                    print_r($draftOrder);
//                    echo '</pre>';
//                    exit;

                    $response = $shopifyClient->createDraftOrder($draftOrder);
//                    $response['id'] = '207364522078';

                    if(count($response)) {
                        $url = 'https://' . $shop->myshopify_domain . '/admin/draft_orders/' . $response['id'];
                        return Redirect::to($url);
                    } else {
                        flash('Something went wrong, please try again later.')->error()->important();
                        return back()->withInput();
                    }
                } catch (\Exception $exception) {
                    flash($exception->getMessage())->error()->important();
                    return back()->withInput();
                }
            } else {
                flash('Shop not found. Pleaes contact with support.')->error()->important();
                return back()->withInput();
            }

        } else {
            flash('Something went wrong, please try again later.')->error()->important();
            return back()->withInput();
        }
    }


    /*
     * Function get the variable to search in database to get the patient
     *
     * */
    public function getProduct(Request $request)
    {
        $name = $request->q;
        $account_id = Auth::User()->account_id;


        $products = ShopifyProducts
            ::join('shopify_product_variants', 'shopify_product_variants.product_id', '=', 'shopify_products.product_id')
            ->where([
                'shopify_products.account_id' => $account_id
            ])
            ->where(function ($query) use ($name) {
                $query->orWhere('shopify_products.title', 'LIKE', "%{$name}%");
                $query->orWhere('shopify_product_variants.title', 'LIKE', "%{$name}%");
            })
            ->select('shopify_product_variants.variant_id', 'shopify_product_variants.product_id', 'shopify_products.title as product_title', 'shopify_product_variants.title as variant_title')
            ->orderBy('shopify_products.title', 'asc')
            ->orderBy('shopify_product_variants.position', 'asc')
            ->get();


        $products_array = array();
        $product_ids = array();
        $product_counts = array();
        if($products) {
            foreach($products as $single_product) {
                if(!array_key_exists($single_product['product_id'], $product_counts)) {
                    $product_counts[$single_product['product_id']] = 1;
                } else {
                    $product_counts[$single_product['product_id']] += 1;
                }
            }

            foreach($products as $single_product) {
                if(!in_array($single_product['product_id'], $product_ids)) {
                    $product_ids[] = $single_product['product_id'];
                    if($product_counts[$single_product['product_id']] == 1) {
                        $products_array[] = array(
                            'id' => $single_product['variant_id'],
                            'name' => $single_product['product_title'],
                        );
                    } else {
                        $products_array[] = array(
                            'id' => $single_product['variant_id'],
                            'name' => $single_product['product_title'],
                            'product_id' => $single_product['product_id'],
                        );
                        $products_array[] = array(
                            'id' => $single_product['variant_id'],
                            'name' => "&nbsp;&nbsp;&nbsp;&nbsp;" . $single_product['variant_title'] . "&nbsp;(default)",
                            'product_id' => $single_product['product_id'],
                        );
                    }
                } else {
                    $products_array[] = array(
                        'id' => $single_product['variant_id'],
                        'name' => "&nbsp;&nbsp;&nbsp;&nbsp;" . $single_product['variant_title'],
                        'product_id' => $single_product['product_id'],
                    );
                }
            }
        }

        return response()->json($products_array);
    }

    /**
     * Get Product Detail with Variant ID
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProductDetail(Request $request) {
        $data = array(
            'status' => 0,
            'product' => array()
        );

        if($request->get('variant_id') != '') {

            $account_id = Auth::User()->account_id;

            $variant = ShopifyProducts
                ::join('shopify_product_variants', 'shopify_product_variants.product_id', '=', 'shopify_products.product_id')
                ->where([
                    'shopify_products.account_id' => $account_id,
                    'shopify_product_variants.variant_id' => $request->get('variant_id')
                ])
                ->select(
                    'shopify_product_variants.variant_id',
                    'shopify_product_variants.product_id',
                    'shopify_products.title as product_title',
                    'shopify_product_variants.price as product_price',
                    'shopify_products.image_src as product_image'
                )
                ->first();

            if($variant) {
                $data = array(
                    'status' => 1,
                    'product' => $variant->toArray()
                );
            }
        }

        return response()->json($data);
    }
}
