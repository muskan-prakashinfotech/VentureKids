<?php

namespace App\Jobs;

use App\Models\StudentCertificates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendStudentCertificateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $certificate;

    /**
     * Create a new job instance.
     */
    public function __construct(StudentCertificates $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $student = $this->certificate->student;
            $grade = $this->certificate->grade;
            $parentEmail = $student ? trim((string) $student->parent_email) : '';

            if ($parentEmail === '' || !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Student Certificate Email Job: Skipping email (parent_email missing/invalid)', [
                    'certificate_id' => $this->certificate->id,
                    'student_id' => $this->certificate->student_id,
                    'student_name' => $student->name ?? null,
                    'student_email' => $parentEmail ?: null,
                    'grade_name' => $grade->grade ?? null,
                    'unique_id' => $this->certificate->unique_id
                ]);
                return;
            }

            Log::info('Student Certificate Email Job: Sending email to student', [
                'certificate_id' => $this->certificate->id,
                'student_id' => $this->certificate->student_id,
                'student_name' => $student->name,
                'student_email' => $parentEmail,
                'grade_name' => $grade->grade,
                'unique_id' => $this->certificate->unique_id
            ]);

            // Generate download URL for student
            $downloadUrl = route('student.certificate.download', [
                'token' => $this->certificate->download_token
            ]);

            // Send email to student
            Mail::to($parentEmail)->send(new \App\Mail\StudentCertificateReleased($this->certificate, $downloadUrl));

            Log::info('Student Certificate Email Job: Email sent successfully to student', [
                'certificate_id' => $this->certificate->id,
                'student_email' => $parentEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Student Certificate Email Job: Failed to send email to student', [
                'certificate_id' => $this->certificate->id,
                'student_id' => $this->certificate->student_id,
                'student_email' => $this->certificate->parent_email ?? 'N/A',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }
}
