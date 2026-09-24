<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProjectfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projectfiles', function (Blueprint $table) {
            $table->unsignedBigInteger('project_details_id')->references('id')->on('project_details')->onUpdate('cascade')->onDelete('cascade')->after('project_id');
            $table->enum('attachment_type', ['Image', 'Video', 'Pdf'])->nullable()->after('student_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projectfiles', function (Blueprint $table) {
            $table->dropColumn('attachment_type');
        });
    }
}
