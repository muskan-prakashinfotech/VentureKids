<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStandardAssessmentToSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->tinyInteger('standard_assessment_assigned')->nullable()->after('logout_redirect_url');
            $table->tinyInteger('standard_assessment_enabled')->default(0)->after('standard_assessment_assigned');
            $table->date('standard_assessment_enabled_from')->nullable()->after('standard_assessment_enabled');
            $table->date('standard_assessment_enabled_to')->nullable()->after('standard_assessment_enabled_from');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('standard_assessment_enabled_to');
            $table->dropColumn('standard_assessment_enabled_from');
            $table->dropColumn('standard_assessment_enabled');
            $table->dropColumn('standard_assessment_assigned');
        });
    }
}

