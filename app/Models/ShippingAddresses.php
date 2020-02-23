<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Auth;


class ShippingAddresses extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'first_name', 'last_name', 'name', 'phone',
        'company', 'latitude', 'longitude', 'country_code', 'province_code',
        'address1', 'address2', 'city', 'zip', 'province', 'country',
        'account_id', 'created_at', 'updated_at'
    ];

    protected $table = 'shipping_addresses';

    protected static $_table = 'shipping_addresses';

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
