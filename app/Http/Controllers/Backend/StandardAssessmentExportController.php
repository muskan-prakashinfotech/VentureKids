<?php

namespace App\Http\Controllers\Backend;

use App\Exports\StandardAssessmentExport;
use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StandardAssessmentExportController extends Controller
{
    public function index()
    {
        $schools = applyCountryScope(
            School::select('id', 'school_name')->orderBy('school_name'),
            'country_id'
        )->get();

        return view('backend.standard_assessment.export', compact('schools'));
    }

    public function export(Request $request)
    {
        $schoolId = $request->get('school_id');
        $fileName = 'standard_assessment_export.xlsx';

        return Excel::download(new StandardAssessmentExport($schoolId), $fileName);
    }
}

