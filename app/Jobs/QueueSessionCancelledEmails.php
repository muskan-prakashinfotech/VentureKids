<?php

namespace App\Jobs;

use App\Jobs\SendSessionCancelledEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueueSessionCancelledEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $externalSession;
    public $recipients;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($externalSession, array $recipients)
    {
        $this->externalSession = $externalSession;
        $this->recipients  = $recipients ;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->recipients as $recipient) {
            SendSessionCancelledEmail::dispatch(
                $this->externalSession,
                $recipient['email'],
                $recipient['name'],
                $recipient['type'],
                $recipient['timezone'] ?? 'UTC'
            );
        }
    }
}
