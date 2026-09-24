<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssessmentOrderToGradesTable extends Migration
{
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->unsignedInteger('assessment_order')->nullable()->after('display_order_id');
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('assessment_order');
        });
    }
}
