<?php

namespace Database\Seeders;

use App\Models\RealQAssessmentSubject;
use App\Models\RealQAssessmentTopic;
use App\Models\StudentBoard;
use Illuminate\Database\Seeder;

/**
 * Seeds the `realq_assessment_topics` table with 1 approved topic under
 * the "Entrepreneurial Thinking" subject, for StudentGrade 1 / CBSE /
 * India — RealQAssessmentQuestionSeeder generates all 5 of its questions
 * against this single topic. `prompt`/`raw_response` mirror the real
 * AI-generation flow's columns with plausible hand-authored values (no
 * OpenAI call is made by this seeder). Requires RealQAssessmentSubjectSeeder
 * and StudentBoardSeeder to have already run.
 */
class RealQAssessmentTopicSeeder extends Seeder
{
    private const TOPICS = [
        'Spotting a Problem Worth Solving',
    ];

    public function run(): void
    {
        $subject = RealQAssessmentSubject::where('name', 'Entrepreneurial Thinking')->first();
        $board = StudentBoard::where('name', 'CBSE')->first();

        if (!$subject || !$board) {
            $this->command?->error('Subject/board not found — run RealQAssessmentSubjectSeeder and StudentBoardSeeder first.');
            return;
        }

        foreach (self::TOPICS as $topic) {
            RealQAssessmentTopic::firstOrCreate(
                ['topic' => $topic],
                [
                    'grade_id' => 1, // StudentGrade "Grade1"
                    'board_id' => $board->id,
                    'country_id' => 1, // India
                    'subject_id' => $subject->id,
                    'moderation_status' => 'approved',
                    'prompt' => "Generate an age-appropriate RealQ scenario topic about: {$topic}.",
                    'raw_response' => "Topic generated and approved for demo/seed purposes: {$topic}.",
                    'created_by' => 1,
                ]
            );
        }
    }
}
