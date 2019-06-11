<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('user_type_id')->nullable()->after('phone');
            $table->unsignedInteger('account_id')->nullable()->after('user_type_id');

            $table->foreign('user_type_id', 'users_user_type')->references('id')->on('user_types');
            $table->foreign('account_id', 'users_account')->references('id')->on('accounts');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_user_type');
            $table->dropColumn('user_type_id');

            $table->dropForeign('users_account');
            $table->dropColumn('account_id');
        });
    }
}
