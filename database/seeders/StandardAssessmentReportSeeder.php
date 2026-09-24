<?php

namespace Database\Seeders;

use App\Models\AssessmentStudentReport;
use App\Models\StandardAssessmentQuestion;
use App\Models\StandardAssessmentStudentAnswer;
use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDF;

/**
 * Seeds one `assessment_student_report` (assessment_type = 'standard') for
 * school1.student1, generated from the answers seeded by
 * StandardAssessmentStudentAnswerSeeder. Scoring/rubric logic is copied
 * exactly from Student\AssessmentController::prepareBaselineReportData()
 * and getRubricByScore() (a fixed threshold table, not AI-driven, unlike
 * RealQ) — per-category scores are summed from each answer's
 * option_{x}_score, then mapped to a rubric level. A real PDF is generated
 * via the same student.assessment.standard_report_pdf view the live
 * "Download Report" button uses. Requires StandardAssessmentStudentAnswerSeeder
 * to have already run.
 */
class StandardAssessmentReportSeeder extends Seeder
{
    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::with('school')->find(
            Students::where('user_id', $studentUser->id)->value('id')
        ) : null;

        if (!$student) {
            $this->command?->error('Student school1.student1@venturekids.test not found — run SchoolSeeder first.');
            return;
        }

        if (AssessmentStudentReport::where('student_id', $student->id)->where('assessment_type', 'standard')->exists()) {
            $this->command?->info('Standard assessment report already seeded.');
            return;
        }

        $answers = StandardAssessmentStudentAnswer::where('student_id', $student->id)->orderBy('id')->get();

        if ($answers->isEmpty()) {
            $this->command?->error('No standard assessment answers found — run StandardAssessmentStudentAnswerSeeder first.');
            return;
        }

        $questions = StandardAssessmentQuestion::with('category:id,category_name')
            ->whereIn('id', $answers->pluck('question_id')->unique())
            ->get()
            ->keyBy('id');

        $categoryScores = [];
        foreach ($answers as $answer) {
            $question = $questions->get($answer->question_id);
            if (!$question || empty($answer->selected_option)) {
                continue;
            }

            $scoreField = 'option_' . strtolower($answer->selected_option) . '_score';
            $earnedScore = (int) ($question->{$scoreField} ?? 0);
            $categoryName = optional($question->category)->category_name ?: 'Uncategorized';

            $categoryScores[$categoryName] = ($categoryScores[$categoryName] ?? 0) + $earnedScore;
        }

        $evaluationRows = [];
        foreach ($categoryScores as $categoryName => $totalScore) {
            $rubric = $this->getRubricByScore($totalScore);
            $evaluationRows[] = [
                'parameter' => $categoryName,
                'total_score' => $totalScore,
                'rubric_level' => $rubric['level'],
                'evidence' => $rubric['evidence'],
            ];
        }

        $attemptedOn = Carbon::parse($answers->max('answered_at'))->toDateString();

        $pdf = PDF::loadView('student.assessment.standard_report_pdf', [
            'student' => $student,
            'evaluationRows' => $evaluationRows,
            'schoolLogoPath' => public_path('asset/images/kids-tm-logo.png'),
            'attemptedOn' => $attemptedOn,
        ])->setPaper('a4', 'portrait');

        $tenantId = optional($student->school)->tenant_id ?: 'common';
        $fileName = Str::random(12) . dechex(time()) . '.pdf';
        $reportPath = trim($tenantId, '/') . '/student/reports/standard_assessment/' . $fileName;
        $absoluteDir = public_path('tenants/' . trim($tenantId, '/') . '/student/reports/standard_assessment');

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0755, true);
        }

        File::put(public_path('tenants/' . $reportPath), $pdf->output());

        AssessmentStudentReport::create([
            'student_id' => $student->id,
            'student_grade_id' => $student->student_grade_id,
            'assessment_type' => 'standard',
            'report_path' => $reportPath,
        ]);

        $this->command?->info("Seeded 1 standard assessment report for {$student->name}.");
    }

    /** Copied from Student\AssessmentController::getRubricByScore() to match the real thresholds exactly. */
    private function getRubricByScore(int $totalScore): array
    {
        if ($totalScore <= 4) {
            return ['level' => 'Beginning', 'evidence' => 'Consider seeking resources or training to develop this skill.'];
        }

        if ($totalScore <= 6) {
            return ['level' => 'Developing', 'evidence' => 'You have a basic level of this skill, but there is room for growth.'];
        }

        if ($totalScore <= 8) {
            return ['level' => 'Promising', 'evidence' => 'You are competent in this skill but can still improve.'];
        }

        if ($totalScore <= 11) {
            return ['level' => 'Proficient', 'evidence' => 'You are competent in this skill but can still improve.'];
        }

        return ['level' => 'Excellent', 'evidence' => 'You have a strong proficiency in this skill.'];
    }
}
