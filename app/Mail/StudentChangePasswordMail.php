<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentChangePasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $domain = '';
    public $username;
    public $password;
    public $studentName;

    public function __construct($username = "", $password = "", $studentName = "", $domain = "")
    {
        $this->username = $username;
        $this->password = $password;
        $this->studentName = $studentName;
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
        return $this->subject('Change Password Notification')->view('emails.student-change-password');
    }
}
