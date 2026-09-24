<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatedSchoolMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $link = '';
    public $principal_name;
    public $school_name;
    public $username;
    public $password;
    public $course_start_date;
    public $course_end_date;
    public $domain;

    public function __construct($principal_name, $school_name, $username, $password, $course_start_date, $course_end_date, $domain)
    {
        $this->link = route('login');
        $this->principal_name = $principal_name;
        $this->school_name = $school_name;
        $this->username = $username;
        $this->password = $password;
        $this->course_start_date = $course_start_date;
        $this->course_end_date = $course_end_date;
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
        return $this->subject($this->school_name . ' joins VentureKids')->view('emails.created-school-name');
    }
}
