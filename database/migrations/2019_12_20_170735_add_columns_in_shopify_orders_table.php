<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->unsignedInteger('booking_id')->nullable()->after('customer_id');
            $table->string('cn_number')->nullable()->after('booking_id');

            $table->foreign('booking_id', 'shopify_orders_booking')->references('id')->on('booked_packets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->dropForeign('shopify_orders_booking');
            $table->dropColumn('booking_id');
            $table->dropColumn('cn_number');
        });
    }
}
