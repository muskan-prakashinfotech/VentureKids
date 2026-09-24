<?php

namespace App\Jobs;

use App\Mail\PartnerPartnershipReminderMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class PartnerPartnershipReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $partnerId;

    public function __construct($partnerId)
    {
        $this->partnerId = $partnerId;
    }

    public function handle(): void
    {
        $partner = User::where('group', 5)->find($this->partnerId);
        if (!$partner || !$partner->partnership_end_date) {
            return;
        }

        $recipients = collect([env('MAIL_ADMIN'), $partner->email])
            ->filter()
            ->unique()
            ->values();

        foreach ($recipients as $recipient) {
            safeMailAction('partner partnership reminder mail', [
                'partner_id' => $partner->id,
                'recipient' => $recipient,
                'partnership_end_date' => $partner->partnership_end_date,
            ], function () use ($recipient, $partner) {
                Mail::to($recipient)->send(new PartnerPartnershipReminderMail(
                    $partner->name,
                    Carbon::parse($partner->partnership_end_date)->format('Y-m-d')
                ));
            });
        }
    }
}
