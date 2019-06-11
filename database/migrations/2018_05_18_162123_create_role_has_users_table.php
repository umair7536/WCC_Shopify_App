<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleHasUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_has_users', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('user_id');

            // Manage Foreing Key Relationshops
            $table->foreign('role_id', 'rolehasusers_role')->references('id')->on('roles');
            $table->foreign('user_id', 'rolehasusers_user')->references('id')->on('users');

            $table->primary(['role_id', 'user_id'], 'rolehasusers_mixed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_has_users');
    }
}
