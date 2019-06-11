<?php

namespace App\Models;

class ShopifyJobs extends BaseModal
{
    protected $fillable = ['payload', 'type', 'reserved_at', 'available_at', 'created_at', 'account_id'];

    protected $table = 'shopify_jobs';
}
