<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StandardAssessmentController extends Controller
{
    public function index()
    {
        $schoolId = Session::get('school_id');
        $school = School::select(
            'id',
            'standard_assessment_assigned',
            'standard_assessment_enabled',
            'standard_assessment_enabled_from',
            'standard_assessment_enabled_to'
        )->findOrFail($schoolId);

        if ((int) $school->standard_assessment_assigned !== 1) {
            return redirect()->route('school.dashboard')->with('message1', 'Standard Assessment is not assigned to this school.');
        }

        return view('school.standard_assessment.index', compact('school'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'is_active' => 'required|in:0,1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $schoolId = Session::get('school_id');
        $school = School::select('id', 'standard_assessment_assigned')->findOrFail($schoolId);
        if ((int) $school->standard_assessment_assigned !== 1) {
            return redirect()->route('school.dashboard')->with('message1', 'Standard Assessment is not assigned to this school.');
        }

        $isActive = (int) $request->is_active;

        if ($isActive === 1 && (empty($request->start_date) || empty($request->end_date))) {
            return redirect()->back()->withErrors([
                'start_date' => 'Start date and end date are required when activation is enabled.',
            ])->withInput();
        }

        $school->standard_assessment_enabled = $isActive;
        $school->standard_assessment_enabled_from = $isActive ? $request->start_date : null;
        $school->standard_assessment_enabled_to = $isActive ? $request->end_date : null;
        $school->save();

        return redirect()->back()->with('message', 'Standard Assessment settings updated successfully.');
    }
}

