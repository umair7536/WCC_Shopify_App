<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrailChanges extends Model
{
    protected $fillable = ['audit_trail_id', 'field_name', 'field_before', 'field_after', 'created_at', 'updated_at'];

    protected $table = 'audit_trail_changes';

}
