<?php

namespace App\Jobs;

use App\Mail\ExternalSessionReminderMail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendExternalSessionReminderEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $externalSession;
    public $email;
    public $name;
    public $type;
    public $timeZone;
    public $occurrenceDateTime;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($externalSession, $email, $name, $type = null, $timeZone = 'UTC', ?Carbon $occurrenceDateTime = null)
    {
        $this->externalSession = $externalSession;
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
        $this->timeZone = $timeZone;
        $this->occurrenceDateTime = $occurrenceDateTime ?: Carbon::parse($externalSession->date_time, 'UTC');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new ExternalSessionReminderMail($this->externalSession, $this->name, $this->type, $this->timeZone, $this->occurrenceDateTime));
    }
}
