<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\SendEmail;

class InformAdminToAllocateTrainerToSchool implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $super_admin_email = User::where('group', 1)->select('email')->first();
        if ($super_admin_email) {
            Mail::to($super_admin_email->email)
                ->send(new SendEmail(['subject' => 'Allow to Allocate trainer', 'emailBody' => 'You are now able to allocate trainer.']));
        }
    }
}
