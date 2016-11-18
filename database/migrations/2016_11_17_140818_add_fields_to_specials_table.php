<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToSpecialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specials', function ($table) {
            $table->unsignedTinyInteger('type')->default(1);
            $table->unsignedTinyInteger('source')->default(1);
            $table->unsignedTinyInteger('action')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('specials', function ($table) {
            $table->dropColumn(['type', 'source', 'action']);
        });
    }
}
