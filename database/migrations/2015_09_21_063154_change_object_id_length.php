<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeObjectIdLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device', function ($table) {
            $table->string('object_id', 18)->change();
        });
        Schema::table('specials', function ($table) {
            $table->string('object_id', 18)->change();
        });
        Schema::table('batch', function ($table) {
            $table->string('object_id', 18)->change();
        });
        Schema::table('managers', function ($table) {
            $table->string('object_id', 18)->change();
        });
        Schema::table('transaction', function ($table) {
            $table->string('object_id', 18)->change();
        });
        Schema::table('token', function ($table) {
            $table->string('object_id', 18)->change();
        });
        Schema::table('managerBillingLog', function ($table) {
            $table->string('object_id', 18)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
