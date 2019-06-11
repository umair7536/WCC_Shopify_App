<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_id');
            $table->string('admin_graphql_api_id');

            $table->string('email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();

            // Address Informaton
            $table->string('company')->nullable();
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('zip')->nullable();
            $table->string('phone')->nullable();
            $table->string('province_code')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();

            $table->integer('orders_count')->nullable();
            $table->string('state');
            $table->double('total_spent')->default(0.00);
            $table->string('last_order_id')->nullable();
            $table->string('last_order_name')->nullable();
            $table->text('note')->nullable();
            $table->boolean('verified_email');
            $table->string('currency')->nullable();

            $table->text('addresses')->nullable();
            $table->text('default_address')->nullable();

            $table->unsignedInteger('account_id')->nullable();
            // Manage Foreign Key Relationships
            $table->foreign('account_id', 'shopify_customers_account')->references('id')->on('accounts');

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
        Schema::dropIfExists('shopify_customers');
    }
}
