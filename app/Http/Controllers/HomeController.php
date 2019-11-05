<?php

namespace App\Http\Controllers;

use App\Events\Shopify\Products\SyncProductsFire;
use App\Events\Shopify\Products\UploadVariantsFire;
use App\Models\Accounts;
use App\Models\HeavyLifter;
use App\Models\ShopifyJobs;
use Auth;
use Config;

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

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearProcesses() {
        HeavyLifter::where(array(
            'account_id' => Auth::User()->account_id
        ))->forceDelete();

        ShopifyJobs::where(array(
            'account_id' => Auth::User()->account_id
        ))->forceDelete();

        flash('Quese is flushed successfully.')->success()->important();
        return redirect()->route('admin.home');
    }
}
