<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingSchoolApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $schoolName;
    public $partnerName;
    public $schoolEmail;
    public $loginUrl;

    public function __construct($schoolName, $partnerName, $schoolEmail, $loginUrl)
    {
        $this->schoolName = $schoolName;
        $this->partnerName = $partnerName;
        $this->schoolEmail = $schoolEmail;
        $this->loginUrl = $loginUrl;
    }

    public function build()
    {
        return $this->subject('School Approved and Published')
            ->view('emails.pending-school-approved');
    }
}
