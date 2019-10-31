<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopifyShops extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'access_token', 'store_id', 'name', 'store_owner', 'domain', 'myshopify_domain',
        'phone', 'email', 'customer_email', 'timezone', 'plan_id', 'activated_on', 'shopify_billing_id',
        'iana_timezone', 'account_id', 'created_at', 'updated_at'
    ];

    protected $table = 'shopify_shops';
}
