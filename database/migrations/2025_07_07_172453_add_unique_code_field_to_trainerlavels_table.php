<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueCodeFieldToTrainerlavelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainerlavels', function (Blueprint $table) {
            $table->string('unique_code', 10)->nullable()->after('display_order_id');
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
            $table->dropColumn('unique_code');
        });
    }
}
