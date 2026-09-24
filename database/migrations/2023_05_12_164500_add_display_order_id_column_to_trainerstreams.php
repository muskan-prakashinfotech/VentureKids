<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayOrderIdColumnToTrainerstreams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainerstreams', function (Blueprint $table) {
            $table->smallInteger('display_order_id')->after('creator_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainerstreams', function (Blueprint $table) {
            $table->dropColumn('display_order_id');
        });
    }
}
