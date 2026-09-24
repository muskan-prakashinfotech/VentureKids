<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Students;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;

class StudentChallenge implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $parentEmail, $studentEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Array $student)
    {
        $this->studentEmail = $student['studentEmail'];
        $parentEmail = Students::where('user_id', $student['studentId'])->pluck('parent_email')->first();
        $this->parentEmail = $parentEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->studentEmail)
            ->cc($this->parentEmail)
            ->send(new SendEmail(['subject' => "Thank you for the submission!", 'emailBody' => "You have successfully responded to the challenge!"]));
    }
}
