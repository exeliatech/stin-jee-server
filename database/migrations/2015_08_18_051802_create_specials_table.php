<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specials', function (Blueprint $table) {
            $table->string('object_id', 10);
            $table->primary('object_id');
            $table->string('country', 32);
            $table->string('country_code', 2);
            $table->string('name', 128);
            $table->text('description');
            $table->integer('status');
            $table->float('location_latitude', 13, 10);
            $table->float('location_longitude', 15, 12);
            $table->text('address');
            $table->string('phone', 16);
            $table->string('image', 200);
            $table->string('batch', 16);
            $table->string('token', 16);
            $table->string('store', 100);
            $table->string('site', 250);
            $table->boolean('let_admin_choose_image');
            $table->integer('valid_for');
            $table->boolean('active');
            $table->dateTime('activated_at');
            $table->dateTime('ends_at');
            $table->string('image600', 200);
            $table->string('image320', 200);
            $table->string('image100', 200);
            $table->integer('views_count');
            $table->string('store_logo', 200);
            $table->string('store_logo_bg', 8);
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
        Schema::drop('specials');
    }
}
