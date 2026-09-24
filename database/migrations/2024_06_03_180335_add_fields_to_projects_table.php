<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('status_changed_by_id')->nullable()->after('project_status')->references('id')->on('trainers')->onDelete('cascade');
            $table->timestamp('status_changed_at')->nullable()->after('status_changed_by_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('status_changed_by_id');
            $table->dropColumn('status_changed_at');
        });
    }
}
