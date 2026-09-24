<?php

namespace Database\Seeders;

use App\Models\ProjectQuestion;
use App\Models\ProjectSection;
use App\Models\StudentProject;
use App\Models\StudentProjectAnswer;
use App\Models\StudentProjectAttachment;
use App\Models\StudentProjectFeedback;
use App\Models\Students;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds 3 full StudentProject submissions — one draft, one submitted
 * (awaiting feedback), and one published (trainer feedback released) — for
 * school1.student1@venturekids.test ("Student 1-1" of Demo School 1). Every
 * project answers every question from ProjectQuestionSeeder with a
 * coherent, original project story ("GreenBite", a reusable snack wrap),
 * plus a real attachment for the file-upload question.
 *
 * Status/label semantics taken directly from the student-facing
 * resources/views/student/project/my_projects.blade.php $resolveStatus
 * mapping (0/1 = Draft, 2-3 = Improving, 4 = Submitted (or "Feedback
 * Received" once a trainer leaves is_publish=1 feedback), 5 = Approved,
 * 6 = Published). Requires ProjectSectionSeeder, ProjectQuestionSeeder and
 * SchoolSeeder to have already run.
 */
class StudentProjectSeeder extends Seeder
{
    /** One shared, coherent set of answers (a reusable snack-wrap project) reused across all 3 submissions. */
    private const ANSWERS = [
        'Project Title' => 'GreenBite',
        'What problem are you trying to solve?' => 'Too many single-use plastic wrappers get thrown away after school snacks, creating unnecessary waste.',
        'Who is this project for?' => 'Students and parents who want an easy, eco-friendly way to pack snacks for school.',
        "What's your big idea, in one sentence?" => 'GreenBite is a reusable, washable snack wrap that replaces plastic cling film.',
        'What did you discover while researching your topic?' => 'I learned that a single family can throw away hundreds of plastic wrappers every year.',
        'What similar ideas or projects already exist?' => 'There are reusable beeswax wraps sold online, but most are expensive and not made for kids.',
        'What makes your idea different?' => 'GreenBite is affordable, comes in fun designs, and is sized just right for school snacks.',
        'What materials or tools did you use?' => 'Cotton fabric, beeswax, a fabric iron, and parchment paper.',
        'Describe how you built it, step by step.' => 'I cut the fabric to size, melted beeswax onto it with an iron, let it cool, then trimmed the edges.',
        'What skills did you practice on this project?' => 'Sewing, measuring, planning, and basic budgeting.',
        'What was the hardest part, and how did you push through?' => 'Getting the wax to spread evenly was tricky, so I practiced on scrap fabric first.',
        'How did you design or present your project?' => 'I made a simple poster with before/after waste comparisons and a sample wrap to touch.',
        "What's the story behind your project?" => 'After noticing how much wrapper trash was in my school bin, I wanted to make a reusable option kids would actually like.',
        'How did you share it with others?' => 'I presented it to my class and let classmates try wrapping their own snacks.',
        'What value does your project create for others?' => 'It saves families money over time and cuts down on classroom waste.',
        'Could this become a real business? How?' => 'Yes — I could sell packs of wraps in different sizes and designs at school fairs.',
        'What would you charge or offer, and why?' => "I'd charge $5 per wrap since the materials cost about $2 and it's reusable for a year.",
        'What feedback did you receive?' => 'My trainer said the wraps were creative but suggested adding care instructions.',
        'What did you learn about yourself?' => "I learned I enjoy hands-on building more than I expected, and I'm patient with fiddly tasks.",
        'What would you do differently next time?' => "I'd test more fabric types before choosing the final one.",
    ];

    public function run(): void
    {
        $studentUser = User::where('email', 'school1.student1@venturekids.test')->first();
        $student = $studentUser ? Students::where('user_id', $studentUser->id)->first() : null;
        $questions = ProjectQuestion::with('section')->orderBy('project_section_id')->orderBy('display_order')->get();
        $fileQuestion = $questions->firstWhere('field_type', 'file');
        $tenantId = $student ? optional(\App\Models\School::find($student->school_id))->tenant_id : null;
        $trainerId = 1;

        if (!$student || $questions->isEmpty()) {
            $this->command?->error('Student school1.student1@venturekids.test or project questions not found — run SchoolSeeder and ProjectQuestionSeeder first.');
            return;
        }

        $states = [
            'draft' => ['status' => 1, 'is_submitted' => 0, 'photo' => 'vk-project-photo-draft.png', 'feedback' => false],
            'submitted' => ['status' => 4, 'is_submitted' => 1, 'photo' => 'vk-project-photo-submitted.png', 'feedback' => false],
            'published' => ['status' => 6, 'is_submitted' => 1, 'photo' => 'vk-project-photo-published.png', 'feedback' => true],
        ];

        foreach ($states as $label => $config) {
            // improvement_comments doubles as a de-dupe marker per state, since
            // multiple StudentProject rows for the same student are otherwise
            // indistinguishable (no unique key on student_id).
            $marker = "[seed:{$label}]";

            $studentProject = StudentProject::firstOrCreate(
                ['student_id' => $student->id, 'improvement_comments' => $marker],
                [
                    'status' => $config['status'],
                    'is_submitted' => $config['is_submitted'],
                    'published_sections' => $config['feedback'] ? ProjectSection::pluck('id')->all() : null,
                ]
            );

            foreach ($questions as $question) {
                if ($question->id === optional($fileQuestion)->id) {
                    if ($tenantId) {
                        StudentProjectAttachment::firstOrCreate(
                            ['student_project_id' => $studentProject->id, 'project_question_id' => $question->id],
                            [
                                'file_path' => $tenantId . '/student/project/attachments/' . $config['photo'],
                                'file_name' => $config['photo'],
                                // 'images' (plural) — matches the exact string
                                // StudentProjectController::attachProjectSummaries()
                                // filters on when picking a project's display
                                // image; 'image' (singular) never matches.
                                'file_type' => 'images',
                            ]
                        );
                    }
                    continue;
                }

                $answerText = self::ANSWERS[$question->field_text] ?? null;
                if ($answerText === null) {
                    continue;
                }

                StudentProjectAnswer::firstOrCreate(
                    ['student_project_id' => $studentProject->id, 'project_question_id' => $question->id],
                    ['answer_text' => $answerText]
                );
            }

            if ($config['feedback']) {
                StudentProjectFeedback::firstOrCreate(
                    ['student_project_id' => $studentProject->id, 'trainer_id' => $trainerId],
                    [
                        'public_note' => 'Great work on GreenBite! Your research and prototype really show you understand the problem you set out to solve.',
                        'private_suggestions' => 'Consider adding washing/care instructions and testing the wrap with younger students for extra feedback.',
                        'smart_score' => 85,
                        'is_publish' => 1,
                    ]
                );
            }

            $this->command?->info("Seeded {$label} project (student_project_id={$studentProject->id}) for school1.student1.");
        }
    }
}
