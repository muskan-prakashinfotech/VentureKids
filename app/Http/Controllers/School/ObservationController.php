<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Exports\StudentObservationDataExport;
use Maatwebsite\Excel\Facades\Excel;

class ObservationController extends Controller
{
    public function downloadData()
    {
        $fileName = 'observationDataExport_'.date('Ymd').'.xlsx';
        
        $param['source'] = 'School'; 
        $param['school_id'] = Session::get('school_id'); 
        
        return Excel::download(new StudentObservationDataExport($param), $fileName, \Maatwebsite\Excel\Excel::XLSX);

    }
}
