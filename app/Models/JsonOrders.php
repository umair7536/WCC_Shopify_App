<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JsonOrders extends Model
{
    protected $fillable = [
        'json_data', 'account_id'
    ];

    protected $table = 'json_orders';
}
