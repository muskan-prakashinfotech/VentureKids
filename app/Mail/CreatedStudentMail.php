<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatedStudentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $domain = '';
    public $email;
    public $password;
    public $studentName;
    public $isRegistred;
    public $userName;

    public function __construct($email = "", $password = "", $studentName = "", $isRegistred = 0, $domain = "", $userName = "")
    {
        $this->email = $email;
        $this->password = $password;
        $this->studentName = $studentName;
        $this->isRegistred = $isRegistred;
        if (!empty($domain)) {
            $this->domain = $domain.'/login';
        } else {
            $this->domain = route('login');
        }
        $this->userName = $userName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Get started with VentureKids')->view('emails.created-student-name');
    }
}
