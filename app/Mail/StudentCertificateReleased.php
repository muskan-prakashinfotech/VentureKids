<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentCertificates;

class StudentCertificateReleased extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $certificate;
    public $downloadUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(StudentCertificates $certificate, $downloadUrl)
    {
        $this->certificate = $certificate;
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Congratulations! Your Certificate is Ready')
                    ->view('emails.student_certificate_released');
    }
}