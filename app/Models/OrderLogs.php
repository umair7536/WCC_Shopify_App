<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;


class OrderLogs extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['order_id', 'name', 'order_number', 'message', 'account_id', 'created_at', 'updated_at'];

    protected $table = 'order_logs';
}
