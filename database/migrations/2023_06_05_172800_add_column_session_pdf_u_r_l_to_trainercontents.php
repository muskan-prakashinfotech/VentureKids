<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSessionPdfURLToTrainercontents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainercontents', function (Blueprint $table) {
            $table->string('drive_url')->nullable()->after('video_url');
            $table->string('pdf')->nullable()->after('drive_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainercontents', function (Blueprint $table) {
            $table->dropColumn('drive_url');
            $table->dropColumn('pdf');
        });
    }
}
