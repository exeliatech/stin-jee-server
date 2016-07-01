<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManagerBillingLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('managerBillingLog', function (Blueprint $table) {
            $table->string('object_id', 16);
            $table->primary('object_id');
            $table->date('month');
            $table->text('tokens');
            $table->float('price');
            $table->string('country', 2);
            $table->string('manager_email', 64);
            $table->string('manager_name', 64);
            $table->integer('status');
            $table->string('manager', 16);
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
        Schema::drop('managerBillingLog');
    }
}
