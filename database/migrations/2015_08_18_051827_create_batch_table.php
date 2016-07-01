<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch', function (Blueprint $table) {
            $table->string('country', 2);
            $table->string('object_id', 10);
            $table->primary('object_id');
            $table->boolean('promotion');
            $table->boolean('active');
            $table->dateTime('ends_at');
            $table->string('email', 64);
            $table->boolean('test');
            $table->integer('purchased_online');
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
        Schema::drop('batch');
    }
}
