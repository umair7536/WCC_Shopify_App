<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;


class Shippers extends BaseModal
{
    use SoftDeletes;

    private $shopify;

    protected $fillable = ['shipper_id', 'name', 'email', 'phone', 'address', 'active', 'account_id', 'city_id', 'created_at', 'updated_at'];

    protected static $_fillable = ['shipper_id', 'name', 'email', 'phone', 'address', 'active', 'city_id'];

    protected $table = 'shippers';

    protected static $_table = 'shippers';

    /**
     * Get City Detail
     */
    public function wcc_citie()
    {
        return $this->belongsTo('App\Models\WccCities', 'city_id');
    }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveSorted($id = false)
    {
        if($id && !is_array($id)) {
            $id = array($id);
        }
        if($id) {
            return self::whereIn('id', $id)->get()->pluck('name','id');
        } else {
            return self::get()->pluck('name','id');
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

        if($request->get('city_id')) {
            $where[] = array(
                'city_id',
                '=',
                $request->get('city_id')
            );
        }

        if($request->get('name')) {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
        }

        if($request->get('email')) {
            $where[] = array(
                'email',
                'like',
                '%' . $request->get('email') . '%'
            );
        }

        if($request->get('phone')) {
            $where[] = array(
                'phone',
                'like',
                '%' . $request->get('phone') . '%'
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

        if($request->get('city_id')) {
            $where[] = array(
                'city_id',
                '=',
                $request->get('city_id')
            );
        }

        if($request->get('name')) {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
        }

        if($request->get('email')) {
            $where[] = array(
                'email',
                'like',
                '%' . $request->get('email') . '%'
            );
        }

        if($request->get('phone')) {
            $where[] = array(
                'phone',
                'like',
                '%' . $request->get('phone') . '%'
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

        $record = Shippers::create($data);

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

        $shipper = Shippers::getData($id);

        if (!$shipper) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shippers.index');
        }

        $record = $shipper->update(['active' => 0]);

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

        $shipper = Shippers::getData($id);

        if (!$shipper) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shippers.index');
        }

        $record = $shipper->update(['active' => 1]);

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

        $shipper = Shippers::getData($id);

        if (!$shipper) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shippers.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (Shippers::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.shippers.index');
        }


        $shipper->delete();

        flash('Record has been deleted successfully.')->success()->important();
        return true;
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
        $old_data = (Shippers::find($id))->toArray();

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
}
