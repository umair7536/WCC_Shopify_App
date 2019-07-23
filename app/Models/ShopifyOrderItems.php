<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Auth;


class ShopifyOrderItems extends BaseModal
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'item_id', 'variant_id', 'product_id', 'admin_graphql_api_id', 'title', 'quantity','sku', 'variant_title',
        'vendor', 'fulfillment_service', 'requires_shipping', 'taxable', 'gift_card', 'name', 'variant_inventory_management','properties',
        'product_exists', 'fulfillable_quantity', 'grams', 'price', 'total_discount', 'fulfillment_status', 'price_set','total_discount_set',
        'discount_allocations', 'tax_lines', 'account_id'
    ];

    protected static $_fillable = [
        'order_id', 'item_id', 'variant_id', 'product_id', 'admin_graphql_api_id', 'title', 'quantity','sku', 'variant_title',
        'vendor', 'fulfillment_service', 'requires_shipping', 'taxable', 'gift_card', 'name', 'variant_inventory_management','properties',
        'product_exists', 'fulfillable_quantity', 'grams', 'price', 'total_discount', 'fulfillment_status', 'price_set','total_discount_set',
        'discount_allocations', 'tax_lines'
    ];

    protected $table = 'shopify_order_items';

    protected static $_table = 'shopify_order_items';

    protected static $skip_columns = [];

    /*
     * Prepare provided record
     */
    static public function prepareRecord($record) {

        $prepared_record = [];

        /*
         * Get table columns and prepare record
         */
        $columns = Schema::getColumnListing(self::$_table); // users table
        foreach($record as $column => $value) {
            // Skip those records which are in skipped columns array
            if(count(self::$skip_columns)) {
                if(in_array($column, self::$skip_columns)) {
                    continue;
                }
            }

            /*
             * Remove records which are not in columns
             */
            if(!in_array($column, $columns)) {
                continue;
            }

            /*
             * Set DateTimes format
             */
            $timestamps = ['created_at', 'updated_at', 'processed_at', 'closed_at'];
            if(in_array($column, $timestamps)) {
                $value = Carbon::parse($value)->toDateTimeString();
            }

            if(is_array($value)) {
                $prepared_record[$column] = json_encode($value);
            } else {
                $prepared_record[$column] = $value;
            }

        }

        return $prepared_record;
    }
}
