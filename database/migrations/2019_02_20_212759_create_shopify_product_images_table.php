<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_product_images', function (Blueprint $table) {
            $table->increments('id');

            $table->bigInteger('image_id')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->text('variant_ids')->nullable();
            $table->text('src')->nullable();
            $table->string('alt')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            $table->string('admin_graphql_api_id')->nullable();

            // Manage Foreign Key Relationships
            $table->foreign('product_id')->references('product_id')->on('shopify_products');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('product_id');
            $table->index('image_id');

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
        Schema::dropIfExists('shopify_product_images');
    }
}
