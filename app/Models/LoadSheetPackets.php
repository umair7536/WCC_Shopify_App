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


class LoadSheetPackets extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'cn_number', 'load_sheet_id', 'booked_packet_id', 'created_at', 'updated_at', 'account_id'
    ];

    protected $table = 'load_sheet_packets';
}
