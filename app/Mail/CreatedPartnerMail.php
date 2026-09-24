<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatedPartnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $partner_name;
    public $username;
    public $password;
    public $license_count;
    public $domain;

    public function __construct($partner_name, $username, $password, $license_count, $domain = null)
    {
        $this->partner_name = $partner_name;
        $this->username = $username;
        $this->password = $password;
        $this->license_count = $license_count;
        $this->domain = !empty($domain) ? rtrim($domain, '/').'/login' : route('login');
    }

    public function build()
    {
        return $this->subject('Partner Onboarding with VentureKids')
            ->view('emails.created-partner-name');
    }
}
