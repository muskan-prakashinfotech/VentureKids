<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayOrderFieldToTrainerlavelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainerlavels', function (Blueprint $table) {
            $table->smallInteger('display_order_id')->after('image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainerlavels', function (Blueprint $table) {
            $table->dropColumn('display_order_id');
        });
    }
}
