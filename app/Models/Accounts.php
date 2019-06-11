<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accounts extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'contact', 'resource_person',
        'created_at', 'updated_at','suspended'
    ];

    protected $table = 'accounts';
}
