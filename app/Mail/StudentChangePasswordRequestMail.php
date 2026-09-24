<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentChangePasswordRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $domain = '';
    public $school_name;
    public $student_name;
    public $student_email;

    public function __construct($school_name = "", $student_name = "", $student_email = "", $domain = "")
    {
        $this->school_name = $school_name;
        $this->student_name = $student_name;
        $this->student_email = $student_email;
        if (!empty($domain)) {
            $this->domain = $domain.'/login';
        } else {
            $this->domain = route('login');
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Change Password Request Notification')->view('emails.student-change-password-request');
    }
}
