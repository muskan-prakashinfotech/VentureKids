<?php

namespace Database\Seeders;

use App\Models\AssessmentStudentReport;
use App\Models\RealQAssessmentRubric;
use App\Models\School;
use App\Models\Students;
use App\Models\User;
use App\Traits\RealQReportParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use PDF;

/**
 * Seeds `assessment_student_report` (assessment_type = 'realq') with one
 * approved report for school1.student1, built from the answers seeded by
 * AssessmentAnswerSeeder. report_data/report_text are hand-authored to
 * match the exact JSON shape Backend\RealQAssessmentStudentReportController
 * ::generate() would have produced via OpenAI (see app/Traits/
 * RealQReportParser.php) — no OpenAI call is made here. A real PDF is
 * generated via the same student.assessment.report_pdf view the live
 * "Generate Report" button uses. Requires RealQAssessmentRubricSeeder and
 * AssessmentAnswerSeeder to have already run.
 */
class AssessmentStudentReportSeeder extends Seeder
{
    use RealQReportParser;


    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::with('school')->find(
            Students::where('user_id', $studentUser->id)->value('id')
        ) : null;
        $rubrics = RealQAssessmentRubric::pluck('id', 'name');

        if (!$student || $rubrics->isEmpty()) {
            $this->command?->error('Student or rubrics not found — run RealQAssessmentRubricSeeder and SchoolSeeder first.');
            return;
        }

        $performanceRows = [
            ['parameter' => 'Critical Thinking', 'level' => 'Scale 3', 'evidence' => 'Identified a specific, real problem (snack wrapper waste) and clearly explained who it affects.', 'rubric_id' => $rubrics['Scale 3'] ?? null, 'rubric_score' => 3],
            ['parameter' => 'Creativity', 'level' => 'Scale 2', 'evidence' => 'Proposed a workable idea but leaned on a familiar concept rather than a fully original angle.', 'rubric_id' => $rubrics['Scale 2'] ?? null, 'rubric_score' => 2],
            ['parameter' => 'Problem Solving', 'level' => 'Scale 3', 'evidence' => 'Explained clearly why the problem matters and who it affects, showing real problem-framing skill.', 'rubric_id' => $rubrics['Scale 3'] ?? null, 'rubric_score' => 3],
            ['parameter' => 'Decision Making', 'level' => 'Scale 3', 'evidence' => 'Reasoned through the consequences of leaving the problem unsolved before concluding it was worth acting on.', 'rubric_id' => $rubrics['Scale 3'] ?? null, 'rubric_score' => 3],
        ];

        $scenarioRows = [
            [
                'label' => 'Question 1',
                'question' => 'Describe one real problem you have noticed at your school or in your neighborhood, and explain who it affects and why it matters.',
                'evidence' => [
                    ['parameter' => 'Critical Thinking', 'level' => 'Scale 3', 'text' => 'Clearly identified snack wrapper waste as a real, observed problem affecting the whole school.'],
                ],
            ],
            [
                'label' => 'Question 2',
                'question' => 'Explain why the problem you described is important enough for someone to want to solve it.',
                'evidence' => [
                    ['parameter' => 'Problem Solving', 'level' => 'Scale 3', 'text' => 'Connected the problem to real costs (cleanup, wasted resources) rather than just restating it.'],
                    ['parameter' => 'Decision Making', 'level' => 'Scale 3', 'text' => 'Reasoned about consequences of inaction before concluding the problem was worth solving.'],
                ],
            ],
        ];

        $summary = "{$student->name} shows solid critical thinking and problem-solving skills, clearly identifying real problems and reasoning through why they matter. Creativity is still developing — encourage exploring more original angles rather than familiar ideas.";

        $reportData = [
            'summary' => $summary,
            'performance_rows' => $performanceRows,
            'scenario_rows' => $scenarioRows,
        ];

        $report = AssessmentStudentReport::firstOrCreate(
            ['student_id' => $student->id, 'assessment_type' => 'realq'],
            [
                'student_grade_id' => $student->student_grade_id,
                'status' => 'approved',
                'report_data' => $reportData,
                'report_text' => $summary,
                'approved_at' => now(),
                'approved_by' => 1,
            ]
        );

        if (empty($report->report_path)) {
            $this->generateReportPdf($report, $student, $reportData, $summary);
        }

        $this->command?->info("Seeded 1 approved RealQ student report for {$student->name}.");
    }

    private function generateReportPdf(AssessmentStudentReport $report, Students $student, array $reportData, string $overview): void
    {
        $pdfSections = $this->reportDataToSections($reportData);

        $pdf = PDF::loadView('student.assessment.report_pdf', [
            'studentName' => $student->name,
            'schoolLogoPath' => public_path('asset/images/kids-tm-logo.png'),
            'reportOverview' => $overview,
            'mode' => 'subjective',
            'generatedAt' => now()->toDateTimeString(),
            'total' => 5,
            'answered' => 5,
            'completion' => 100,
            'reportBody' => $overview,
            'reportSections' => $pdfSections,
        ]);

        $tenantId = optional($student->school)->tenant_id ?: 'common';
        $fileName = 'realq_report_' . substr(hash('sha256', 'realq-report-' . $report->id), 0, 16) . '.pdf';
        $reportPath = trim($tenantId, '/') . '/student/reports/realq_assessment/' . $fileName;
        $absoluteDir = public_path('tenants/' . trim($tenantId, '/') . '/student/reports/realq_assessment');

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0755, true);
        }

        File::put(public_path('tenants/' . $reportPath), $pdf->output());

        $report->update(['report_path' => $reportPath]);
    }
}
