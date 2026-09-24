<?php

namespace App\Http\Livewire\Trainer;

use App\Models\School;
use App\Models\StudentAttendance;
use App\Models\Students;
use App\Models\Trainer;
use App\Models\TrainerAllocation;
use App\Models\Grade;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component;

class Attendance extends Component
{
    public $school_id;
    public $grade_id;
    public $allocation;
    public $dates;
    public $trainer;

    public function render()
    {
        $this->trainer = Trainer::where('user_id', Session::get('user_id'))->first();
        $trainerallocations = TrainerAllocation::where('trainer_id', $this->trainer->id)->get();
        $schools = School::whereIn('id', $trainerallocations->pluck('school_id')->toArray())->get();
        
        $selectedSchool = $selectedClass = '';
        if(empty($this->school_id) && $schools->count()) {
            $this->school_id = $schools[0]['id'];
        } else {
            $selectedSchool = $this->school_id;
        }

        $day = '';
        if(!empty($this->grade_id)) {
            $dayFilterQry = TrainerAllocation::where('id', $this->grade_id)->get()->ToArray();
            $day = $dayFilterQry[0]['day'];
            $selectedClass = $this->grade_id;
        }

        $this->allocation = Session::get('user_id');
        
        // dd(Auth::user());
        $students = [];
        $dateRange = [];
        $studentattendances = [];
        if (isset($this->school_id) && isset($this->allocation)) {
            $grade = $trainerallocations->pluck('grade')->unique();
            $students = Students::where('school_id', $this->school_id)->whereIn('grade_id',$grade)->get();
            $trainerday = $trainerallocations->pluck('day')->unique()->toArray();
            $dateRange = CarbonPeriod::create(now()->startOfMonth(), now()->endOfMonth());
            if(!empty($day)) {
                $dateRange = CarbonPeriod::create(now()->startOfMonth(), now()->endOfMonth())
                    ->filter(function (Carbon $date) use ($day) {
                        switch ($day) {
                            case 0:
                                return $date->isSunday();
                                break;
                            case 1:
                                return $date->isMonday();
                                break;
                            case 2:
                                return $date->isTuesday();
                                break;
                            case 3:
                                return $date->isWednesday();
                                break;
                            case 4:
                                return $date->isThursday();
                                break;
                            case 5:
                                return $date->isFriday();
                                break;
                            case 6:
                                return $date->isSaturday();
                                break;

                            default:
                                return null;
                                break;
                        }
                    });
            }

            $studentattendances = StudentAttendance::whereIn('date', $dateRange->toArray())->whereIn('student_id', $students->pluck('id'))->get();
            foreach ($students as $student) {
                foreach ($dateRange as $date) {
                    if ($date->lte(now())) {
                        $this->dates[$date->format('Y-m-d')][$student->id] = true;
                    }
                }
            }

            foreach ($studentattendances as $studentattendance) {
                $this->dates[$studentattendance->date][$studentattendance->student_id] = $studentattendance->status;
            }
        }
        
        return view('livewire.trainer.attendance', compact('trainerallocations', 'schools', 'students', 'dateRange', 'studentattendances', 'trainerday', 'selectedSchool', 'selectedClass'));
    }
}
