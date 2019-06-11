<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Schema;


class ShopifyProducts extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'title', 'body_html', 'handle', 'product_type', 'published_at', 'published_scope',
        'image', 'image_src', 'created_at', 'updated_at', 'published_scope', 'tags', 'account_id'
    ];

    protected static $skip_columns = ['variants', 'options', 'images'];

    protected static $_fillable = [
        'product_id', 'title', 'body_html', 'handle', 'product_type', 'published_at', 'published_scope',
        'image', 'image_src', 'created_at', 'updated_at', 'published_scope', 'tags'
    ];

    protected $table = 'shopify_products';

    protected static $_table = 'shopify_products';

    /**
     * Get Charge per Units.
     */
    public function shopify_product_variants()
    {
        return $this->hasMany('App\Models\ShopifyProductVariants', 'product_id');
    }

    /**
     * Get Charge per Units.
     */
    public function shopify_product_tags()
    {
        return $this->hasMany('App\Models\ShopifyProductTags', 'product_id');
    }

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
        return $query->OrderBy('name','asc')->get();
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
