<?php

namespace Database\Seeders;

use App\Models\Trainercontent;
use App\Models\Trainerlavel;
use App\Models\Trainerstream;
use Illuminate\Database\Seeder;

/**
 * Seeds the `trainercontents` table with several original facilitation
 * sessions per stream (not just one), each with a real video/drive link,
 * mirroring the expansion done for students in StudentContentSeeder. Every
 * trainer level gets 7 sessions total across its 2 streams (see
 * TrainerStreamSeeder), spread 4 + 3.
 *
 * Note: unlike student content, trainer content has no separate quiz-
 * question system to attach — `quiz_questions.content_type` is a strict
 * enum('session','weekly_challenge','daily_challenge') with no trainer
 * variant, confirmed by checking the schema and every controller that
 * writes to it. The closest existing equivalent already on this table —
 * `question_access_knowledge`, a prior-knowledge prompt — is filled in for
 * every session below instead.
 *
 * Requires TrainerLevelSeeder and TrainerStreamSeeder to have already run.
 */
class TrainerContentSeeder extends Seeder
{
    /**
     * A real, public facilitation-skills training video ("How To Be A Great
     * Facilitator - The 8 Facilitation Skills You Need", AJ&Smart), reused
     * across every seeded session — actually relevant to trainer
     * facilitation content, unlike the earlier generic placeholder.
     */
    private const VIDEO_URL = 'https://www.youtube.com/watch?v=5kPP07jY_rQ';

    /** trainer level => [ stream title => [ session titles ] ] */
    private const CONTENT_MAP = [
        'Foundations Facilitator' => [
            'Facilitating Curiosity Circles' => [
                'Facilitating Curiosity Circles',
                'Opening Questions That Spark Curiosity',
                'Managing Group Discussions',
                'Wrapping Up a Curiosity Circle',
            ],
            'Classroom Icebreakers for Young Founders' => [
                'Classroom Icebreakers for Young Founders',
                'Icebreakers for Shy Students',
                'Reading the Room: Adjusting Energy Levels',
            ],
        ],
        'Ideation Coach' => [
            'Guiding Brainstorm Sessions' => [
                'Guiding Brainstorm Sessions',
                'Encouraging Wild Ideas Without Judgment',
                'Keeping Brainstorms on Track',
                'Capturing Ideas Visually',
            ],
            'Coaching Idea Selection' => [
                'Coaching Idea Selection',
                'Helping Students Let Go of Weak Ideas',
                'Building Consensus in a Group',
            ],
        ],
        'Prototype Mentor' => [
            'Hands-On Prototyping Techniques' => [
                'Hands-On Prototyping Techniques',
                'Choosing Materials for Quick Prototypes',
                'Safety Basics in the Maker Space',
                'Prototyping Digital vs Physical Ideas',
            ],
            'Feedback Loops & Iteration' => [
                'Feedback Loops & Iteration',
                'Teaching Students to Take Feedback Well',
                'Facilitating a Rapid Iteration Round',
            ],
        ],
        'Pitch & Storytelling Coach' => [
            'Building a Pitch Deck' => [
                'Building a Pitch Deck',
                'Structuring a Pitch for Young Audiences',
                'Coaching Confident Delivery',
                'Handling Nerves Before Presenting',
            ],
            'Storytelling Frameworks' => [
                'Storytelling Frameworks',
                "Using the Hero's Journey with Kids",
                "Teaching \"Show, Don't Tell\"",
            ],
        ],
        'Digital Tools Facilitator' => [
            'Intro to No-Code Tools' => [
                'Intro to No-Code Tools',
                'Choosing the Right Tool for the Task',
                'Troubleshooting Common Tech Hiccups',
                'Digital Safety Basics for Young Builders',
            ],
            'Digital Collaboration Basics' => [
                'Digital Collaboration Basics',
                'Running a Remote Brainstorm',
                'Sharing Work Online Responsibly',
            ],
        ],
        'Community Impact Coach' => [
            'Community Needs Assessment' => [
                'Community Needs Assessment',
                'Interviewing Community Members',
                'Spotting Patterns in Community Feedback',
                'Prioritizing Which Needs to Address',
            ],
            'Designing for Social Impact' => [
                'Designing for Social Impact',
                'Measuring Impact in Simple Terms',
                'Partnering with Local Organizations',
            ],
        ],
        'Financial Skills Coach' => [
            'Budgeting Basics for Young Founders' => [
                'Budgeting Basics for Young Founders',
                'Teaching the Difference Between Needs and Wants',
                'Simple Tools for Tracking Spending',
                'Talking About Money Comfortably',
            ],
            'Understanding Revenue & Costs' => [
                'Understanding Revenue & Costs',
                'Explaining Profit in Kid-Friendly Terms',
                'Pricing Practice Activities',
            ],
        ],
        'Leadership & Wellbeing Coach' => [
            'Building Confident Leaders' => [
                'Building Confident Leaders',
                'Encouraging Quiet Students to Lead',
                'Teaching Healthy Disagreement',
                'Recognizing Leadership Growth',
            ],
            'Wellbeing in Entrepreneurship' => [
                'Wellbeing in Entrepreneurship',
                'Spotting Burnout in Young Founders',
                'Building in Breaks and Reflection Time',
            ],
        ],
    ];

    public function run(): void
    {
        $createdCount = 0;

        foreach (self::CONTENT_MAP as $levelName => $streamMap) {
            $level = Trainerlavel::where('grade', $levelName)->first();

            if (!$level) {
                $this->command?->error("Trainer level '{$levelName}' not found — run TrainerLevelSeeder first.");
                continue;
            }

            foreach ($streamMap as $streamTitle => $sessionTitles) {
                $stream = Trainerstream::where('title', $streamTitle)->where('agegroup_id', $level->id)->first();

                if (!$stream) {
                    $this->command?->error("Stream '{$streamTitle}' not found for {$levelName} — run TrainerStreamSeeder first.");
                    continue;
                }

                foreach ($sessionTitles as $index => $title) {
                    $content = Trainercontent::firstOrCreate(
                        ['stream_id' => $stream->id, 'agegroup_id' => $level->id, 'title' => $title],
                        [
                            'description' => "A facilitation guide for the session: {$title}.",
                            'video_url' => self::VIDEO_URL,
                            'learning_object' => "Equip trainers to confidently lead \"{$title}\".",
                            'outcome_session' => "By the end of this session, trainers can facilitate \"{$title}\" independently.",
                            'question_access_knowledge' => "What prior facilitation experience relates to \"{$title}\"?",
                            'introduce_topic_student' => "Open the session by introducing: {$title}.",
                            'related_activity_one' => 'Live demo/role-play with the group.',
                            'related_activity_two' => 'Peer facilitation practice.',
                            'vocabulary' => 'Facilitation, Coaching, Mentorship, Feedback',
                            'tips_of_parents' => 'Share a short recap note with parents after the session.',
                            'display_order_id' => $index + 1,
                            'is_publish' => 1,
                        ]
                    );

                    if ($content->wasRecentlyCreated) {
                        $createdCount++;
                    } elseif (empty($content->video_url)) {
                        // Backfill video_url on content rows seeded before
                        // this seeder added video links (the first item per
                        // stream, from the original single-item run).
                        $content->video_url = self::VIDEO_URL;
                        $content->save();
                    }
                }
            }
        }

        $this->command?->info("Seeded {$createdCount} new trainer facilitation sessions.");
    }
}
