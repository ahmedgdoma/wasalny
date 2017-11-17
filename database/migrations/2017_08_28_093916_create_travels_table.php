<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTravelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('travel_name');
            $table->string('capacity');
            $table->time('start_time');
            $table->enum('passenger_gender',['0','1','2'])->comment('0=male,1=female,2=all');
            $table->string('days');
            $table->enum('status', ['0','1','2'])->comment('activated','deactivated','temporary_deactivated');
            $table->enum('payment', ['0', '1'])->comment('0=free,1=cost');
            $table->integer('cost');
            $table->text('notes');
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
        Schema::drop('travels');
    }
}
