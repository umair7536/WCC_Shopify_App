<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_subscriptions', function ($table) {
            $table->increments('id');

            $table->string('name');
            $table->integer('quantity');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->unsignedInteger('account_id')->nullable();
            $table->unsignedInteger('plan_id')->nullable();

            // Foreign Key Relationships
            $table->foreign('account_id', 'account_subscriptions_account')->references('id')->on('accounts');
            $table->foreign('plan_id', 'account_subscriptions_plan')->references('id')->on('plans');

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
        Schema::drop('account_subscriptions');
    }
}
