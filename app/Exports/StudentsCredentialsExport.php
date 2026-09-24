<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsCredentialsExport implements FromCollection, WithHeadings
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->students)->map(function ($student) {
            return [
                'student_grade' => $student['student_grade'],
                'student_batch' => $student['student_batch'],
                'student_first_name' => $student['student_first_name'],
                'student_last_name' => $student['student_last_name'],
                'student_user_name' => $student['student_user_name'],
                'student_password' => $student['student_password'], 
            ];
        });
    }

    public function headings(): array
    {
        return ['Grade', 'Batch', 'Student First Name', 'Student Last Name', 'User Name', 'Password'];
    }
}
