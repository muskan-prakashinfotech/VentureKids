<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingSchoolSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $schoolName;
    public $partnerName;
    public $partnerEmail;
    public $approvalUrl;

    public function __construct($schoolName, $partnerName, $partnerEmail, $approvalUrl)
    {
        $this->schoolName = $schoolName;
        $this->partnerName = $partnerName;
        $this->partnerEmail = $partnerEmail;
        $this->approvalUrl = $approvalUrl;
    }

    public function build()
    {
        return $this->subject('Pending School Approval Requested')
            ->view('emails.pending-school-submitted');
    }
}
