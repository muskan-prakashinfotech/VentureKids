<?php

namespace App\Jobs;

use App\Jobs\SendExternalSessionReminderEmails;
use App\Models\ExternalSession;
use App\Services\ExternalSessionAttendeeService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class QueueExternalSessionReminderEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $externalSession;
    public $occurrenceDateTime;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ExternalSession $externalSession, Carbon $occurrenceDateTime)
    {
        $this->externalSession = $externalSession;
        $this->occurrenceDateTime = $occurrenceDateTime->copy();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $session = $this->externalSession;
        $occurrence = $this->occurrenceDateTime->toDateTimeString();

        if (!$session || $session->is_cancelled) {
            Log::info("[1-hour reminder] Skipped — session #{$session?->id} '{$session?->title}' has been cancelled entirely.", [
                'session_id' => $session?->id,
                'occurrence_date_time' => $occurrence,
            ]);
            return;
        }

        if ($session->isOccurrenceCancelled($this->occurrenceDateTime)) {
            Log::info("[1-hour reminder] Skipped — occurrence {$occurrence} of session #{$session->id} '{$session->title}' was cancelled.", [
                'session_id' => $session->id,
                'occurrence_date_time' => $occurrence,
            ]);
        } else {
            $recipients = app(ExternalSessionAttendeeService::class)->resolve($session);

            foreach ($recipients as $recipient) {
                SendExternalSessionReminderEmails::dispatch(
                    $session,
                    $recipient['email'],
                    $recipient['name'],
                    $recipient['type'],
                    $recipient['timezone'] ?? 'UTC',
                    $this->occurrenceDateTime
                );
            }

            Log::info("[1-hour reminder] Sent for occurrence {$occurrence} of session #{$session->id} '{$session->title}' to " . count($recipients) . ' recipient(s).', [
                'session_id' => $session->id,
                'occurrence_date_time' => $occurrence,
                'recipient_count' => count($recipients),
            ]);
        }

        $this->scheduleNextOccurrence($session);
    }

    protected function scheduleNextOccurrence(ExternalSession $session)
    {
        $next = $session->nextOccurrenceFrom($this->occurrenceDateTime->copy()->addDay()->startOfDay());

        if (!$next) {
            Log::info("[1-hour reminder] Chain ended — no further active occurrences for session #{$session->id} '{$session->title}'.", [
                'session_id' => $session->id,
            ]);
            return;
        }

        $sendAt = $next->copy()->timezone('Asia/Singapore')->subHour()->timezone('UTC');
        $job = new self($session, $next);
        $job->delay($sendAt);

        $session->email_reminder_job_id = Bus::dispatch($job);
        $session->save();

        Log::info("[1-hour reminder] Chain advanced — next occurrence for session #{$session->id} '{$session->title}' is {$next->toDateTimeString()}, reminder scheduled for {$sendAt->toDateTimeString()} UTC.", [
            'session_id' => $session->id,
            'next_occurrence_date_time' => $next->toDateTimeString(),
            'send_at_utc' => $sendAt->toDateTimeString(),
        ]);
    }
}
