<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PartnerSchoolEditedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $schoolName;
    public string $partnerName;
    public string $partnerEmail;
    public array $changes;

    public function __construct(string $schoolName, string $partnerName, string $partnerEmail, array $changes)
    {
        $this->schoolName = $schoolName;
        $this->partnerName = $partnerName;
        $this->partnerEmail = $partnerEmail;
        $this->changes = $changes;
    }

    public function build()
    {
        return $this->subject('Partner School Update Alert: ' . $this->schoolName)
            ->view('emails.partner-school-edited');
    }
}
