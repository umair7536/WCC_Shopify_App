<?php

namespace App\Http\Controllers;

use App\Events\Shopify\Products\SyncCustomersFire;
use App\Events\Shopify\Products\SyncProductsFire;
use App\Events\Shopify\Products\UploadVariantsFire;
use App\Models\Accounts;
use App\Models\ShopifyCollects;
use App\Models\ShopifyCustomCollections;
use App\Models\ShopifyCustomers;
use App\Models\ShopifyShops;
use Auth;
use Config;
use ZfrShopify\ShopifyClient;

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

        $shop = ShopifyShops::where([
            'account_id' => Auth::User()->account_id
        ])->first();

        if($shop) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                'access_token' => $shop->access_token,
                'shop' => $shop->myshopify_domain
            ]);

            $collects = $shopifyClient->getCollects();
//            dd($collects);

            if(count($collects)) {

                foreach ($collects as $collect) {
                    /*
                     * Prepare record before insert
                     */
                    $collect['collect_id'] = $collect['id'];
                    unset($collect['id']);
                    $collect_processed = ShopifyCollects::prepareRecord($collect);
                    $collect_processed['account_id'] = $shop['account_id'];

                    dd($collect_processed);

                    $collect_record = ShopifyCollects::where([
                        'collect_id' => $collect_processed['collect_id'],
                        'account_id' => $collect_processed['account_id'],
                    ])->select('id')->first();

                    if($collect_record) {
                        ShopifyCollects::where([
                            'collect_id' => $collect_processed['collect_id'],
                            'account_id' => $collect_processed['account_id'],
                        ])->update($collect_processed);
                    } else {
                        ShopifyCollects::create($collect_processed);
                    }
                }
            } else {
                echo 'No Custom Collection fetched' . "\n";
            }

            echo 'synced'; exit;
        }

        return view('home');
    }

    public function runQueue() {
        event(new SyncProductsFire(Accounts::find(Auth::User()->account_id)));
        echo 'Sync Products Event is dispatched';
    }

    public function runVariantsQueue() {
        event(new UploadVariantsFire(Accounts::find(Auth::User()->account_id)));
        echo 'Upload Variants Event is dispatched';
    }
}
