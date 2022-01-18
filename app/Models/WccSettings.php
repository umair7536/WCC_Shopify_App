<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;


use Illuminate\Database\Eloquent\Model;

class WccSettings extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['account_id', 'slug', 'name', 'data', 'active', 'created_at', 'updated_at', 'sort_number'];

    protected static $_fillable = ['name', 'slug', 'data', 'active'];

    protected $table = 'wcc_settings';

    protected static $_table = 'wcc_settings';

    /**
     * Create Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function createRecord($request, $account_id)
    {
        /**
         * Updat All Records
         */
        $wcc_settings = WccSettings::where([
            'account_id' => $account_id
        ])
            ->orderBy('id', 'asc')
            ->get();

        if ($wcc_settings) {
            $data = $request->all();

            /**
             * If 'shipper-type' is 'self' then empty shipper information
             */
            if ($data['shipper-type'] == 'self') {
                $data['shipper-name'] = null;
                $data['shipper-email'] = null;
                $data['shipper-phone'] = null;
                $data['shipper-address'] = null;
                $data['shipper-city'] = null;
            }

            foreach ($wcc_settings as $wcc_setting) {
                if (array_key_exists($wcc_setting->slug, $data)) {
                    self::where([
                        'account_id' => $account_id,
                        'slug' => $wcc_setting->slug,
                    ])->update([
                        'data' => $data[$wcc_setting->slug]
                    ]);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Default Inventory Location
     *
     * @param $accont_id
     * @return boolean|null
     */
    static public function getDefaultInventoryLocation($accont_id)
    {
        $inventory_location = null;

        $inventory_record = self::where([
            'account_id' => $accont_id,
            'slug' => 'inventory-location',
        ])->select('id', 'data')->first();
        if ($inventory_record) {
            $inventory_location = $inventory_record->data;
        }

        return $inventory_location;
    }

    /**
     * check Auto Fulfillment Status
     *
     * @param $accont_id
     * @return boolean true|false
     */
    static public function isAutoFulfillmentEnabled($accont_id)
    {
        $enabled = false;

        $record = self::where([
            'account_id' => $accont_id,
            'slug' => 'auto-fulfillment',
        ])->select('id', 'data')->first();
        if ($record) {
            $enabled = ($record->data == '1') ? true : false;
        }

        return $enabled;
    }

    /**
     * check Auto Mark Paid Status
     *
     * @param $accont_id
     * @return boolean true|false
     */
    static public function isAutoMarkPaidEnabled($accont_id)
    {
        $enabled = false;

        $record = self::where([
            'account_id' => $accont_id,
            'slug' => 'auto-mark-paid',
        ])->select('id', 'data')->first();
        if ($record) {
            $enabled = ($record->data == '1') ? true : false;
        }

        return $enabled;
    }
}