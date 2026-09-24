<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessPlanIdToBusinessPlanTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_plan_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('business_plan_id')->nullable()->after('id');
            $table->foreign('business_plan_id')->references('id')->on('business_plans')->onDelete('cascade');
        });

        Schema::table('business_plan_question_options', function (Blueprint $table) {
            $table->unsignedBigInteger('business_plan_id')->nullable()->after('id');
            $table->foreign('business_plan_id')->references('id')->on('business_plans')->onDelete('cascade');
        });

        Schema::table('business_plan_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('business_plan_id')->nullable()->after('id');
            $table->foreign('business_plan_id')->references('id')->on('business_plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('business_plan_questions', function (Blueprint $table) {
            $table->dropForeign(['business_plan_id']);
            $table->dropColumn('business_plan_id');
        });

        Schema::table('business_plan_question_options', function (Blueprint $table) {
            $table->dropForeign(['business_plan_id']);
            $table->dropColumn('business_plan_id');
        });

        Schema::table('business_plan_answers', function (Blueprint $table) {
            $table->dropForeign(['business_plan_id']);
            $table->dropColumn('business_plan_id');
        });
    }
}
