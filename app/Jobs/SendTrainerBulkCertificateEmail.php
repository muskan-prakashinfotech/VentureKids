<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTrainerBulkCertificateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $certificates;
    protected $bulkDownloadUrl;
    protected $grade;

    /**
     * Create a new job instance.
     */
    public function __construct($certificates, $bulkDownloadUrl, $grade)
    {
        $this->certificates = $certificates;
        $this->bulkDownloadUrl = $bulkDownloadUrl;
        $this->grade = $grade;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            if (empty($this->certificates)) {
                return;
            }

            $trainer = $this->certificates[0]->trainer;

            Log::info('Trainer Bulk Certificate Email Job: Sending bulk email to trainer', [
                'certificate_count' => count($this->certificates),
                'trainer_id' => $trainer->id,
                'trainer_name' => $trainer->trainer_name,
                'trainer_email' => $trainer->official_email_id,
                'grade_name' => $this->grade->grade
            ]);

            // Send bulk email to trainer
            Mail::to($trainer->official_email_id)->send(new \App\Mail\BulkCertificateReleased($this->certificates, $this->bulkDownloadUrl, $this->grade));

            Log::info('Trainer Bulk Certificate Email Job: Bulk email sent successfully to trainer', [
                'trainer_email' => $trainer->official_email_id,
                'certificate_count' => count($this->certificates)
            ]);

        } catch (\Exception $e) {
            Log::error('Trainer Bulk Certificate Email Job: Failed to send bulk email to trainer', [
                'certificate_count' => count($this->certificates),
                'trainer_email' => $this->certificates[0]->trainer->official_email_id ?? 'N/A',
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