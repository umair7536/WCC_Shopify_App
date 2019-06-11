<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_products', function (Blueprint $table) {
            $table->increments('id');

            $table->string('product_id')->unique()->nullable();
            $table->string('title')->nullable();
            $table->text('body_html')->nullable();
            $table->string('handle')->nullable();
            $table->string('product_type')->nullable();
            $table->dateTimeTz('published_at')->nullable();
            $table->string('published_scope')->nullable();
            $table->mediumText('tags')->nullable();
            $table->string('template_suffix')->nullable();
            $table->string('metafields_global_title_tag')->nullable();
            $table->string('metafields_global_description_tag')->nullable();
            $table->text('image')->nullable();
            $table->text('image_src')->nullable();
            $table->text('admin_graphql_api_id')->nullable();
            $table->string('vendor')->nullable();

            $table->unsignedInteger('account_id')->nullable();
            // Manage Foreign Key Relationships
            $table->foreign('account_id')->references('id')->on('accounts');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_products');
    }
}
