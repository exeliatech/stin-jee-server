<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device', function (Blueprint $table) {

            $table->string('object_id', 16);
            $table->primary('object_id');
            $table->float('location_latitude', 12, 10);
            $table->float('location_longitude', 15, 12);
            $table->integer('device_type');
            $table->string('device_token', 250);
            $table->boolean('sended');
            $table->dateTime('last_visit_date');
            $table->string('device_id', 42);
            $table->string('id', 16); //what?
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
        Schema::drop('device');
    }
}
