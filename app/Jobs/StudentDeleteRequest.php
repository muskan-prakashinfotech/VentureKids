<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;

class StudentDeleteRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $student, $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Array $student, $host = "")
    {
        $this->student = $student;
        $this->host = $host;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emailBody = "<p>Hi Admin,</p>";
        $emailBody .= "<p>School <span style='font-weight: 600;margin: 0;'>{$this->student['school_name']}</span> requested to delete below student data.</p>";
        $emailBody .= "<ul><li>Student Name : {$this->student['student_name']}</li><li>Student Email : {$this->student['student_email']}</li></ul>";
        
        Mail::to(env('MAIL_ADMIN'))->bcc(env('MAIL_BCC'))->send(new SendEmail(['subject' => "Student Delete Request", 'emailBody' => $emailBody]));
    }
}
