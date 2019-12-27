<?php

namespace App\Http\Controllers;

use App\Events\Shopify\Orders\SingleOrderFulfillmentFire;
use App\Events\Shopify\Products\SyncProductsFire;
use App\Events\Shopify\Products\UploadVariantsFire;
use App\Models\Accounts;
use App\Models\HeavyLifter;
use App\Models\LeopardsSettings;
use App\Models\ShopifyJobs;
use App\Models\ShopifyLocations;
use App\Models\ShopifyOrders;
use Config;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * Dispatch to check if Auto Fulfillment is 'true' or 'false'
         * if 'true' then order will be fulfilled automatically
         * if 'false' then order will not be fulfilled
         */
        $order = ShopifyOrders::where([
            'account_id' => Auth::User()->account_id
        ])->whereIn('order_id', ['2000622747741'])
            ->select('order_id', 'name', 'order_number', 'customer_id', 'account_id')
            ->first();
        event(new SingleOrderFulfillmentFire($order->toArray(), 'LE781929495'));
        echo 'event is dispatched';
        exit;


        return view('home');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings()
    {
        $accounts = Accounts::select('id')->get();

        foreach($accounts as $account) {
            $account_id = $account->id;

            $global_leopards_settings = Config::get('setup.leopards_settings');
            $sort_number = 0;
            foreach($global_leopards_settings as $leopards_setting) {
                $leopards_record = LeopardsSettings::where([
                    'account_id' => $account_id,
                    'slug' => $leopards_setting['slug'],
                ])->select('id')->first();

                if(!$leopards_record) {
                    $data = null;
                    if($leopards_setting['slug'] == 'auto-fulfillment') {
                        $data = 0;
                    } else if($leopards_setting['slug'] == 'inventory-location') {
                        $location = ShopifyLocations::where([
                            'account_id' => $account_id
                        ])->first();
                        if($location) {
                            $data = $location->location_id;
                        }
                    }

                    // Set Account ID
                    LeopardsSettings::create([
                        'name' => $leopards_setting['name'],
                        'slug' => $leopards_setting['slug'],
                        'data' => $data,
                        'sort_number'=> $sort_number++,
                        'account_id' => $account_id,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ]);
                }
            }
        }

        echo 'All Done';
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function instructions()
    {
        $leopards_settings = LeopardsSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');

        return view('instructions', compact('leopards_settings'));
    }

    public function runQueue() {
        event(new SyncProductsFire(Accounts::find(Auth::User()->account_id)));
        echo 'Sync Products Event is dispatched';
    }

    public function runVariantsQueue() {
        event(new UploadVariantsFire(Accounts::find(Auth::User()->account_id)));
        echo 'Upload Variants Event is dispatched';
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearProcesses() {
        HeavyLifter::where(array(
            'account_id' => Auth::User()->account_id
        ))->forceDelete();

        ShopifyJobs::where(array(
            'account_id' => Auth::User()->account_id,
            'is_processing' => 0,
        ))->forceDelete();

        flash('Quese is flushed successfully.')->success()->important();
        return redirect()->route('admin.home');
    }
}
