<?php

namespace App\Http\Controllers\Admin;

use App\Events\Shopify\Locations\SyncLocationsFire;
use App\Events\Shopify\Orders\SyncOrdersFire;
use App\Events\Shopify\Products\SyncCollectsFire;
use App\Events\Shopify\Products\SyncCustomCollecionsFire;
use App\Events\Shopify\Products\SyncCustomersFire;
use App\Events\Shopify\Products\SyncProductsFire;
use App\Events\Shopify\Webhooks\CreateWebhooksFire;
use App\Helpers\ShopifyHelper;
use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\GeneralSettings;
use App\Models\HeavyLifter;
// use App\Models\LeopardsSettings;
use App\Models\WccSettings;
use App\Models\ShopifyCollects;
use App\Models\ShopifyJobs;
use App\Models\ShopifyLocations;
use App\Models\ShopifyPlans;
use App\Models\ShopifyShops;
use App\Models\TicketStatuses;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use ZfrShopify\OAuth\AuthorizationRedirectResponse;
use GuzzleHttp\Client;
use ZfrShopify\OAuth\TokenExchanger;
use ZfrShopify\Exception\RuntimeException;
use ZfrShopify\ShopifyClient;
use Auth;
use Config;
use DB;
use Psr\Http\Message\ServerRequestInterface;
use ZfrShopify\Exception\InvalidRequestException;
use ZfrShopify\Validator\RequestValidator;

class ShopifyController extends Controller
{
    /*
     * Shopify App Api Key
     */
    protected static $APP_API_KEY;

    /*
     * Shopify App Shared Secret
     */
    protected static $APP_SHARED_SECRET;

    /*
     * Shopify App Default Store for testing
     */
    protected static $APP_DEFAULT_STORE;

    /*
     * Shopify App Scopes
     * reference URL: https://help.shopify.com/en/api/getting-started/authentication/oauth/scopes
     * write_content,write_themes,write_orders,write_products,read_product_listings,read_customers,write_draft_orders,write_checkouts,write_price_rules,write_script_tags
     */
    protected static $APP_SCOPES;


    /*
     * Shopify App Nounce
     */
    protected static $APP_NONCE;

    public function __construct()
    {
        try {
            self::$APP_API_KEY = env('SHOPIFY_APP_API_KEY');
            self::$APP_SHARED_SECRET = env('SHOPIFY_APP_SHARED_SECRET');
            self::$APP_DEFAULT_STORE = env('SHOPIFY_APP_DEFAULT_STORE');
            self::$APP_SCOPES = env('SHOPIFY_APP_SCOPES');
            self::$APP_NONCE = env('SHOPIFY_APP_NONCE');


//            $globalSettings = Settings::where(array(
//                'account_id' => 1
//            ))->get()->keyBy('slug');
//
//            if($globalSettings) {
//                $globalSettings = $globalSettings->toArray();
//            }
//            self::$APP_SHARED_SECRET = $globalSettings["sys-app-shared-secret"]->data;
//            self::$APP_DEFAULT_STORE = $globalSettings["sys-app-default-store"]->data;
//            self::$APP_SCOPES = $globalSettings["sys-app-scopes"]->data;

            if(
                !self::$APP_API_KEY ||
                !self::$APP_SHARED_SECRET ||
                !self::$APP_DEFAULT_STORE ||
                !self::$APP_SCOPES ||
                !self::$APP_NONCE
            ) {
                return Redirect::to(route('auth.login'))->withErrors(['Something went wrong with credentials.']);
            }

        } catch (\Exception $e) {
            return Redirect::to(route('auth.login'))->withErrors(['Something went wrong.']);
        }
    }

    public function index()
    {
        return view('shop');
    }

    public function install(Request $request)
    {
        if ($request->get('shop')) {


            $redirectionUri = env('APP_URL') . '/redirect';


            $response = new AuthorizationRedirectResponse(self::$APP_API_KEY, ($request->get('shop')) ? $request->get('shop') : self::$APP_DEFAULT_STORE, explode(',', self::$APP_SCOPES), $redirectionUri, self::$APP_NONCE);
            $location = $response->getHeader('location')[0];


            return redirect($location);
        } else {
//            return redirect('/login')->withErrors(['No Shopify Store url provided.']);
            return Redirect::back()->withErrors(['No Shopify Store url provided by you.!!']);
        }
    }

    public function redirect(ServerRequestInterface $request)
    {

        try {

            /*
             * Check if Access token is already set or not
             */
            // Understood this part (every user come here first time for shopify verification)
            if (!session('access_token') || !session('shopify_domain')) {    // This run when access token and shopify admin session not present
                try {


//                    ZfrShopify client provides an easy way to validate an incoming request to make sure it comes from Shopify through the RequestValidator object. It requires a PSR7 requests and a shared secret:
                    $validator = new RequestValidator();
                    $validator->validateRequest($request, self::$APP_SHARED_SECRET);

                    /*
                     * Received OAuth request response from shopify
                     */

                    $shopRequest = $request->getQueryParams();


                    // isset() Check whether a variable is empty. Also check whether the variable is set/declared:
                    $code = (isset($shopRequest['code']) && $shopRequest['code']) ? $shopRequest['code'] : '';  // it return null value if $shopRequest['code'] not present

                    //$shopRequest['shop'] (it is temp. token generate by shopify ) return app name (webie-app-store.myshopify.com)
                    $shopDomain = (isset($shopRequest['shop']) && $shopRequest['shop']) ? $shopRequest['shop'] : '';

                    //it execute when one or both values empty
                    if(!$code || !$shopDomain) {
                        return Redirect::to(route('auth.register'))->withErrors(['Authentication failed, Please try again.']);
                    }


                    try {
                        try {

                            $tokenExchanger = new TokenExchanger(new Client());
                            // this function exchange tokens and give new token by shopify
                            $accessToken = $tokenExchanger->exchangeCodeForToken(self::$APP_API_KEY, self::$APP_SHARED_SECRET, $shopDomain, explode(',', self::$APP_SCOPES), $code);
                            
        
//                            check token present or not
                            if ($accessToken) {
                                session(['access_token' => $accessToken]);
                                session(['shopify_domain' => $shopDomain]);
//                                if store present then it goes to 'shopify.redirect' route if not then it redirect to register page with error
                                return redirect()->to(route('shopify.redirect'));
                            } else {
                                return Redirect::to(route('auth.register'))->withErrors('Invalid Access Toekn, please start process again');
                            }
                        } catch (RuntimeException $e) {
                            return Redirect::to(route('auth.register'))->withErrors([$e->getMessage()]);
                        }
                    } catch (ClientException $e) {
                        return Redirect::to(route('auth.register'))->withErrors([$e->getMessage()]);
                    }

                } catch (InvalidRequestException $exception) {
                    return Redirect::to(route('auth.login'))->withErrors([$exception->getMessage()]);
                }
            }
            // Every user come this must if new then first if ->redirect function ->else part
            else {
                // create an shopify
                $shopifyClient = new ShopifyClient([
                    'private_app' => false,    // true for private & false for public app
                    'api_key' => self::$APP_API_KEY, // In public app, this is the app ID
                    'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                    'access_token' => session('access_token'),
                    'shop' => session('shopify_domain')
                ]);

                $shopDomain = $shopifyClient->getShop();     // this return data related to app that present on shopify

                // it execute when  shopify id and store present
                if (isset($shopDomain['id']) && isset($shopDomain['myshopify_domain'])) {

                    /*
                     * Register User
                     */

                    // it check in database that shopify store present or not
                    $shop = ShopifyShops::where(['myshopify_domain' => $shopDomain['myshopify_domain']])->first();
                    // if store in database then this update the access token and installed in DB
                    if($shop) {
                        // Shop found just redirect at login page for login
                        $shop->update(array(
                            'access_token' => session('access_token'),
                            'installed' => 1
                        ));


                        $this->setupAccount($shop->account_id);

                        $user = User::where(array(
                            'account_id' => $shop->account_id,
                            'main_account' => 1,
                        ))->first();

                        // Unset session variables
                        session()->forget('access_token');
                        session()->forget('shopify_domain');

                        if($user) {
                            Auth::login($user);

                            return Redirect::to(route('auth.login'));
                        } else {
                            return Redirect::to(route('auth.register'))->withErrors(['Something went wrong, Please try again.']);
                        }
                    } else {
                        // Register this shop
                        $account = Accounts::create(array(
                            'name' => $shopDomain['name'],
                            'email' => $shopDomain['email'],
                            'contact' => $shopDomain['phone'],
                            'resource_person' => $shopDomain['shop_owner'],
                            'created_at' => Carbon::parse(Carbon::now())->toDateTimeString(),
                            'updated_at' => Carbon::parse(Carbon::now())->toDateTimeString(),
                        ));

                        $shop_data = array(
                            'access_token' => session('access_token'),
                            'store_id' => $shopDomain['id'],
                            'domain' => $shopDomain['domain'],
                            'myshopify_domain' => $shopDomain['myshopify_domain'],
                            'name' => $shopDomain['name'],
                            'shop_owner' => $shopDomain['shop_owner'],
                            'phone' => $shopDomain['phone'],
                            'email' => $shopDomain['email'],
                            'customer_email' => $shopDomain['customer_email'],
                            'timezone' => $shopDomain['timezone'],
                            'iana_timezone' => $shopDomain['iana_timezone'],
                            'account_id' => $account->id,
                        );

                        /**
                         * Grab Free Plan and assign this to User
                         */
                        $plan_data = ShopifyHelper::getFreePlan($account->id);
                        if($plan_data['status']) {
                            $shop_data['plan_id'] = $plan_data['plan_id'];
                            $shop_data['activated_on'] = $plan_data['activated_on'];
                            $shop_data['shopify_billing_id'] = $plan_data['shopify_billing_id'];
                        }

                        $shop = ShopifyShops::create($shop_data);

                        $user = User::create(array(
                            'name' => $shopDomain['shop_owner'],
                            'email' => $shopDomain['email'],
                            'phone' => $shopDomain['phone'],
                            'user_type_id' => Config::get('constants.application_user_id'),
                            'account_id' => $account->id,
                            'main_account' => 1,
                            'password' => Hash::make($shopDomain['id'] . $shopDomain['domain']),
                            'created_at' => Carbon::parse(Carbon::now())->toDateTimeString(),
                            'updated_at' => Carbon::parse(Carbon::now())->toDateTimeString(),
                        ));

                        // Assign CSR to this user
                        $user->syncRoles(array(Config::get('constants.role_application_user')));

                        $this->setupAccount($account->id);

                        // Unset session variables
                        session()->forget('access_token');
                        session()->forget('shopify_domain');
                    }

                    Auth::login($user);

                    /*
                     * Set Session for this user
                     */
                    $account_id=Auth::User()->account_id;
                    session(['account_id' => $account_id]);
                    $account= DB::table('accounts')->find($account_id);
                    session(['account' => $account]);

                    return Redirect::to(route('auth.login'));
                }
            }
        } catch (RuntimeException $e) {
            return Redirect::back()->withErrors([$e->getMessage()]);
        }
    }

    /*
     * Verify incoming shopify request to automatically login
     *
     * @param: Illuminate\Http\Request $request
     * @return: Illuminate\Http\Response $response
     */
    public function verifyShopify(ServerRequestInterface $request) {

        $shopRequest = $request->getQueryParams();

        if(
                (isset($shopRequest['shop']) && $shopRequest['shop'])
            &&  (isset($shopRequest['hmac']) && $shopRequest['hmac'])
        ) {

            try {
                $validator = new RequestValidator();
                $validator->validateRequest($request, self::$APP_SHARED_SECRET);

                if($shopRequest['shop'] && $shopRequest['hmac']) {
                    $shop = ShopifyShops::where(['myshopify_domain' => $shopRequest['shop']])->first();
                    if($shop) {

                        /**
                         * Check if App Installed is '0' then reinstall this app
                         */
                        if(!$shop->installed) {
                            return redirect()->route('shopify.install', [
                                'shop' => $shopRequest['shop']
                            ]);
                        } else {
                            /**
                             * If Shop data is not provided then again reinstall this app
                             */
                            $shopifyClient = new ShopifyClient([
                                'private_app' => false,
                                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                                'access_token' => $shop->access_token,
                                'shop' => $shop->myshopify_domain
                            ]);

                            try {
                                $shop_live = $total_records = $shopifyClient->getShop();
                            } catch (\Exception $exception) {
                                $message = $exception->getMessage();
                                if(is_string($message) && strpos($message, '401 Unauthorized') !== false) {
                                    return redirect()->route('shopify.install', [
                                        'shop' => $shopRequest['shop']
                                    ]);
                                }
                            }
                        }

                        $user = User::where(array(
                            'account_id' => $shop->account_id,
                            'main_account' => 1,
                        ))->first();

                        if ($user) {
                            Auth::login($user);

                            /*
                             * Set Session for this user
                             */
                            $account_id=Auth::User()->account_id;
                            session(['account_id' => $account_id]);
                            $account= DB::table('accounts')->find($account_id);
                            session(['account' => $account]);

                            return Redirect::to(route('auth.login'));
                        } else {
                            return Redirect::to(route('auth.register'))->withErrors(['Something went wrong, Please try again.']);
                        }
                    } else {
                        return redirect()->route('shopify.install', [
                            'shop' => $shopRequest['shop']
                        ]);
                    }
                } else {
                    return Redirect::to(route('auth.login'))->withErrors(['Invalid request received, Please try again.']);
                }
            } catch (InvalidRequestException $exception) {
                return Redirect::to(route('auth.login'))->withErrors([$exception->getMessage()]);
            }

        } else {
            if(isset($shopRequest['shop']) && $shopRequest['shop']) {

                $redirectionUri = env('APP_URL') . '/redirect';

                $response = new AuthorizationRedirectResponse(self::$APP_API_KEY, $shopRequest['shop'], explode(',', self::$APP_SCOPES), $redirectionUri, self::$APP_NONCE);
                $location = $response->getHeader('location')[0];

                return redirect($location);
            } else {
                return redirect('/login')->withErrors(['No Shopify Store url provided by you!!.']);
//                return Redirect::back()->withErrors(['No Shopify Store url provided.']);
            }
        }
    }

    /*
     * Setup basic table settings for each account
     *
     * @param: (int) $account_id
     *
     * @return: boolean true|false
     */
    private function setupAccount($account_id) {

        /**
         * Flush any Queue process
         */
        HeavyLifter::where(array(
            'account_id' => $account_id
        ))->forceDelete();

        ShopifyJobs::where(array(
            'account_id' => $account_id
        ))->forceDelete();     // soft delete for temp. delete move to trash , Forcedelete permanent delete

        /**
         * Dispatch Events
         */
        $account = Accounts::find($account_id);


//        event(new SyncProductsFire($account));
        event(new SyncLocationsFire($account));
        event(new SyncCustomersFire($account));
        event(new SyncOrdersFire($account));
        event(new CreateWebhooksFire($account));
//        event(new SyncCustomCollecionsFire($account));
        /**
         * Dispatch Collects Event and Delte existing records
         */
//        ShopifyCollects::where([
//            'account_id' => $account_id
//        ])->forceDelete();
//        event(new SyncCollectsFire($account));

        /**
         * Check if Ticket Statuses exists or not
         */
        $statuses = TicketStatuses::where([
            'account_id' => $account_id
        ])->get();




        if(!$statuses || !$statuses->count()) {
            $global_ticket_statuses = Config::get('setup.ticket_statuses');

            $ticket_statuses = [];
            $sort_number = 0;
            foreach($global_ticket_statuses as $ticket_statuse) {
                $ticket_statuses[] = array(
                    'name' => $ticket_statuse['name'],
                    'slug' => $ticket_statuse['slug'],
                    'sort_number'=> $sort_number++,
                    'account_id' => $account_id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                );
            }

            TicketStatuses::insert($ticket_statuses);
        }

        /**
         * General Settings
         */
        $global_general_settings = Config::get('setup.general_settings');

        $settings = GeneralSettings::where([
            'account_id' => $account_id,
        ])->get();

        if(!$settings || !$settings->count()) {
            $general_settings = [];
            $sort_number = 0;
            foreach($global_general_settings as $general_setting) {
                $general_settings[] = array(
                    'name' => $general_setting['name'],
                    'slug' => $general_setting['slug'],
                    'data' => null,
                    'sort_number'=> $sort_number++,
                    'account_id' => $account_id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                );
            }

            GeneralSettings::insert($general_settings);
        }

        /**
         * LCS Settings
         */
        $global_wcc_settings = Config::get('setup.wcc_settings');
        $sort_number = 0;
        foreach($global_wcc_settings as $wcc_setting) {
            $wcc_record = WccSettings::where([
                'account_id' => $account_id,
                'slug' => $wcc_setting['slug'],
            ])->select('id')->first();

            if(!$wcc_record) {
                $data = null;
                if($wcc_setting['slug'] == 'auto-fulfillment') {
                    $data = 0;
                } else if($wcc_setting['slug'] == 'inventory-location') {
                    $location = ShopifyLocations::where([
                        'account_id' => $account_id
                    ])->first();
                    if($location) {
                        $data = $location->location_id;
                    }
                } else if($wcc_setting['slug'] == 'shipper-type') {
                    $data = 'other';
                }

                // Set Account ID
                WccSettings::create([
                    'name' => $wcc_setting['name'],
                    'slug' => $wcc_setting['slug'],
                    'data' => $data,
                    'sort_number'=> $sort_number++,
                    'account_id' => $account_id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
            }
        }

        return $account_id;
    }

}
