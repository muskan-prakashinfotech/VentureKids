<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\CreatedStudentMail;

class StudentCreate implements ShouldQueue
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
        $domain = null;
        if (!empty($this->student['school_id'])) {
            $school = \App\Models\School::with('domains')->find($this->student['school_id']);
            if (!empty($school) && isset($school->domains->domain) && !empty($school->domains->domain)) {
                $domain = $school->domains->domain.config("tenancy.sub_domain");
            }
        }

        if (!empty($this->host) && $this->host != $domain) {
            $domain = $this->host;
        }

        $displayEmail = true;
        if (!array_key_exists('student_email', $this->student) || empty($this->student['student_email'])) {
            $displayEmail = false;
        }
        
        Mail::to($this->student['email'])->bcc(env('MAIL_BCC'))->send(new CreatedStudentMail($displayEmail ? $this->student['email'] : '', $this->student['password'], $this->student['name'], 0, $domain, $this->student['user_name']));
    }
}
