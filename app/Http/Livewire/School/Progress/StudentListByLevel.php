<?php

namespace App\Http\Livewire\School\Progress;

use App\Models\Grade;
use App\Models\School;
use App\Models\Students;
use App\Models\Submission;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class StudentListByLevel extends Component
{
    public $grade = null;
    public $selectstudent = null;
    public $assignments = null;

    public function studentInfo($id)
    {
        $this->selectstudent = Students::find($id);
        $this->assignments = Submission::where('student_id', $id)->with(['assignment'])->get();
    }

    public function render()
    {
        $grades = Grade::all();
        $schoolId = School::where('user_id', Session::get('user_id'))->first();
        $totalstudents = Students::where('school_id', $schoolId->id)->count();

        $students = null;
        if (isset($this->grade)) {
            $schoolId = School::where('user_id', Session::get('user_id'))->first();
            $students = Students::where('grade_id', $this->grade)->where('school_id', $schoolId->id)->get();
        }

        return view('livewire.school.progress.student-list-by-level', compact('grades', 'students', 'totalstudents'));
    }
}
