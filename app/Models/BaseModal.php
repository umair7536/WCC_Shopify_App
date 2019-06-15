<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use ZfrShopify\ShopifyClient;

class BaseModal extends Model
{

    /**
     * Get Data
     *
     * @param (int) $id
     *
     * @return (mixed)
     */
    static public function getData($id) {

        return self::where([
            ['id','=',$id],
            ['account_id','=', Auth::User()->account_id]
        ])->first();
    }

    /*
     * Get Bulk Data
     *
     * @param (int)|(array) $id
     *
     * @return (mixed)
     */
    static public function getBulkData($id) {
        if(!is_array($id)) {
            $id = array($id);
        }
        return self::where([
            ['account_id','=', Auth::User()->account_id]
        ])->whereIn('id', $id)
            ->get();
    }

    /*
     * Get Bulk Data for appointment images
     *
     * @param (int)|(array) $id
     *
     * @return (mixed)
     */
    static public function getBulkData_forimage($id)
    {
        if (!is_array($id)) {
            $id = array($id);
        }
        return self::whereIn('id', $id)->get();
    }

    protected static function getShopifyObject() {
        $shop = ShopifyShops::where(array(
            'account_id' => Auth::User()->account_id
        ))->first();

        return new ShopifyClient([
            'private_app' => false,
            'api_key' => env("SHOPIFY_APP_API_KEY"), // In public app, this is the app ID
            'version' => env('SHOPIFY_API_VERSION'), // Put API Version
            'access_token' => $shop->access_token,
            'shop' => $shop->domain
        ]);
    }

}
