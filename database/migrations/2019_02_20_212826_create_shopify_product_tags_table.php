<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyProductTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_product_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->nullable();
            $table->unsignedInteger('tag_id')->nullable();
            $table->bigInteger('product_id')->nullable();

            // Manage Foreign Key Relationships
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('tag_id')->references('id')->on('shopify_tags');
            $table->foreign('product_id')->references('product_id')->on('shopify_products');
            $table->index('product_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_product_tags');
    }
}
