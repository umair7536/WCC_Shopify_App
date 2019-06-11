<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Schema;


class ShopifyProductVariants extends BaseModal
{

    protected $fillable = [
        'variant_id', 'admin_graphql_api_id', 'title', 'barcode', 'sku', 'compare_at_price', 'price',
        'fulfillment_service', 'grams', 'inventory_item_id', 'inventory_management', 'inventory_policy',
        'inventory_quantity', 'old_inventory_quantity', 'inventory_quantity_adjustment', 'metafields', 'presentment_prices',
        'position', 'requires_shipping', 'taxable', 'tax_code', 'weight', 'weight_unit',
        'image_id', 'product_id', 'created_at', 'updated_at', 'account_id'
    ];

    protected static $skip_columns = [];

    protected static $_fillable = [
        'variant_id', 'admin_graphql_api_id', 'title', 'barcode', 'sku', 'compare_at_price', 'price',
        'fulfillment_service', 'grams', 'inventory_item_id', 'inventory_management', 'inventory_policy',
        'inventory_quantity', 'old_inventory_quantity', 'inventory_quantity_adjustment', 'metafields', 'presentment_prices',
        'position', 'requires_shipping', 'taxable', 'tax_code', 'weight', 'weight_unit',
        'image_id', 'product_id', 'created_at', 'updated_at', 'account_id'
    ];

    protected $table = 'shopify_product_variants';

    protected static $_table = 'shopify_product_variants';

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
    static public function getAllRecordsDictionary($account_id)
    {
        return self::where(['account_id' => $account_id])->get()->getDictionary();
    }

    /**
     * Create Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function createRecord($request, $account_id)
    {

        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;

        $record = self::create($data);

        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

        return $record;
    }

    /**
     * Delete Record
     *
     * @param $id
     *
     * @return (mixed)
     */
    static public function deleteRecord($id)
    {

        $variant = ShopifyProductVariants::getData($id);

        if (!$variant) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.variants.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (ShopifyProductVariants::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.variants.index');
        }

        $record = $variant->delete();

        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;
    }

    /**
     * Update Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function updateRecord($id, $request, $account_id)
    {
        $old_data = (ShopifyProductVariants::find($id))->toArray();

        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if(!$record) {
            return null;
        }

        $record->update($data);

        AuditTrails::EditEventLogger(self::$_table, 'edit', $data, self::$_fillable, $old_data, $id);

        return $record;
    }

    /**
     * Check if child records exist
     *
     * @param (int) $id
     * @param
     *
     * @return (boolean)
     */
    static public function isChildExists($id, $account_id)
    {
        return false;
    }

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
            $timestamps = ['created_at', 'updated_at'];
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
