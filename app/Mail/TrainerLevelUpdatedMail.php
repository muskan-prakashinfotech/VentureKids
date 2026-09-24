<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrainerLevelUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $trainerName;
    public string $partnerName;
    public string $partnerEmail;
    public string $oldLevels;
    public string $newLevels;
    public string $action;

    public function __construct(string $trainerName, string $partnerName, string $partnerEmail, string $oldLevels, string $newLevels, string $action = 'updated')
    {
        $this->trainerName = $trainerName;
        $this->partnerName = $partnerName;
        $this->partnerEmail = $partnerEmail;
        $this->oldLevels = $oldLevels;
        $this->newLevels = $newLevels;
        $this->action = $action;
    }

    public function build()
    {
        return $this->subject('Trainer Level ' . ucfirst($this->action) . ' Alert: ' . $this->trainerName)
            ->view('emails.trainer-level-updated');
    }
}
