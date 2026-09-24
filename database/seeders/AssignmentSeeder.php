<?php

namespace Database\Seeders;

use App\Models\AssingmentFiles;
use App\Models\School;
use App\Models\StudentCommunications;
use App\Models\Studentscontent;
use App\Models\Stream;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the `assignments` table with every field populated, covering both
 * assignment categories the app supports (see
 * Backend\StudentCommunication::saveAssignment()):
 *  - 'facilitated'   : trainer-led, requires attached files (assignmentsfiles)
 *  - 'self_learning' : self-paced, driven by a SCORM package (scorm_file)
 *
 * One of each category is created per school, cycling through the streams/
 * sessions seeded by StudentStreamSeeder/StudentContentSeeder. Requires
 * SchoolSeeder, StudentStreamSeeder and StudentContentSeeder to have
 * already run.
 */
class AssignmentSeeder extends Seeder
{
    /**
     * icon_name is varchar(20), so these use short filenames (copies of real
     * files already present under public/image/assignment/) rather than the
     * longer 'assignment_<random>.ext' attachment-style names, which would
     * silently truncate and no longer match a real file on disk.
     */
    private const ICONS = [
        'vk-icon-1.png',
        'vk-icon-2.png',
        'vk-icon-3.png',
        'vk-icon-4.jpg',
        'vk-icon-5.png',
    ];

    /**
     * Student\AssignmentController only previews the FIRST attachment
     * (AssingmentFiles::where(...)->first()) inside an <iframe> on the
     * assignment detail page (resources/views/student/assignment/show.blade.php).
     * Browsers can't render .doc/.docx inline, so a Word doc as the first
     * attachment shows as blank/missing — only PDFs preview correctly here.
     *
     * The first entry is a real, on-topic "Project Assignment Brief" PDF
     * (generated from resources/views/pdf/project_assignment_brief.blade.php
     * via dompdf), so every facilitated assignment previews something
     * actually relevant rather than an unrelated reused document.
     */
    private const ATTACHMENTS = [
        'vk-project-assignment-brief.pdf',
        'assignment_35fkcAqB5h.pdf',
        'assignment_42t80atHgf.pdf',
        'assignment_5mKABQaERI.pdf',
    ];

    public function run(): void
    {
        $schools = School::orderBy('id')->get();
        $streams = Stream::orderBy('id')->get();

        if ($schools->isEmpty() || $streams->isEmpty()) {
            $this->command?->error('Schools/streams not found — run SchoolSeeder and StudentStreamSeeder first.');
            return;
        }

        $streamIndex = 0;

        foreach ($schools as $school) {
            $trainerId = DB::table('trainer_allocation_new')
                ->where('school_id', $school->id)
                ->orderBy('id')
                ->value('trainer_id');

            // --- FACILITATED assignment: trainer-led, with attached files ---
            $facilitatedStream = $streams[$streamIndex % $streams->count()];
            $facilitatedSession = Studentscontent::where('stream_id', $facilitatedStream->id)->first();
            $streamIndex++;

            $facilitated = StudentCommunications::firstOrCreate(
                ['school_id' => $school->id, 'category' => 'facilitated', 'title' => "{$school->school_name}: {$facilitatedStream->title} Assignment"],
                [
                    'grade_id' => $facilitatedStream->agegroup_id,
                    'trainer_id' => $trainerId,
                    'stream_id' => $facilitatedStream->id,
                    'session_id' => $facilitatedSession->id ?? null,
                    'comment' => "Complete the hands-on activity for \"{$facilitatedStream->title}\" and submit your work for trainer review.",
                    'icon_name' => self::ICONS[$school->id % count(self::ICONS)],
                    'scorm_file' => null,
                    'scorm_status' => 0,
                    'display_order_id' => (new StudentCommunications())->getNextDisplayOrderId(),
                    'is_active' => 1,
                ]
            );

            if (AssingmentFiles::where('assignment_id', $facilitated->id)->doesntExist()) {
                foreach (array_slice(self::ATTACHMENTS, 0, 2) as $attachment) {
                    AssingmentFiles::firstOrCreate([
                        'assignment_id' => $facilitated->id,
                        'attachment' => 'image/assignment/' . $attachment,
                    ]);
                }
            }

            // --- SELF_LEARNING assignment: self-paced, SCORM-driven ---
            $selfLearningStream = $streams[$streamIndex % $streams->count()];
            $selfLearningSession = Studentscontent::where('stream_id', $selfLearningStream->id)->first();
            $streamIndex++;

            StudentCommunications::firstOrCreate(
                ['school_id' => $school->id, 'category' => 'self_learning', 'title' => "{$school->school_name}: {$selfLearningStream->title} Self-Paced Module"],
                [
                    'grade_id' => $selfLearningStream->agegroup_id,
                    'trainer_id' => null,
                    'stream_id' => $selfLearningStream->id,
                    'session_id' => $selfLearningSession->id ?? null,
                    'comment' => "A self-paced SCORM module covering \"{$selfLearningStream->title}\". Complete it at your own pace.",
                    'icon_name' => self::ICONS[($school->id + 1) % count(self::ICONS)],
                    'scorm_file' => 'venturekids-' . Str::slug($selfLearningStream->title) . '.zip',
                    'scorm_status' => 1,
                    'display_order_id' => (new StudentCommunications())->getNextDisplayOrderId(),
                    'is_active' => 1,
                ]
            );

            $this->command?->info("Seeded facilitated + self_learning assignments for {$school->school_name}.");
        }
    }
}
