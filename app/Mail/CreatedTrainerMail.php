<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatedTrainerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $link = 'http://trainer.venderkids.com';
    public $trainer_name;
    public $username;
    public $password;

    public function __construct($trainer_name, $username, $password)
    {
        $this->trainer_name = $trainer_name;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Trainer Onboarding with VentureKids')->view('emails.created-trainer-name');
    }
}
