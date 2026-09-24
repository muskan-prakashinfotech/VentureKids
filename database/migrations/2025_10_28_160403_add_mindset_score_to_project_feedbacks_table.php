<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMindsetScoreToProjectFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_feedback', function (Blueprint $table) {
            $table->integer('mindset_score')->nullable()->after('mindset_feedback');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_feedback', function (Blueprint $table) {
            $table->dropColumn('mindset_score');
        });
    }
}
