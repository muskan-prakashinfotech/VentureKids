<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentParentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $studentMailData;
    

    public function __construct($studentMailData)
    {
        $this->studentMailData = $studentMailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->studentMailData['name'].' Logged into VentureKids')->view('emails.student-parent-mail');
    }
}