<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentCertificates;

class BulkCertificateReleased extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $certificates;
    public $bulkDownloadUrl;
    public $grade;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($certificates, $bulkDownloadUrl, $grade)
    {
        $this->certificates = $certificates;
        $this->bulkDownloadUrl = $bulkDownloadUrl;
        $this->grade = $grade;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Student Certificates Released')
                    ->view('emails.bulk_certificate_released');
    }
}