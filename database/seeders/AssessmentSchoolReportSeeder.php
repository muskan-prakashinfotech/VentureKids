<?php

namespace Database\Seeders;

use App\Models\AssessmentSchoolReport;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use PDF;

/**
 * Seeds `assessment_school_report` (assessment_type = 'realq') — a
 * school-level aggregate report for Demo School 1, built from the same
 * grade/parameter data seeded elsewhere. Generates a real PDF via the same
 * backend.realq_assessment.reports.report_pdf view the live "Generate
 * School Report" button uses (report_path is NOT NULL). Requires
 * RealQAssessmentSchoolAssignmentSeeder to have already run.
 */
class AssessmentSchoolReportSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::where('official_email_id', 'school1@venturekids.test')->first();

        if (!$school) {
            $this->command?->error('School not found — run SchoolSeeder first.');
            return;
        }

        if (AssessmentSchoolReport::where('school_id', $school->id)->where('assessment_type', 'realq')->exists()) {
            $this->command?->info('School-level RealQ report already seeded.');
            return;
        }

        $gradeSummary = [[
            'grade_name' => 'Grade1',
            'total_students' => 1,
            'parameters' => [
                ['name' => 'Critical Thinking', 'levels' => [['name' => 'Scale 3', 'count' => 1]]],
                ['name' => 'Creativity', 'levels' => [['name' => 'Scale 2', 'count' => 1]]],
                ['name' => 'Problem Solving', 'levels' => [['name' => 'Scale 3', 'count' => 1]]],
                ['name' => 'Decision Making', 'levels' => [['name' => 'Scale 3', 'count' => 1]]],
            ],
        ]];

        $reportSections = [
            'assessment_overview' => "This report summarizes RealQ baseline results for {$school->school_name}, Grade1.",
            'cross_parameter_patterns' => 'Students show strongest performance in Problem Solving and Decision Making, with Creativity as an area for growth.',
            'what_this_means' => 'Overall the cohort demonstrates solid entrepreneurial thinking fundamentals, with room to build more original idea generation.',
            'actionable_recommendations' => 'Incorporate more open-ended brainstorming activities to strengthen creativity alongside the already-strong problem-solving skills.',
            'moderation_note' => 'This is demo/seed data generated for testing purposes, not a real AI-generated assessment report.',
        ];

        $pdf = PDF::loadView('backend.realq_assessment.reports.report_pdf', [
            'schoolName' => $school->school_name,
            'schoolLogoPath' => public_path('asset/images/kids-tm-logo.png'),
            'reportSections' => $reportSections,
            'generatedAt' => now()->toDateTimeString(),
            'totalStudents' => 1,
            'gradeSummary' => $gradeSummary,
            'assessmentFormat' => 'Subjective, scenario-based REALQ questions',
        ]);

        $tenantId = $school->tenant_id ?: 'common';
        $fileName = 'realq_school_report_' . substr(hash('sha256', 'realq-school-report-' . $school->id), 0, 16) . '.pdf';
        $reportPath = trim($tenantId, '/') . '/school/reports/realq_assessment/' . $fileName;
        $absoluteDir = public_path('tenants/' . trim($tenantId, '/') . '/school/reports/realq_assessment');

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0755, true);
        }

        File::put(public_path('tenants/' . $reportPath), $pdf->output());

        AssessmentSchoolReport::create([
            'school_id' => $school->id,
            'assessment_type' => 'realq',
            'report_path' => $reportPath,
        ]);

        $this->command?->info("Seeded 1 school-level RealQ report for {$school->school_name}.");
    }
}
