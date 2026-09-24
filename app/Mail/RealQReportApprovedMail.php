<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RealQReportApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $studentName;
    public string $recipientType; // 'student' or 'parent'

    public function __construct(string $studentName, string $recipientType = 'student')
    {
        $this->studentName   = $studentName;
        $this->recipientType = $recipientType;
    }

    public function build()
    {
        return $this->subject('VentureKids: Your RealQ Report Is Ready!')
                    ->view('emails.realq_report_approved');
    }
}
