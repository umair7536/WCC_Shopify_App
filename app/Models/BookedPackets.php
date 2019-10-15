<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Schema;


class BookedPackets extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'booked_packet_id', 'shipment_type_id', 'booking_date', 'packet_pieces', 'net_weight', 'collect_amount', 'order_id',
        'vol_weight_w', 'vol_weight_h', 'vol_weight_l', 'shipper_id', 'shipper_name', 'shipper_email', 'shipper_phone', 'shipper_address',
        'consignee_id', 'consignee_name', 'consignee_email', 'consignee_phone', 'consignee_phone_2', 'consignee_phone_3', 'consignee_address',
        'comments', 'track_number', 'slip_link', 'status', 'history', 'origin_city', 'destination_city', 'cn_number',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'account_id'
    ];

    protected static $skip_columns = ['variants', 'options', 'images'];

    protected static $_fillable = [
        'booked_packet_id', 'shipment_type_id', 'booking_date', 'packet_pieces', 'net_weight', 'collect_amount', 'order_id',
        'vol_weight_w', 'vol_weight_h', 'vol_weight_l', 'shipper_id', 'shipper_name', 'shipper_email', 'shipper_phone', 'shipper_address',
        'consignee_id', 'consignee_name', 'consignee_email', 'consignee_phone', 'consignee_phone_2', 'consignee_phone_3', 'consignee_address',
        'comments', 'track_number', 'slip_link', 'status', 'history', 'origin_city', 'destination_city', 'cn_number',
    ];

    protected $table = 'booked_packets';

    protected static $_table = 'booked_packets';

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

        if($request->get('product_type')) {
            $where[] = array(
                'product_type',
                'like',
                '%' . $request->get('product_type') . '%'
            );
        }

        if($request->get('vendor')) {
            $where[] = array(
                'vendor',
                'like',
                '%' . $request->get('vendor') . '%'
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

        $orderBy = 'title';
        $order = 'asc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

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

        if($request->get('product_type')) {
            $where[] = array(
                'product_type',
                'like',
                '%' . $request->get('product_type') . '%'
            );
        }

        if($request->get('vendor')) {
            $where[] = array(
                'vendor',
                'like',
                '%' . $request->get('vendor') . '%'
            );
        }

        if(count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
        }
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
     * Inactive Record
     *
     * @param $id
     *
     * @return (mixed)
     */
    static public function inactiveRecord($id)
    {

        $product = ShopifyProducts::getData($id);

        if (!$product) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.products.index');
        }

        $record = $product->update(['active' => 0]);

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

        $product = ShopifyProducts::getData($id);

        if (!$product) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.products.index');
        }

        $record = $product->update(['active' => 1]);

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

        $product = ShopifyProducts::getData($id);

        if (!$product) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.products.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (ShopifyProducts::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.products.index');
        }

        $record = $product->delete();

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
        $old_data = (ShopifyProducts::find($id))->toArray();

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

            if($column == 'image' && isset($record[$column]['src'])) {
                $prepared_record['image_src'] = $record[$column]['src'];
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
