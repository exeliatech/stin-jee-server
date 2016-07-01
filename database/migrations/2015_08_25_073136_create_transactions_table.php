<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->string('object_id', 16);
            $table->primary('object_id');
            $table->string('cart_number', 32);
            $table->string('person', 64);
            $table->float('amount');
            $table->string('batch_id', 16);
            $table->string('country', 2);
            $table->string('email', 64);
            $table->string('company', 64);
            $table->string('city', 64);
            $table->string('store', 64);
            $table->string('address', 64);
            $table->string('province', 64);
            $table->string('postal_id', 64);
            $table->string('fiscal_id', 64);
            $table->boolean('updated');
            $table->integer('tokens');
            $table->boolean('buyed_online');
            $table->boolean('online');
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
        Schema::drop('transaction');
    }
}
