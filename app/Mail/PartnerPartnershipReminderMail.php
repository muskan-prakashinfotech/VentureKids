<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PartnerPartnershipReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $partnerName;
    public $partnershipEndDate;

    public function __construct($partnerName, $partnershipEndDate)
    {
        $this->partnerName = $partnerName;
        $this->partnershipEndDate = $partnershipEndDate;
    }

    public function build()
    {
        return $this->subject('Partner Partnership End Date Reminder')
            ->view('emails.partner-partnership-reminder');
    }
}
