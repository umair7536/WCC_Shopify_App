<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->tinyInteger('main_account')->default(0);
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->string('image_src')->nullable();
            $table->unsignedTinyInteger('active')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['email', 'deleted_at']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
