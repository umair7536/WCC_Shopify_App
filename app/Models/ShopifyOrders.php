<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Auth;


class ShopifyOrders extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'email', 'name', 'number', 'order_number', 'note', 'token','gateway', 'test',
        'total_price', 'subtotal_price', 'total_weight', 'total_tax', 'taxes_included', 'currency', 'financial_status',
        'confirmed', 'total_discounts', 'total_line_items_price', 'cart_token', 'buyer_accepts_marketing', 'referring_site', 'landing_site',
        'cancelled_at', 'cancel_reason', 'total_price_usd', 'checkout_token', 'reference', 'user_id', 'location_id',
        'source_identifier', 'source_url', 'device_id', 'phone', 'customer_locale', 'app_id', 'browser_ip', 'landing_site_ref',
        'discount_applications', 'discount_codes', 'note_attributes', 'payment_gateway_names', 'processing_method', 'checkout_id', 'source_name',
        'fulfillment_status', 'tax_lines', 'tags', 'contact_email', 'order_status_url', 'presentment_currency', 'total_line_items_price_set',
        'total_discounts_set', 'total_shipping_price_set', 'subtotal_price_set', 'total_price_set', 'total_tax_set', 'total_tip_received', 'admin_graphql_api_id',
        'shipping_lines', 'fulfillments', 'refunds', 'created_at', 'updated_at', 'account_id', 'processed_at', 'closed_at', 'customer_id'
    ];

    protected static $_fillable = [
        'order_id', 'email', 'name', 'number', 'order_number', 'note', 'token','gateway', 'test',
        'total_price', 'subtotal_price', 'total_weight', 'total_tax', 'taxes_included', 'currency', 'financial_status',
        'confirmed', 'total_discounts', 'total_line_items_price', 'cart_token', 'buyer_accepts_marketing', 'referring_site', 'landing_site',
        'cancelled_at', 'cancel_reason', 'total_price_usd', 'checkout_token', 'reference', 'user_id', 'location_id',
        'source_identifier', 'source_url', 'device_id', 'phone', 'customer_locale', 'app_id', 'browser_ip', 'landing_site_ref',
        'discount_applications', 'discount_codes', 'note_attributes', 'payment_gateway_names', 'processing_method', 'checkout_id', 'source_name',
        'fulfillment_status', 'tax_lines', 'tags', 'contact_email', 'order_status_url', 'presentment_currency', 'total_line_items_price_set',
        'total_discounts_set', 'total_shipping_price_set', 'subtotal_price_set', 'total_price_set', 'total_tax_set', 'total_tip_received', 'admin_graphql_api_id',
        'shipping_lines', 'fulfillments', 'refunds', 'created_at', 'updated_at', 'account_id', 'processed_at', 'closed_at', 'customer_id'
    ];

    protected $table = 'shopify_orders';

    protected static $_table = 'shopify_orders';

    public $timestamps = false;

    protected static $skip_columns = ['line_items', 'customer'];

    /**
     * Get active and sorted data only.
     *
     * @param: integer $id (optional)
     * @param: string $skip_slug (optional)
     * @param: integer $account_id Organizatioin ID
     *
     * @return: mixed
     */
    static public function getActiveSorted($id = false, $skip_slug = false, $account_id)
    {
        if($id && !is_array($id)) {
            $id = array($id);
        }
        if($skip_slug && !is_array($skip_slug)) {
            $skip_slug = array($skip_slug);
        }

        if($id) {
            if(is_array($skip_slug) && count($skip_slug)) {
                return self::whereIn('id', $id)->whereNotIn('slug', $skip_slug)->where(['account_id' => $account_id])->get()->pluck('title','id');
            }

            return self::whereIn('id', $id)->where(['account_id' => $account_id])->get()->pluck('title','id');
        } else {
            if(is_array($skip_slug) && count($skip_slug)) {
                return self::where(['account_id' => $account_id, 'active' => 1])->whereNotIn('slug', $skip_slug)->get()->pluck('title','id');
            }

            return self::where(['account_id' => $account_id, 'active' => 1])->get()->pluck('title','id');
        }
    }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveOnly($Id = false)
    {
        if($Id && !is_array($Id)) {
            $Id = array($Id);
        }
        $query = self::where(['active' => 1]);
        if($Id) {
            $query->whereIn('id',$Id);
        }
        return $query->OrderBy('title','asc')->get();
    }

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id = false)
    {
        $where = array();

        if($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
        }

        if($request->get('title')) {
            $where[] = array(
                'title',
                'like',
                '%' . $request->get('title') . '%'
            );
        }

        if($request->get('payment_type') != '') {
            $where[] = array(
                'payment_type',
                '=',
                $request->get('payment_type')
            );
        }

        if(count($where)) {
            return self::where($where)->count();
        } else {
            return self::count();
        }
    }

    /**
     * Get Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $iDisplayStart Start Index
     * @param (int) $iDisplayLength Total Records Length
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false)
    {
        $where = array();

        $orderBy = 'name';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        if($account_id) {
            $where[] = array(
                'shopify_orders.account_id',
                '=',
                $account_id
            );
        }

        if($request->get('name')) {
            $where[] = array(
                'shopify_orders.name',
                'like',
                '%' . $request->get('name') . '%'
            );
        }


        if($request->get('customer_name')) {
            $where[] = array(
                'shopify_customers.name',
                'like',
                '%' . $request->get('customer_name') . '%'
            );
        }


        if($request->get('customer_email')) {
            $where[] = array(
                'shopify_customers.email',
                'like',
                '%' . $request->get('customer_email') . '%'
            );
        }


        if($request->get('customer_phone')) {
            $where[] = array(
                'shopify_customers.phone',
                'like',
                '%' . $request->get('customer_phone') . '%'
            );
        }

        if(count($where)) {
            return self::join('shopify_customers','shopify_customers.customer_id', '=', 'shopify_orders.customer_id')
                ->where($where)
                ->limit($iDisplayLength)->offset($iDisplayStart)
                ->orderBy($orderBy,$order)
                ->select(
                    'shopify_customers.name as customer_name',
                    'shopify_customers.email as customer_email',
                    'shopify_customers.phone as customer_phone',
                    'shopify_orders.*'
                )
                ->get();
        } else {
            return self::limit($iDisplayLength)
                ->offset($iDisplayStart)
                ->orderBy($orderBy,$order)
                ->get();
        }
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
        $data['published_at'] = Carbon::now()->toDateTimeString();
        $data['updated_at'] = Carbon::now()->toDateTimeString();

        $record = self::create($data);

        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

        return $record;
    }

    /**
     * Inactive Record
     *
     * @param $id
     *
     * @return (mixed)
     */
    static public function inactiveRecord($id)
    {

        $order = ShopifyOrders::getData($id);

        if (!$order) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.orders.index');
        }

        $record = $order->update([
            'active' => 0,
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        flash('Record has been inactivated successfully.')->success()->important();

        AuditTrails::inactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

        return $record;
    }

    /**
     * active Record
     *
     * @param $id
     *
     * @return (mixed)
     */
    static public function activeRecord($id)
    {

        $order = ShopifyOrders::getData($id);

        if (!$order) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.orders.index');
        }

        $record = $order->update([
            'active' => 1,
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        flash('Record has been inactivated successfully.')->success()->important();

        AuditTrails::activeEventLogger(self::$_table, 'active', self::$_fillable, $id);

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

        $order = ShopifyOrders::getData($id);

        if (!$order) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.orders.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (ShopifyOrders::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.orders.index');
        }

        $record = $order->delete();

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
        $old_data = (ShopifyOrders::find($id))->toArray();

        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;
        $data['updated_at'] = Carbon::now()->toDateTimeString();

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