<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_product_variants', function (Blueprint $table) {
            $table->increments('id');

            $table->string('variant_id')->nullable();
            $table->string('admin_graphql_api_id')->nullable();
            $table->string('title')->nullable();
            $table->string('barcode')->nullable();
            $table->string('sku')->nullable();
            $table->double('compare_at_price', 11, 2)->nullable();
            $table->double('price', 11, 2)->nullable();
            $table->string('fulfillment_service')->nullable();
            $table->string('grams')->nullable();
            $table->unsignedInteger('image_id')->nullable();
            $table->unsignedInteger('inventory_item_id')->nullable();
            $table->string('inventory_management')->nullable();
            $table->string('option1')->nullable();
            $table->string('option2')->nullable();
            $table->string('option3')->nullable();
            $table->string('inventory_policy')->nullable();
            $table->unsignedInteger('inventory_quantity')->nullable();
            $table->unsignedInteger('old_inventory_quantity')->nullable();
            $table->unsignedInteger('inventory_quantity_adjustment')->nullable();
            $table->mediumText('metafields')->nullable();
            $table->mediumText('presentment_prices')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->boolean('requires_shipping')->default(false);
            $table->boolean('taxable')->default(false);
            $table->string('tax_code')->nullable();
            $table->string('weight')->nullable();
            $table->string('weight_unit')->nullable();
            $table->string('product_id')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            // Manage Foreign Key Relationships
            $table->foreign('product_id')->references('product_id')->on('shopify_products');
            $table->foreign('account_id')->references('id')->on('accounts');

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
        Schema::dropIfExists('shopify_product_variants');
    }
}
