<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreateShopifyShopsTable extends Migration
{
    use SoftDeletes;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_shops', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('access_token');
            $table->bigInteger('store_id');
            $table->string('name', 255)->nullable();
            $table->string('domain', 255)->nullable();
            $table->string('myshopify_domain', 255)->nullable();
            $table->string('shop_owner', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('timezone', 255)->nullable();
            $table->string('iana_timezone', 255)->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Add foreign key
            $table->foreign('account_id','shopify_shops_account')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_shops');
    }
}
