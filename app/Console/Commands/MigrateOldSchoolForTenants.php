<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class MigrateOldSchoolForTenants extends Command
{
    protected $signature = 'tenant:migrate-old-school';

    protected $description = 'generate tenant for old school and move assets';

    public function handle()
    {
        $schools = \App\Models\School::whereNull('tenant_id')->orWhere('tenant_id', '')->get();

        foreach ($schools as $school) {
            $this->migrateOldSchool($school);
        }

        $this->info('Old school migration for tenants completed.');
    }

    protected function migrateOldSchool(\App\Models\School $school)
    {
        if(empty($school->tenant_id)) {
            // @todo generate domain

            // Move School Assets
            $tenantId = Uuid::uuid4()->toString();
            $logoPath = null;
            $coverImagePath = null;

            if($school->school_logo && File::exists(public_path($school->school_logo))) {
                $ext = pathinfo(public_path($school->school_logo), PATHINFO_EXTENSION);
                $logoPath = "tenants/".$tenantId.'/school/'. 'logo.'.$ext;
                $this->moveFile(public_path($school->school_logo), public_path($logoPath));
            }

            if($school->school_cover_image && File::exists(public_path($school->school_cover_image))) {
                $ext = pathinfo(public_path($school->school_cover_image), PATHINFO_EXTENSION);
                $coverImagePath = "tenants/".$tenantId.'/school/'. 'login_cover.'.$ext;
                $this->moveFile(public_path($school->school_cover_image), public_path($coverImagePath));
            }

            \App\Models\School::where('id', $school->id)->update([
                'tenant_id' => $tenantId,
                'school_logo' => $logoPath,
                'school_cover_image' => $coverImagePath,
            ]);


            // Move Student Assets
            $students  = \App\Models\Students::where('school_id', $school->id)
                ->where('image', '!=', '')
                ->whereNotNull('image')
                ->get();

            foreach ($students as $student) {
                $imagePath = null;

                if($student->image && File::exists(public_path($student->image))) {
                    $imagePath = "tenants/".$tenantId.'/student/'. basename($student->image);
                    $this->moveFile(public_path($student->image), public_path($imagePath));
                }

                \App\Models\Students::where('id', $student->id)->update([
                    'image' => $imagePath
                ]);

                // Project Files
                $projectFiles =  \App\Models\Projectfiles::where('student_id', $student->id)
                    ->whereNotNull('attachment')
                    ->where('attachment', '!=', '')
                    ->get();

                foreach ($projectFiles as $projectFile) {
                    $projectFilePath = null;
                    if($projectFile->attachment && File::exists(public_path($projectFile->attachment))) {
                        if($projectFile->attachment_type == "Pdf") {
                            $projectFilePath = "tenants/".$tenantId.'/student/attachments/'. basename($projectFile->attachment);
                        } else {
                            $projectFilePath = "tenants/".$tenantId.'/student/project/'. basename($projectFile->attachment);
                        }
                        $this->moveFile(public_path($projectFile->attachment), public_path($projectFilePath));
                    }

                    \App\Models\Projectfiles::where('id', $projectFile->id)->update([
                        'attachment' => $projectFilePath
                    ]);
                }
                $projectFiles =  \App\Models\Projectfiles::where('student_id', $student->id)
                    ->whereNotNull('attachment')
                    ->where('attachment', '!=', '')
                    ->get();

                // Submissions
                $submissions =  \App\Models\Submission::where('student_id', $student->id)
                    ->where('file', '!=', '')
                    ->whereNotNull('file')
                    ->get();

                foreach ($submissions as $submission) {
                    $submissionPath = null;
                    if($submission->file && File::exists(public_path($submission->file))) {
                        $submissionPath = "tenants/".$tenantId.'/student/submissions/'. basename($submission->file);
                        $this->moveFile(public_path($submission->file), public_path($submissionPath));
                    }

                    \App\Models\Submission::where('id', $submission->id)->update([
                        'file' => $submissionPath
                    ]);
                }

                // Event Challenges
                $studentId = $student->id;
                $challanges = \DB::table('event_challenge_files')
                    ->whereNotNull('attachment')
                    ->where('attachment', '!=', '')
                    ->whereExists(function ($query) use ($studentId) {
                        $query->select(\DB::raw(1))
                            ->from('event_challenges')
                            ->whereRaw('event_challenges.id = event_challenge_files.event_challenge_id')
                            ->where('student_id', $studentId);
                    })
                    ->get();

                foreach ($challanges as $challenge) {
                    $challengePath = null;
                    if($challenge->attachment && File::exists(public_path($challenge->attachment))) {
                        $challengePath = "tenants/".$tenantId.'/student/event_challenge/'. basename($challenge->attachment);
                        $this->moveFile(public_path($challenge->attachment), public_path($challengePath));
                    }

                    \App\Models\EventChallengeFiles::where('id', $challenge->id)->update([
                        'attachment' => $challengePath
                    ]);
                }
            }
        }
    }

    public function moveFile($sourcePath, $destinationPath)
    {
        $destinationDirectory = dirname($destinationPath);
        if (!File::isDirectory($destinationDirectory)) {
            mkdir($destinationDirectory, 0755, true);
        }
        File::move($sourcePath, $destinationPath);
    }
}
