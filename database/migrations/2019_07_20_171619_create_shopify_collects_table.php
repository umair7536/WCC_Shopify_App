<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyCollectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_collects', function (Blueprint $table) {
            $table->increments('id');

            $table->string('collect_id')->nullable();
            $table->string('collection_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('featured')->nullable();
            $table->string('position')->nullable();
            $table->string('sort_value')->nullable();

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
        Schema::dropIfExists('shopify_collects');
    }
}
