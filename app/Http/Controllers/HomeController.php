<?php

namespace App\Http\Controllers;

use App\Events\Shopify\Products\SyncProductsFire;
use App\Events\Shopify\Products\UploadVariantsFire;
use App\Models\Accounts;
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
