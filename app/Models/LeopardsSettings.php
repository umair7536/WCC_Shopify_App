<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;


class LeopardsSettings extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['account_id', 'slug', 'name',  'data', 'active', 'created_at', 'updated_at', 'sort_number'];

    protected static $_fillable = ['name', 'slug', 'data', 'active'];

    protected $table = 'leopards_settings';

    protected static $_table = 'leopards_settings';

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
        $leopards_settings = LeopardsSettings::where([
            'account_id' => $account_id
        ])
            ->orderBy('id', 'asc')
            ->get();

        if($leopards_settings) {
            $data = $request->all();

            foreach($leopards_settings as $leopards_setting) {
                if(array_key_exists($leopards_setting->slug, $data)) {
                    self::where([
                        'account_id' => $account_id,
                        'slug' => $leopards_setting->slug,
                    ])->update([
                       'data' =>  $data[$leopards_setting->slug]
                    ]);
                }
            }

            return true;
        } else {
            return false;
        }
    }
}
