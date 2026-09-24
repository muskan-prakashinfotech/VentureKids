<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SessionCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $external_session;
    public $name;
    public $type;
    public $timeZone;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($external_session, $name, $type ,$timeZone = 'UTC')
    {
        $this->external_session = $external_session;
        $this->name = $name;
        $this->type = $type;
        $this->timeZone = $timeZone;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('VentureKids - Your Scheduled Session Has Been Cancelled')
                    ->view('emails.session_cancelled')
                    ->with([
                            'external_session' => $this->external_session,
                            'name' => $this->name,
                            'timeZone' => $this->timeZone,
                    ]);
    }
}
