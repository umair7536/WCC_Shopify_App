<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Schema;


class ShopifyProductTags extends BaseModal
{

    /*
     * Disable timestamps
     */
    public $timestamps = false;

    protected $fillable = [
        'tag_id', 'product_id', 'account_id', 'created_at', 'updated_at'
    ];

    protected static $skip_columns = [];

    protected static $_fillable = [
        'tag_id', 'product_id', 'account_id'
    ];

    protected $table = 'shopify_product_tags';

    protected static $_table = 'shopify_product_tags';

    /**
     * Get Charge per Units.
     */
    public function product()
    {
        return $this->belongsTo('App\Models\ShopifyProducts', 'product_id');
    }

    /**
     * Get All Records
     *
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getAllRecordsDictionary($account_id, $product_id = false)
    {
        if($product_id) {
            return self::where([
                'account_id' => $account_id,
                'product_id' => $product_id,
            ])->get()->getDictionary();
        }

        return self::where(['account_id' => $account_id])->get()->getDictionary();
    }
}
