<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailNotification;
use App\Models\EmailInfo;
use App\Mail\SendEmail;
use App\Models\School;

class SchoolEdited implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $school_name;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($school_name, $data)
    {
        $this->school_name = $school_name;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emailBody = "<p><img src='http://schoolmanagement.com/image/school.png' alt='app_logo' height='50px'></p><p>Hi</p>
        <p>School {$this->school_name} updated his below profile data.</p><ul>";
        foreach($this->data as $data) {
            $emailBody .= "<li>{$data}</li>";
        }
        $emailBody .= "</ul>";
        
        Mail::to(env('MAIL_ADMIN'))
            ->send(new SendEmail(['subject' => "School Edited Notification", 'emailBody' => $emailBody]));
    }
}
