<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;


class ShopifyBillings extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'account_id', 'charge_id', 'name', 'api_client_id', 'price', 'status', 'return_url',
        'billing_on', 'test', 'activated_on', 'cancelled_on', 'trial_days', 'trial_ends_on', 'decorated_return_url'.
        'confirmation_url', 'created_at', 'updated_at'
    ];

    protected static $_fillable = [
        'charge_id', 'name', 'api_client_id', 'price', 'status', 'return_url',
        'billing_on', 'test', 'activated_on', 'cancelled_on', 'trial_days', 'trial_ends_on', 'decorated_return_url'.
        'confirmation_url', 'created_at', 'updated_at'
    ];

    protected $table = 'shopify_billings';

    protected static $_table = 'shopify_billings';

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

        if($request->get('name')) {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
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

        if($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
        }

        if($request->get('name')) {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
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
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->get();
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

        $record = self::create($data);

        $record->update(['sort_no' => $record->id]);

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

        $shopify_billing = ShopifyBillings::getData($id);

        if (!$shopify_billing) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

        $record = $shopify_billing->update(['active' => 0]);

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

        $shopify_billing = ShopifyBillings::getData($id);

        if (!$shopify_billing) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

        $record = $shopify_billing->update(['active' => 1]);

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

        $shopify_billing = ShopifyBillings::getData($id);

        if (!$shopify_billing) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (ShopifyBillings::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.shopify_billings.index');
        }

        $record = $shopify_billing->delete();

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
        $old_data = (ShopifyBillings::find($id))->toArray();

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

    static public function getShopifyBillings($excludeIds = false)
    {
        $where = [
            ['account_id', '=', Auth::User()->account_id],
            ['active', '=', '1'],
        ];

        if($excludeIds && !is_array($excludeIds)) {
            $excludeIds = array($excludeIds);
        } else {
            $excludeIds = [];
        }

        if(count($excludeIds)) {
            return self::where($where)->whereNotIn('id', $excludeIds)->OrderBy('sort_number', 'asc')->get()->pluck('name', 'id');
        } else {
            return self::where($where)->OrderBy('sort_number', 'asc')->get()->pluck('name', 'id');
        }
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
}
