<?php

namespace App\Http\Livewire\Trainer\Assigment;

use App\Models\Grade;
use App\Models\School;
use App\Models\Trainer;
use App\Models\TrainerAllocation;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SchoolSelection extends Component
{
    public $school = null;

    public function render()
    {
        $trainer = Trainer::where('user_id', Session::get('user_id'))->first();
        $trainerallicaion = TrainerAllocation::select('school_id')->where('trainer_id', $trainer->id)->get();
        $schools = School::whereIn('id', $trainerallicaion->pluck('school_id')->toArray())->get();
        $grades = null;
        if (isset($this->school)) {
            $gradeids = TrainerAllocation::select('grade')->where('school_id', $this->school)->where('trainer_id', $trainer->id)->get();
            $grades = Grade::whereIn('id', $gradeids->pluck('grade')->toArray())->get();
        }

        return view('livewire.trainer.assigment.school-selection', compact('schools', 'grades'));
    }
}
