<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleFieldsToTrainercontentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainercontents', function (Blueprint $table) {
            $table->string('video_name')->nullable()->after('video');
            $table->string('pdf_name')->nullable()->after('pdf');
            $table->string('worksheet_name')->nullable()->after('worksheet');
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
            $table->dropColumn('video_name');
            $table->dropColumn('pdf_name');
            $table->dropColumn('worksheet_name');
        });
    }
}
