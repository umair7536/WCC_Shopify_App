<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;


class ShopifyPlans extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['account_id', 'slug', 'name', 'price', 'quota', 'active', 'created_at', 'updated_at', 'sort_number'];

    protected static $_fillable = ['name', 'slug', 'price', 'quota', 'active'];

    protected $table = 'shopify_plans';

    protected static $_table = 'shopify_plans';

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
                return self::whereIn('id', $id)->whereNotIn('slug', $skip_slug)->where(['account_id' => $account_id])->get()->pluck('name','id');
            }

            return self::whereIn('id', $id)->where(['account_id' => $account_id])->get()->pluck('name','id');
        } else {
            if(is_array($skip_slug) && count($skip_slug)) {
                return self::where(['account_id' => $account_id, 'active' => 1])->whereNotIn('slug', $skip_slug)->get()->pluck('name','id');
            }

            return self::where(['account_id' => $account_id, 'active' => 1])->get()->pluck('name','id');
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
        return $query->OrderBy('sort_number','asc')->get();
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

        $shopify_plan = ShopifyPlans::getData($id);

        if (!$shopify_plan) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_plans.index');
        }

        $record = $shopify_plan->update(['active' => 0]);

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

        $shopify_plan = ShopifyPlans::getData($id);

        if (!$shopify_plan) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_plans.index');
        }

        $record = $shopify_plan->update(['active' => 1]);

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

        $shopify_plan = ShopifyPlans::getData($id);

        if (!$shopify_plan) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_plans.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (ShopifyPlans::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.shopify_plans.index');
        }

        $record = $shopify_plan->delete();

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
        $old_data = (ShopifyPlans::find($id))->toArray();

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

    static public function getShopifyPlans($excludeIds = false)
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
