<?php

namespace App\Models;

use Carbon\Carbon;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use Config;
use Illuminate\Support\Facades\Schema;


class LoadSheets extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'load_sheet_id', 'total_packets', 'created_at', 'updated_at', 'account_id'
    ];

    protected $table = 'load_sheets';


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

        if($request->get('load_sheet_id') != '') {
            $where[] = array(
                'load_sheet_id',
                'like',
                '%' . $request->get('load_sheet_id') . '%'
            );
        }

        if ($request->get('created_at_from') && $request->get('created_at_from') != '') {
            $where[] = array(
                'created_at',
                '>=',
                $request->get('created_at_from') . ' 00:00:00'
            );
        }

        if ($request->get('created_at_to') && $request->get('created_at_to') != '') {
            $where[] = array(
                'created_at',
                '<=',
                $request->get('created_at_to') . ' 23:59:59'
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

        if($request->get('load_sheet_id') != '') {
            $where[] = array(
                'load_sheet_id',
                'like',
                '%' . $request->get('load_sheet_id') . '%'
            );
        }

        if ($request->get('created_at_from') && $request->get('created_at_from') != '') {
            $where[] = array(
                'created_at',
                '>=',
                $request->get('created_at_from') . ' 00:00:00'
            );
        }

        if ($request->get('created_at_to') && $request->get('created_at_to') != '') {
            $where[] = array(
                'created_at',
                '<=',
                $request->get('created_at_to') . ' 23:59:59'
            );
        }

        if(count($where)) {
            return self::where($where)->limit($iDisplayLength)->orderBy('created_at', 'desc')->offset($iDisplayStart)->get();
        } else {
            return self::limit($iDisplayLength)->orderBy('created_at', 'desc')->offset($iDisplayStart)->get();
        }
    }

    static public function createLoadSheet($load_sheet_id, $booked_packets, $account_id) {

        /**
         * Create Sheet
         */
        $load_sheet = self::create([
            'load_sheet_id' => $load_sheet_id,
            'total_packets' => count($booked_packets),
            'account_id' => $account_id,
        ]);

        /**
         * Keep record of load sheet packets
         */
        $load_sheet_packets = [];
        foreach ($booked_packets as $booked_packet) {
            $load_sheet_packets[] = array(
                'load_sheet_id' => $load_sheet->id,
                'sheet_id' => $load_sheet_id,
                'booked_packet_id' => $booked_packet->id,
                'cn_number' => $booked_packet->cn_number,
                'order_id' => $booked_packet->order_id,
                'order_number' => $booked_packet->order_number,
                'account_id' => $account_id,
            );
        }

        LoadSheetPackets::insert($load_sheet_packets);

        return $load_sheet;
    }
}
