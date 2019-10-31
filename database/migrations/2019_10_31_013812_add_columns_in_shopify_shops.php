<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInShopifyShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_shops', function (Blueprint $table) {
            $table->date('activated_on')->nullable();
            $table->unsignedInteger('shopify_billing_id')->nullable();
            $table->foreign('shopify_billing_id', 'shopify_billing_shopify_billing_id')->references('id')->on('shopify_billings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_shops', function (Blueprint $table) {
            $table->dropColumn('activated_on');
            $table->dropForeign('shopify_billing_shopify_billing_id');
            $table->dropColumn('shopify_billing_id');
        });
    }
}
