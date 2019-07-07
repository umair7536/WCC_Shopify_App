<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_billings', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('charge_id');
            $table->string('name');
            $table->string('api_client_id');
            $table->double('price', 11, 2);
            $table->string('status');
            $table->text('return_url');
            $table->datetime('billing_on')->nullable();
            $table->string('test')->default(0);
            $table->datetime('activated_on')->nullable();
            $table->datetime('cancelled_on')->nullable();
            $table->unsignedInteger('trial_days');
            $table->datetime('trial_ends_on')->nullable();
            $table->text('decorated_return_url');
            $table->text('confirmation_url');

            // Manage Foreign Key Relationships
            $table->unsignedInteger('plan_id');
            $table->foreign('plan_id', 'shopify_billings_plan')->references('id')->on('shopify_plans');

            $table->unsignedInteger('account_id');
            $table->foreign('account_id', 'shopify_billings_account')->references('id')->on('accounts');

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
        Schema::dropIfExists('shopify_billings');
    }
}
