<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token', function (Blueprint $table) {
            $table->string('object_id', 16);
            $table->primary('object_id');
            $table->string('country', 2);
            $table->string('email', 64);
            $table->string('batch', 16);
            $table->string('manager', 16);
            $table->dateTime('ends_at');
            $table->dateTime('purchase_date');
            $table->float('price');
            $table->integer('purchase_online');
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
        Schema::drop('token');
    }
}
