<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorksheetNameToStudentscontentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('studentscontents', function (Blueprint $table) {
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
        Schema::table('studentscontents', function (Blueprint $table) {
            $table->dropColumn('worksheet_name');
        });
    }
}
