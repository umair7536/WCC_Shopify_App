<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyCustomCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_custom_collections', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('collection_id')->nullable();
            $table->string('title')->nullable();
            $table->text('body_html')->nullable();
            $table->string('handle')->nullable();
            $table->text('image')->nullable();
            $table->mediumText('image_link')->nullable();
            $table->text('metafields')->nullable();
            $table->unsignedTinyInteger('published')->nullable();
            $table->dateTimeTz('published_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();
            $table->string('sort_order')->nullable();
            $table->string('template_suffix')->nullable();
            $table->unsignedTinyInteger('active')->default(1);

            $table->unsignedInteger('account_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            // Manage Foreign Key Relationships
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

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
        Schema::dropIfExists('shopify_custom_collections');
    }
}
