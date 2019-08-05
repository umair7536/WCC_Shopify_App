<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_orders', function (Blueprint $table) {
            $table->increments('id');

            $table->string('order_id');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('number')->nullable();
            $table->string('order_number')->nullable();
            $table->string('note')->nullable();
            $table->string('token')->nullable();
            $table->string('gateway')->nullable();
            $table->string('test')->nullable();

            $table->double('total_price', 11,2)->default(0.00);
            $table->double('subtotal_price', 11,2)->default(0.00);
            $table->double('total_weight', 11,3)->default(0.000);
            $table->double('total_tax', 11,2)->default(0.00);
            $table->string('taxes_included')->nullable();
            $table->string('currency')->nullable();
            $table->string('financial_status')->nullable();

            $table->unsignedTinyInteger('confirmed')->default(0);
            $table->double('total_discounts', 11,2)->default(0.00);
            $table->double('total_line_items_price', 11,2)->default(0.00);
            $table->string('cart_token')->nullable();
            $table->string('buyer_accepts_marketing')->nullable();
            $table->text('referring_site')->nullable();
            $table->text('landing_site')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->double('total_price_usd', 11, 2)->default(0.00);
            $table->string('checkout_token')->nullable();
            $table->text('reference')->nullable();
            $table->string('user_id')->nullable();
            $table->string('location_id')->nullable();
            $table->string('source_identifier')->nullable();
            $table->text('source_url')->nullable();
            $table->string('device_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('customer_locale')->nullable();
            $table->string('app_id')->nullable();
            $table->string('browser_ip')->nullable();
            $table->text('landing_site_ref')->nullable();

            $table->text('discount_applications')->nullable();
            $table->text('discount_codes')->nullable();
            $table->text('note_attributes')->nullable();

            $table->text('payment_gateway_names')->nullable();
            $table->string('processing_method')->nullable();
            $table->string('checkout_id')->nullable();
            $table->string('source_name')->nullable();

            $table->string('fulfillment_status')->nullable();
            $table->text('tax_lines')->nullable();
            $table->string('tags')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('order_status_url')->nullable();
            $table->text('presentment_currency')->nullable();

            $table->text('total_line_items_price_set')->nullable();
            $table->text('total_discounts_set')->nullable();
            $table->text('total_shipping_price_set')->nullable();
            $table->text('subtotal_price_set')->nullable();
            $table->text('total_price_set')->nullable();
            $table->text('total_tax_set')->nullable();
            $table->double('total_tip_received', 11, 2)->default(0.00);
            $table->string('admin_graphql_api_id')->nullable();

            $table->string('shipping_lines')->nullable();
            $table->string('fulfillments')->nullable();
            $table->string('refunds')->nullable();

            $table->string('customer_id')->nullable();

            $table->unsignedInteger('account_id')->nullable();
            // Manage Foreign Key Relationships
            $table->foreign('account_id')->references('id')->on('accounts');

            $table->dateTime('processed_at')->nullable();
            $table->dateTime('closed_at')->nullable();

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
        Schema::dropIfExists('shopify_orders');
    }
}
