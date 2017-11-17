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
            $table->string('username');
            $table->string('phone');
            $table->string('password');
            $table->enum('gender', [0, 1])->comment('1=female, 0=male');
            $table->enum('have_car', ['0', '1'])->comment('0=no,1=yes');
            $table->string('car_number');
            $table->string('car_model');
            $table->string('car_color');
            $table->enum('type', ['user', 'admin']);
            $table->string('img');
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
