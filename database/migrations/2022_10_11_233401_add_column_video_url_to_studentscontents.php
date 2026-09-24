<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnVideoUrlToStudentscontents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('studentscontents', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('video');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('studentscontents', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
}
