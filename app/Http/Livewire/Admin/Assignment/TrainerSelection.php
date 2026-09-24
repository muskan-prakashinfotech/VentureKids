<?php

namespace App\Http\Livewire\Admin\Assignment;

use App\Models\Grade;
use App\Models\School;
use App\Models\Trainer;
use App\Models\TrainerAllocation;
use Livewire\Component;

class TrainerSelection extends Component
{
    public $school = null;
    public $grade = null;

    public function render()
    {
        $schools = School::get();

        $grades = null;
        if (isset($this->school)) {
            $gradeids = TrainerAllocation::select('grade')->where('school_id', $this->school)->get();
            $grades = Grade::whereIn('id', $gradeids->pluck('grade')->toArray())->get();
        }

        $trainers = null;
        if (isset($this->grade)) {
            $trainerids = TrainerAllocation::select('trainer_id')->where('school_id', $this->school)->where('grade', $this->grade)->get();
            $trainers = Trainer::select('trainer_name', 'id')->whereIn('id', $trainerids->pluck('trainer_id')->toArray())->get();
        }

        return view('livewire.admin.assignment.trainer-selection', compact('schools', 'grades', 'trainers'));
    }
}
