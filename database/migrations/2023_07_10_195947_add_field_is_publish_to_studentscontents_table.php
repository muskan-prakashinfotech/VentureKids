<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldIsPublishToStudentscontentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('studentscontents', function (Blueprint $table) {
            $table->tinyInteger('is_publish')->default('1')->after('worksheet');
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
            $table->dropColumn('is_publish');
        });
    }
}
