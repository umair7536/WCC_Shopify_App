<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyProductOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_product_options', function (Blueprint $table) {
            $table->increments('id');

            $table->bigInteger('option_id')->nullable();
            $table->string('name')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->text('values')->nullable();

            $table->bigInteger('product_id')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            // Manage Foreign Key Relationships
            $table->foreign('product_id')->references('product_id')->on('shopify_products');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('product_id');
            $table->index('option_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_product_options');
    }
}
