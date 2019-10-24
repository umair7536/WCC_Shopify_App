<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;


class ShopifyWebhooks extends BaseModal
{
    use SoftDeletes;

    private $shopify;

    protected $fillable = ['webhook_id', 'address', 'topic', 'format', 'fields', 'metafield_namespaces', 'created_at', 'updated_at', 'account_id'];

    protected static $_fillable = ['webhook_id', 'address', 'topic', 'format', 'fields', 'metafield_namespaces'];

    protected $table = 'shopify_webhooks';

    protected static $_table = 'shopify_webhooks';

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

        if($request->get('address')) {
            $where[] = array(
                'address',
                'like',
                '%' . $request->get('address') . '%'
            );
        }

        if($request->get('topic')) {
            $where[] = array(
                'topic',
                'like',
                '%' . $request->get('topic') . '%'
            );
        }

        if($request->get('format')) {
            $where[] = array(
                'format',
                'like',
                '%' . $request->get('format') . '%'
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

        if($request->get('address')) {
            $where[] = array(
                'address',
                'like',
                '%' . $request->get('address') . '%'
            );
        }

        if($request->get('topic')) {
            $where[] = array(
                'topic',
                'like',
                '%' . $request->get('topic') . '%'
            );
        }

        if($request->get('format')) {
            $where[] = array(
                'format',
                'like',
                '%' . $request->get('format') . '%'
            );
        }

        if($request->get('address')) {
            $where[] = array(
                'address',
                'like',
                '%' . $request->get('address') . '%'
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

        try {

            $data = $request->all();

            $shopify = self::getShopifyObject();

            $webhook = $shopify->createWebhook(array(
               'address' => $data['address'],
               'format' => $data['format'],
               'topic' => $data['topic'],
            ));

            if(count($webhook)) {
                // Set Account ID
                $data['account_id'] = $account_id;
                $data['fields'] = json_encode($webhook['fields']);
                $data['metafield_namespaces'] = json_encode($webhook['metafield_namespaces']);
                $data['created_at'] = Carbon::parse($webhook['created_at'])->toDateTimeString();
                $data['updated_at'] = Carbon::parse($webhook['updated_at'])->toDateTimeString();
                $data['webhook_id'] = $webhook['id'];
                $data['address'] = $webhook['address'];
                $data['topic'] = $webhook['topic'];
                $data['format'] = $webhook['format'];
                unset($data['_token']);

                $record = ShopifyWebhooks::create($data);

                $record->update(['sort_no' => $record->id]);

                AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

                return $record;
            }

        } catch(\Exception $exception) {
            echo $exception->getMessage(); exit;
            return null;
        }
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

        $shopify_webhook = ShopifyWebhooks::getData($id);

        if (!$shopify_webhook) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_webhooks.index');
        }

        $record = $shopify_webhook->update(['active' => 0]);

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

        $shopify_webhook = ShopifyWebhooks::getData($id);

        if (!$shopify_webhook) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_webhooks.index');
        }

        $record = $shopify_webhook->update(['active' => 1]);

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

        $shopify_webhook = ShopifyWebhooks::getData($id);

        if (!$shopify_webhook) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.shopify_webhooks.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (ShopifyWebhooks::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.shopify_webhooks.index');
        }

        try {
            $shopify = self::getShopifyObject();
            $shopify->deleteWebhook(array(
                'id' => (int) $shopify_webhook->webhook_id
            ));

            $shopify_webhook->forcedelete();

            flash('Record has been deleted successfully.')->success()->important();
            return true;
        } catch (\Exception $exception) {
            flash($exception->getMessage())->success()->important();
            return false;
        }
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
        $old_data = (ShopifyWebhooks::find($id))->toArray();

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

        try {
            $shopify = self::getShopifyObject();

            $webhook = $shopify->updateWebhook(array(
                'id' => (int) $record->webhook_id,
                'address' => $data['address'],
                'format' => $data['format'],
                'topic' => $data['topic'],
            ));
        } catch (\Exception $exception) {
            return false;
        }

        if(!count($webhook)) {
            return null;
        }

        $data['fields'] = json_encode($webhook['fields']);
        $data['metafield_namespaces'] = json_encode($webhook['metafield_namespaces']);
        $data['created_at'] = Carbon::parse($webhook['created_at'])->toDateTimeString();
        $data['updated_at'] = Carbon::parse($webhook['updated_at'])->toDateTimeString();
        $data['address'] = $webhook['address'];
        $data['topic'] = $webhook['topic'];
        $data['format'] = $webhook['format'];

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

    /**
     * Sync Data from Shopify
     *
     * @param int $account_id
     *
     * @return (boolean)
     */
    static public function syncData($account_id)
    {
        try {
            $shopify = self::getShopifyObject();
            $data = $shopify->getWebhooks();

            self::where(['account_id' => $account_id])->forcedelete();

            $records = [];

            if(count($data)) {
                foreach($data as $single_data) {
                    $single_data['webhook_id'] = $single_data['id'];
                    unset($single_data['id']);
                    $single_data['account_id'] = $account_id;
                    $single_data['fields'] = json_encode($single_data['fields']);
                    $single_data['metafield_namespaces'] = json_encode($single_data['metafield_namespaces']);
                    $single_data['created_at'] = Carbon::parse($single_data['created_at'])->toDateTimeString();
                    $single_data['updated_at'] = Carbon::parse($single_data['updated_at'])->toDateTimeString();

                    self::updateOrCreate(
                        ['webhook_id' => $single_data['webhook_id']],
                        $single_data
                    );
                }
            }
        } catch (\Exception $exception) {

            return false;
        }

        return true;
    }
}
