<?php

namespace App\Http\Controllers;

use App\Events\Leopards\BookedPackets\FullSyncPacketStatusFire;
use App\Events\Shopify\Orders\SingleOrderFulfillmentFire;
use App\Events\Shopify\Orders\SyncOrdersFire;
use App\Events\Shopify\Products\SyncProductsFire;
use App\Events\Shopify\Products\UploadVariantsFire;
use App\Events\Shopify\Webhooks\CreateWebhooksFire;
use App\Models\Accounts;
use App\Models\HeavyLifter;
use App\Models\LeopardsSettings;
use App\Models\ShopifyJobs;
use App\Models\ShopifyLocations;
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
                    if($leopards_setting['slug'] == 'auto-mark-paid') {
                        $data = 0;
                    } else if($leopards_setting['slug'] == 'auto-fulfillment') {
                        $data = 0;
                    } else if($leopards_setting['slug'] == 'inventory-location') {
                        $location = ShopifyLocations::where([
                            'account_id' => $account_id
                        ])->first();
                        if($location) {
                            $data = $location->location_id;
                        }
                    } else if($leopards_setting['slug'] == 'shipper-type') {
                        $data = 'self';
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

    /**
     * Our Apps section
     *
     * @return \Illuminate\Http\Response
     */
    public function ourApps()
    {
        return view('our_apps');
    }

    public function runQueue() {
//        event(new SyncProductsFire(Accounts::find(Auth::User()->account_id)));
//        echo 'Sync Products Event is dispatched';

        try {

            $accounts = Accounts::where([
                'suspended' => 0
            ])->get();

            if($accounts) {
                foreach ($accounts as $account) {
                    event(new CreateWebhooksFire($account));
                    event(new SyncOrdersFire($account));
                }
            }

            echo 'so far so good';
        } catch(\Exception $e) {
            echo "\n";
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }
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
