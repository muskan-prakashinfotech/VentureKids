<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Exports\StudentObservationDataExport;
use App\Models\Students;
use App\Models\TrainerAllocationNew;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ObservationController extends Controller
{
    public function downloadData($student_id)
    {
        $student_id = (int) $student_id;
        $trainer_id = Session::get('trainer_id');

        $trainer_access = false;

        $student_data = Students::select('school_batch_id')->find($student_id);
        
        if($student_data) {
            $allocation_data = TrainerAllocationNew::where('trainer_id', $trainer_id)->where('school_batch_id',$student_data->school_batch_id)->get();
            if($allocation_data->count()) {
                $trainer_access = true;
            }
        }
        
        if($trainer_access) {
            $fileName = 'observationDataExport_'.date('Ymd').'.xlsx';
            
            $param['source'] = 'Trainer'; 
            $param['student_id'] = $student_id; 
            $param['school_id'] = $allocation_data[0]->school_id; 

            return Excel::download(new StudentObservationDataExport($param), $fileName, \Maatwebsite\Excel\Excel::XLSX);
        } 

        return redirect()->route('trainer.student_list');

    }
}
