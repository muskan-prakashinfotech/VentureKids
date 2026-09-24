<?php

namespace Database\Seeders;

use App\Models\StandardAssessmentCategory;
use App\Models\StandardAssessmentQuestion;
use App\Models\StandardAssessmentStudentAnswer;
use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds `standard_assessment_student_answers` — a full, submitted attempt
 * for school1.student1 across all 6 questions from
 * StandardAssessmentQuestionSeeder. Answers are realistic (not all the
 * highest-scoring option) so StandardAssessmentReportSeeder produces a
 * genuine "Proficient" result rather than a perfect score. Requires
 * StandardAssessmentQuestionSeeder and SchoolSeeder to have already run.
 */
class StandardAssessmentStudentAnswerSeeder extends Seeder
{
    /** question_text => selected option letter */
    private const SELECTED_OPTIONS = [
        'When explaining an idea to a friend, I...' => 'B',
        'If someone disagrees with my idea, I...' => 'B',
        'When presenting to a group, I...' => 'D',
        'When I face a new problem, I...' => 'A',
        "If my first solution doesn't work, I..." => 'B',
        'Before starting a project, I...' => 'D',
    ];

    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::where('user_id', $studentUser->id)->first() : null;
        $categoryIds = StandardAssessmentCategory::where('grade_id', 1)->pluck('id');
        $questions = StandardAssessmentQuestion::whereIn('category_id', $categoryIds)->where('active', 1)->get();

        if (!$student || $questions->isEmpty()) {
            $this->command?->error('Student school1.student1@venturekids.test or questions not found — run SchoolSeeder and StandardAssessmentQuestionSeeder first.');
            return;
        }

        $startedAt = Carbon::now()->subDays(7)->setTime(9, 30);

        foreach ($questions as $index => $question) {
            $answeredAt = $startedAt->copy()->addMinutes($index * 2 + 2);

            StandardAssessmentStudentAnswer::firstOrCreate(
                ['student_id' => $student->id, 'question_id' => $question->id],
                [
                    'selected_option' => self::SELECTED_OPTIONS[$question->question_text] ?? 'A',
                    'is_submitted' => 1,
                    'started_at' => $startedAt,
                    'answered_at' => $answeredAt,
                ]
            );
        }

        $this->command?->info("Seeded {$questions->count()} standard assessment answers for school1.student1.");
    }
}
