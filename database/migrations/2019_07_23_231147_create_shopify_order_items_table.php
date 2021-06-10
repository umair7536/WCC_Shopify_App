<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_order_items', function (Blueprint $table) {
            $table->increments('id');

            $table->bigInteger('order_id');
            $table->bigInteger('item_id');
            $table->bigInteger('variant_id')->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->string('admin_graphql_api_id');
            $table->string('title')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('sku')->nullable();
            $table->string('variant_title')->nullable();
            $table->string('vendor')->nullable();
            $table->string('fulfillment_service')->nullable();
            $table->unsignedInteger('requires_shipping')->default(0);
            $table->unsignedInteger('taxable')->default(0);
            $table->text('gift_card')->nullable();
            $table->text('name')->nullable();
            $table->string('variant_inventory_management')->nullable();
            $table->text('properties')->nullable();

            $table->unsignedInteger('product_exists')->default(0);
            $table->unsignedInteger('fulfillable_quantity')->default(0);
            $table->unsignedInteger('grams')->default(0);

            $table->double('price', 11, 2)->default(0.00);
            $table->double('total_discount', 11, 2)->default(0.00);
            $table->string('fulfillment_status')->nullable();

            $table->text('price_set')->nullable();
            $table->text('total_discount_set')->nullable();
            $table->text('discount_allocations')->nullable();
            $table->text('tax_lines')->nullable();

            $table->unsignedInteger('account_id')->nullable();
            // Manage Foreign Key Relationships
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_order_items');
    }
}
