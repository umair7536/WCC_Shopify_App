<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Schema;


class ShopifyLocations extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'location_id', 'name', 'address1', 'address2', 'city', 'zip', 'province', 'country',
        'phone', 'country_code', 'country_name', 'province_code', 'legacy', 'active',
        'created_at', 'updated_at', 'admin_graphql_api_id', 'account_id',
    ];

    protected static $skip_columns = [];

    protected $table = 'shopify_locations';

    protected static $_table = 'shopify_locations';

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

            /*
             * Set Legacy and Active fields
             */
            $timestamps = ['legacy', 'active'];
            if(in_array($column, $timestamps)) {
                $value = ($value) ? 1 : 0;
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
