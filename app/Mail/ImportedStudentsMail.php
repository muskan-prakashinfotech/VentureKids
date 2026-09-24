<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsCredentialsExport;

class ImportedStudentsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $students; 
    public $school;
    public $recipientName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($students, $school, $recipientName = 'School Admin')
    {
        $this->students = $students;
        $this->school = $school;
        $this->recipientName = $recipientName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $excelFile = Excel::raw(new StudentsCredentialsExport($this->students), \Maatwebsite\Excel\Excel::XLSX);

        return $this->subject('Imported Students Credentials - ' . $this->school['school_name'])
                    ->view('emails.imported_students')
                    ->with([
                        'school' => $this->school,
                        'recipientName' => $this->recipientName,
                    ])
                    ->attachData($excelFile, 'students_credentials.xlsx', [
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
    }
}
