<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExternalSessionCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $external_session;
    public $name;
    public $type;
    public $timeZone;
    public $occurrenceDateTime;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($external_session, $name, $type, $timeZone = 'UTC', ?Carbon $occurrenceDateTime = null)
    {
        $this->external_session = $external_session;
        $this->name = $name;
        $this->type = $type;
        $this->timeZone = $timeZone;
        $this->occurrenceDateTime = $occurrenceDateTime ?: Carbon::parse($external_session->date_time, 'UTC');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('VentureKids - New Session Scheduled!')
                    ->view('emails.external_session_created')
                    ->with([
                            'external_session' => $this->external_session,
                            'name' => $this->name,
                            'timeZone' => $this->timeZone,
                            'occurrence_date_time' => $this->occurrenceDateTime,
                    ]);
    }
}
