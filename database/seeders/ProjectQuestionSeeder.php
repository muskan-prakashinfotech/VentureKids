<?php

namespace Database\Seeders;

use App\Models\ProjectQuestion;
use App\Models\ProjectSection;
use Illuminate\Database\Seeder;

/**
 * Seeds the `project_questions` table with 2-3 original questions per
 * section created by ProjectSectionSeeder. Requires ProjectSectionSeeder to
 * have already run.
 */
class ProjectQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questionsBySection = [
            'Spark & Purpose' => [
                // Named "Project Title" (not "Project Name") deliberately:
                // StudentProjectController::attachProjectSummaries() picks the
                // my-projects card title from whichever answered question's
                // field_text contains the substring "title".
                ['text' => 'Project Title', 'type' => 'input'],
                ['text' => 'What problem are you trying to solve?', 'type' => 'textarea'],
                ['text' => 'Who is this project for?', 'type' => 'input'],
                ['text' => "What's your big idea, in one sentence?", 'type' => 'input'],
            ],
            'Explore & Research' => [
                ['text' => 'What did you discover while researching your topic?', 'type' => 'textarea'],
                ['text' => 'What similar ideas or projects already exist?', 'type' => 'textarea'],
                ['text' => 'What makes your idea different?', 'type' => 'input'],
            ],
            'Build & Create' => [
                ['text' => 'What materials or tools did you use?', 'type' => 'input'],
                ['text' => 'Describe how you built it, step by step.', 'type' => 'textarea'],
                ['text' => 'Upload a photo of your creation', 'type' => 'file', 'allow_attachments' => 1, 'allowed_types' => 'jpg,jpeg,png,pdf'],
            ],
            'Skills & Growth' => [
                ['text' => 'What skills did you practice on this project?', 'type' => 'input'],
                ['text' => 'What was the hardest part, and how did you push through?', 'type' => 'textarea'],
            ],
            'Share & Present' => [
                ['text' => 'How did you design or present your project?', 'type' => 'textarea'],
                ['text' => "What's the story behind your project?", 'type' => 'textarea'],
                ['text' => 'How did you share it with others?', 'type' => 'input'],
            ],
            'Venture Thinking' => [
                ['text' => 'What value does your project create for others?', 'type' => 'textarea'],
                ['text' => 'Could this become a real business? How?', 'type' => 'textarea'],
                ['text' => 'What would you charge or offer, and why?', 'type' => 'input'],
            ],
            'Reflect & Next Steps' => [
                ['text' => 'What feedback did you receive?', 'type' => 'textarea'],
                ['text' => 'What did you learn about yourself?', 'type' => 'textarea'],
                ['text' => 'What would you do differently next time?', 'type' => 'input'],
            ],
        ];

        foreach ($questionsBySection as $sectionTitle => $questions) {
            $section = ProjectSection::where('section_title', $sectionTitle)->first();

            if (!$section) {
                $this->command?->error("Section '{$sectionTitle}' not found — run ProjectSectionSeeder first.");
                continue;
            }

            foreach ($questions as $index => $question) {
                $displayOrder = $index + 1;

                $projectQuestion = ProjectQuestion::firstOrCreate(
                    ['project_section_id' => $section->id, 'field_text' => $question['text']],
                    [
                        'field_type' => $question['type'],
                        'help_text' => null,
                        'is_required' => 1,
                        'allow_attachments' => $question['allow_attachments'] ?? 0,
                        'allowed_types' => $question['allowed_types'] ?? null,
                        'allowed_multiples' => 0,
                        'display_order' => $displayOrder,
                        'status' => 1,
                    ]
                );

                // Keep display_order in sync on re-runs too, e.g. when a new
                // question (like "Project Name") is inserted ahead of
                // existing ones and their ordering needs to shift.
                if ((int) $projectQuestion->display_order !== $displayOrder) {
                    $projectQuestion->display_order = $displayOrder;
                    $projectQuestion->save();
                }
            }
        }
    }
}
