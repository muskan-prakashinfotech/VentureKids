<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalWorkflowToAssessmentStudentReport extends Migration
{
    public function up()
    {
        Schema::table('assessment_student_report', function (Blueprint $table) {
            // Make report_path nullable — pending reports have no PDF yet
            $table->string('report_path')->nullable()->change();

            $table->enum('status', ['pending', 'generated', 'approved'])
                ->default('pending')
                ->after('assessment_type');

            $table->json('report_data')
                ->nullable()
                ->after('report_text')
                ->comment('Structured AI output stored as JSON for admin editing');

            $table->timestamp('approved_at')
                ->nullable()
                ->after('report_data');

            $table->unsignedBigInteger('approved_by')
                ->nullable()
                ->after('approved_at')
                ->comment('Admin user id who approved the report');
        });
    }

    public function down()
    {
        Schema::table('assessment_student_report', function (Blueprint $table) {
            $table->dropColumn(['status', 'report_data', 'approved_at', 'approved_by']);
            $table->string('report_path')->nullable(false)->change();
        });
    }
}
