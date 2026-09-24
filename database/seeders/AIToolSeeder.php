<?php

namespace Database\Seeders;

use App\Models\AITool;
use App\Models\AIToolSubcategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the `ai_tools` / `ai_tool_subcategories` tables (the student
 * "Workspace" AI tool catalog, see Student\WorkspaceController) with 9
 * original VentureKids tools, named distinctly from kids_new's catalog
 * (Prototype My Idea, My First Business Plan Generator, Set My Goal, My
 * StrengthsFinder, Test My Pitch, Tinkering Challenge, Content Recommend,
 * Generate My Report, LifeQuest). Two map to real, already-built features
 * (Prototype, Business Plan) and keep their working route name in `url`;
 * the other 7 are original concepts left unrouted (empty `url`), matching
 * kids_new's own convention of listing not-yet-built tools with a blank url.
 */
class AIToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            [
                'title' => 'Bring My Idea to Life',
                'url' => 'student.prototype',
                'image' => 'asset/prototypes/bring-my-idea-to-life.png',
                'description' => 'Turn a sketch or concept into an AI-generated visual prototype of your idea.',
                'status' => 1,
            ],
            [
                'title' => 'Venture Plan Builder',
                'url' => 'student.businessplan.index',
                'image' => 'asset/prototypes/venture-plan-builder.png',
                'description' => 'Answer a few guided questions and get a simple, structured first business plan for your venture.',
                'status' => 1,
            ],
            [
                'title' => 'My Superpower Finder',
                'url' => '',
                'image' => 'asset/prototypes/my-superpower-finder.png',
                'description' => 'Discover what makes you shine — AI highlights your core strengths based on your activity.',
                'status' => 1,
            ],
            [
                'title' => 'Pitch Booster',
                'url' => '',
                'image' => 'asset/prototypes/pitch-booster.png',
                'description' => 'Practice your pitch and get instant AI feedback on clarity, confidence, and delivery.',
                'status' => 1,
            ],
            [
                'title' => 'Spark Challenge Generator',
                'url' => '',
                'image' => 'asset/prototypes/spark-challenge-generator.png',
                'description' => 'Spark your creativity with a hands-on entrepreneurial challenge generated on any topic you choose.',
                'status' => 1,
            ],
            [
                'title' => 'Discovery Feed',
                'url' => '',
                'image' => 'asset/prototypes/discovery-feed.png',
                'description' => 'AI-curated articles, videos, and activities picked just for you as you explore entrepreneurship.',
                'status' => 1,
            ],
            [
                'title' => 'Milestone Snapshot',
                'url' => '',
                'image' => 'asset/prototypes/milestone-snapshot.png',
                'description' => 'A personalized, AI-generated report summarizing your learning journey, strengths, and achievements.',
                'status' => 1,
            ],
            [
                'title' => 'Founder\'s Journey',
                'url' => '',
                'image' => 'asset/prototypes/founders-journey.png',
                'description' => 'A personalized entrepreneurial simulation game — build ventures, make decisions, and grow over time.',
                'status' => 1,
            ],
            [
                'title' => 'Dream to Plan',
                'url' => '',
                'image' => 'asset/prototypes/dream-to-plan.png',
                'description' => 'Turn big dreams into achievable steps with a personalized action plan built to reach your goals.',
                'status' => 1,
            ],
        ];

        foreach ($tools as $index => $tool) {
            $aiTool = AITool::firstOrCreate(
                ['title' => $tool['title']],
                ['url' => $tool['url']]
            );

            AIToolSubcategory::firstOrCreate(
                ['ai_tool_id' => $aiTool->id],
                [
                    'name' => $tool['title'],
                    'image' => $tool['image'],
                    'description' => $tool['description'],
                    'display_order' => $index + 1,
                    'status' => $tool['status'],
                ]
            );
        }
    }
}
