<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\QuizQuestions;
use App\Models\Stream;
use App\Models\Studentscontent;
use Illuminate\Database\Seeder;

/**
 * Seeds the `studentscontents` table with several original sessions per
 * stream (not just one), each with a YouTube video link, plus 2 MCQ quiz
 * questions per session in `quiz_questions` (content_type = 'session').
 * Every primary grade (is_primary = 1, as defined by GradeSeeder — currently
 * Curiosity Spark, Idea Forge, Venture Lab, Growth Studio, Impact Summit)
 * gets 7 sessions total across its 2 streams. Requires GradeSeeder and
 * StudentStreamSeeder to have already run.
 */
class StudentContentSeeder extends Seeder
{
    /** A real, always-public placeholder video reused across every seeded session. */
    private const VIDEO_URL = 'https://www.youtube.com/watch?v=jNQXAC9IVRw';

    private const SKILL_POOL = [
        'Creativity', 'Curiosity', 'Observation', 'Reflection', 'Critical Thinking',
        'Organization', 'Collaboration', 'Planning', 'Visualization', 'Communication',
        'Building', 'Research', 'Resourcefulness', 'Perseverance', 'Storytelling',
        'Design', 'Financial Literacy', 'Strategy', 'Public Speaking', 'Confidence',
    ];

    /** grade => [ stream title => [ [title, skill], ... ] ] */
    private const CONTENT_MAP = [
        'Curiosity Spark' => [
            'What is an Idea?' => [
                ['title' => 'What is an Idea?', 'skill' => 'Creativity'],
                ['title' => 'Where Do Ideas Come From?', 'skill' => 'Curiosity'],
                ['title' => 'Everyday Inventions We Take for Granted', 'skill' => 'Observation'],
                ['title' => 'Idea Journal: Capturing Your Thoughts', 'skill' => 'Reflection'],
            ],
            'Spotting Everyday Problems' => [
                ['title' => 'Spotting Everyday Problems', 'skill' => 'Observation'],
                ['title' => 'Problems at Home vs Problems at School', 'skill' => 'Critical Thinking'],
                ['title' => 'Turning Annoyances into Opportunities', 'skill' => 'Creativity'],
            ],
        ],
        'Idea Forge' => [
            'Brainstorming Like a Pro' => [
                ['title' => 'Brainstorming Like a Pro', 'skill' => 'Creativity'],
                ['title' => 'Mind Mapping Your Ideas', 'skill' => 'Organization'],
                ['title' => "The Power of \"Yes, And\"", 'skill' => 'Collaboration'],
                ['title' => 'Combining Two Ideas Into One', 'skill' => 'Creativity'],
            ],
            'From Idea to Concept' => [
                ['title' => 'From Idea to Concept', 'skill' => 'Planning'],
                ['title' => 'Sketching Your First Concept', 'skill' => 'Visualization'],
                ['title' => 'Getting Feedback on Your Concept', 'skill' => 'Communication'],
            ],
        ],
        'Venture Lab' => [
            'Build Your First Prototype' => [
                ['title' => 'Build Your First Prototype', 'skill' => 'Building'],
                ['title' => 'Choosing the Right Materials', 'skill' => 'Research'],
                ['title' => 'Prototyping on a Budget', 'skill' => 'Resourcefulness'],
                ['title' => 'From Sketch to Model', 'skill' => 'Building'],
            ],
            'Testing with Real Users' => [
                ['title' => 'Testing with Real Users', 'skill' => 'Communication'],
                ['title' => 'Asking Good Feedback Questions', 'skill' => 'Communication'],
                ['title' => 'Iterating After Feedback', 'skill' => 'Perseverance'],
            ],
        ],
        'Growth Studio' => [
            'Telling Your Brand Story' => [
                ['title' => 'Telling Your Brand Story', 'skill' => 'Storytelling'],
                ['title' => 'Naming Your Venture', 'skill' => 'Creativity'],
                ['title' => 'Designing a Simple Logo', 'skill' => 'Design'],
                ['title' => 'Writing Your Elevator Pitch', 'skill' => 'Communication'],
            ],
            'Pricing for Growth' => [
                ['title' => 'Pricing for Growth', 'skill' => 'Financial Literacy'],
                ['title' => 'Understanding Costs vs Price', 'skill' => 'Financial Literacy'],
                ['title' => 'Offering Discounts and Bundles', 'skill' => 'Strategy'],
            ],
        ],
        'Impact Summit' => [
            'Pitch Like a Founder' => [
                ['title' => 'Pitch Like a Founder', 'skill' => 'Public Speaking'],
                ['title' => 'Structuring a Winning Pitch', 'skill' => 'Planning'],
                ['title' => 'Handling Tough Questions', 'skill' => 'Confidence'],
                ['title' => 'Practicing Your Delivery', 'skill' => 'Public Speaking'],
            ],
            'Measuring Real-World Impact' => [
                ['title' => 'Measuring Real-World Impact', 'skill' => 'Reflection'],
                ['title' => 'Collecting Feedback from Your Community', 'skill' => 'Communication'],
                ['title' => 'Planning Your Next Steps', 'skill' => 'Planning'],
            ],
        ],
    ];

    public function run(): void
    {
        $createdCount = 0;
        $questionCount = 0;

        foreach (self::CONTENT_MAP as $gradeName => $streamMap) {
            $grade = Grade::where('grade', $gradeName)->first();

            if (!$grade) {
                $this->command?->error("Grade '{$gradeName}' not found — run GradeSeeder first.");
                continue;
            }

            // All titles in this grade, used as cross-referenced distractors below.
            $allTitlesInGrade = collect($streamMap)->flatten(1)->pluck('title')->all();

            foreach ($streamMap as $streamTitle => $items) {
                $stream = Stream::where('title', $streamTitle)->where('agegroup_id', $grade->id)->first();

                if (!$stream) {
                    $this->command?->error("Stream '{$streamTitle}' not found for {$gradeName} — run StudentStreamSeeder first.");
                    continue;
                }

                foreach ($items as $index => $item) {
                    $content = Studentscontent::firstOrCreate(
                        ['stream_id' => $stream->id, 'agegroup_id' => $grade->id, 'title' => $item['title']],
                        [
                            'description' => "A guided session exploring: {$item['title']}. Understand the core idea through discussion and hands-on activity. By the end, students can explain and apply the concept.",
                            'video_url' => self::VIDEO_URL,
                            'display_order_id' => $index + 1,
                            'is_publish' => 1,
                        ]
                    );

                    if ($content->wasRecentlyCreated) {
                        $createdCount++;
                    } elseif (empty($content->video_url)) {
                        // Backfill video_url on content rows seeded before this
                        // seeder added video links (the first item per stream,
                        // from the original run).
                        $content->video_url = self::VIDEO_URL;
                        $content->save();
                    }

                    $alreadySeeded = QuizQuestions::where('content_id', $content->id)
                        ->where('content_type', 'session')
                        ->where('question', 'What is this session mainly about?')
                        ->exists();

                    if (!$alreadySeeded) {
                        $questionCount += $this->seedQuestionsForContent($content, $item, $allTitlesInGrade);
                    }
                }
            }
        }

        $this->command?->info("Seeded {$createdCount} student content sessions with {$questionCount} quiz questions.");
    }

    /**
     * Creates 2 MCQ questions for a content item:
     *  - Q1: "what is this session about?" — correct answer is the session's
     *    own title, distractors are 2 other session titles from the same
     *    grade (cross-referenced, so they're plausible but wrong).
     *  - Q2: "which skill does this session build?" — correct answer is the
     *    item's tagged skill, distractors are 3 other skills from the pool.
     */
    private function seedQuestionsForContent(Studentscontent $content, array $item, array $allTitlesInGrade): int
    {
        $distractorTitles = collect($allTitlesInGrade)
            ->reject(fn ($title) => $title === $item['title'])
            ->shuffle()
            ->take(3)
            ->values();

        $topicOptions = collect([$item['title']])->concat($distractorTitles)->shuffle()->values();

        QuizQuestions::firstOrCreate(
            ['content_id' => $content->id, 'content_type' => 'session', 'question' => "What is this session mainly about?"],
            [
                'option1' => $topicOptions[0],
                'option2' => $topicOptions[1],
                'option3' => $topicOptions[2],
                'option4' => $topicOptions[3],
                'correct_option' => $this->optionLetterFor($topicOptions, $item['title']),
                'isDisabled' => 0,
                'created_by' => 1,
            ]
        );

        $distractorSkills = collect(self::SKILL_POOL)
            ->reject(fn ($skill) => $skill === $item['skill'])
            ->shuffle()
            ->take(3)
            ->values();

        $skillOptions = collect([$item['skill']])->concat($distractorSkills)->shuffle()->values();

        QuizQuestions::firstOrCreate(
            ['content_id' => $content->id, 'content_type' => 'session', 'question' => "Which skill does the \"{$item['title']}\" session mainly help you practice?"],
            [
                'option1' => $skillOptions[0],
                'option2' => $skillOptions[1],
                'option3' => $skillOptions[2],
                'option4' => $skillOptions[3],
                'correct_option' => $this->optionLetterFor($skillOptions, $item['skill']),
                'isDisabled' => 0,
                'created_by' => 1,
            ]
        );

        return 2;
    }

    private function optionLetterFor($options, $correctValue): string
    {
        $letters = ['A', 'B', 'C', 'D'];

        return $letters[$options->search($correctValue)] ?? 'A';
    }
}
