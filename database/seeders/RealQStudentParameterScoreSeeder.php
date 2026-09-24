<?php

namespace Database\Seeders;

use App\Models\AssessmentStudentReport;
use App\Models\RealQAssessmentParameter;
use App\Models\RealQStudentParameterScore;
use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds `realq_assessment_student_parameter_scores` from the report built
 * by AssessmentStudentReportSeeder — one row per parameter, mirroring
 * RealQReportParser::storeRealQParameterScores() (the real logic that runs
 * right after a report is generated). Requires AssessmentStudentReportSeeder
 * to have already run.
 */
class RealQStudentParameterScoreSeeder extends Seeder
{
    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::where('user_id', $studentUser->id)->first() : null;
        $report = $student ? AssessmentStudentReport::where('student_id', $student->id)->where('assessment_type', 'realq')->first() : null;
        $parameterIds = RealQAssessmentParameter::pluck('id', 'name');

        if (!$student || !$report || empty($report->report_data['performance_rows'])) {
            $this->command?->error('Student or RealQ report not found — run AssessmentStudentReportSeeder first.');
            return;
        }

        foreach ($report->report_data['performance_rows'] as $row) {
            $parameterId = $parameterIds[$row['parameter']] ?? null;

            if (!$parameterId) {
                continue;
            }

            RealQStudentParameterScore::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'assessment_report_id' => $report->id,
                    'parameter_id' => $parameterId,
                ],
                [
                    'school_id' => $student->school_id,
                    'student_grade_id' => $student->student_grade_id,
                    'rubric_id' => $row['rubric_id'] ?? null,
                    'generate_time_rubric_id' => $row['rubric_id'] ?? null,
                ]
            );
        }

        $this->command?->info('Seeded RealQ parameter scores for school1.student1.');
    }
}
