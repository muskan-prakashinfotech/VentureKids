<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentChangePasswordRequestMail;

class StudentChangePasswordRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $student, $host;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Array $student, $host)
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
        $domain = "";
        if (isset($this->student['school_id'])) {
            $school = \App\Models\School::with('domains')->find($this->student['school_id']);
            if (!empty($school)) {
                $domain = $school->domains->domain.config("tenancy.sub_domain");
            }
        }

        if (!empty($this->host) && $this->host != $domain) {
            $domain = $this->host;
        }

        Mail::to($this->student['school']['official_email_id'])->send(new StudentChangePasswordRequestMail($this->student['school']['school_name'], $this->student['user']['name'], $this->student['user']['email'], $domain));
    }
}
