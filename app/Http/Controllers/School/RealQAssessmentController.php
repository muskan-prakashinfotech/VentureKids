<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSchoolReport;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\StudentBoard;
use App\Models\StudentGrade;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class RealQAssessmentController extends Controller
{
    public function index()
    {
        $schoolId = Session::get('school_id');
        $school = School::select('id')->findOrFail($schoolId);
        $assignments = RealQAssessmentSchoolAssignment::where('school_id', $schoolId)
            ->where('realq_assessment_assigned', 1)
            ->get();

        if ($assignments->isEmpty()) {
            return redirect()->route('school.dashboard')->with('message1', 'RealQ Assessment is not assigned to this school.');
        }

        $gradeIds = $assignments->pluck('realq_assessment_assigned_grade_id')->filter()->unique()->all();

        $assignedGrades = $gradeIds
            ? StudentGrade::whereIn('id', $gradeIds)->orderBy('name')->get()
            : collect();

        $assignedBoard = null;
        $boardId = $assignments->first()->realq_assessment_assigned_board_id ?? null;
        if (!empty($boardId)) {
            $assignedBoard = StudentBoard::find($boardId);
        }

        $school->realq_assessment_enabled = (int) ($assignments->first()->realq_assessment_enabled ?? 0);
        $school->realq_assessment_enabled_from = $assignments->first()->realq_assessment_enabled_from ?? null;
        $school->realq_assessment_enabled_to = $assignments->first()->realq_assessment_enabled_to ?? null;
        $latestReport = AssessmentSchoolReport::where('school_id', $schoolId)
            ->where('assessment_type', 'realq')
            ->latest('id')
            ->first();

        return view('school.realq_assessment.index', compact('school', 'assignedGrades', 'assignedBoard', 'latestReport'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'is_active' => 'required|in:0,1',
        ]);

        $schoolId = Session::get('school_id');
        $school = School::select('id')->findOrFail($schoolId);
        $assignments = RealQAssessmentSchoolAssignment::where('school_id', $schoolId)
            ->where('realq_assessment_assigned', 1)
            ->get();
        if ($assignments->isEmpty()) {
            return redirect()->route('school.dashboard')->with('message1', 'RealQ Assessment is not assigned to this school.');
        }

        $isActive = (int) $request->is_active;

        RealQAssessmentSchoolAssignment::where('school_id', $schoolId)
            ->where('realq_assessment_assigned', 1)
            ->update([
                'realq_assessment_enabled' => $isActive,
            ]);

        return redirect()->back()->with('message', 'RealQ Assessment settings updated successfully.');
    }

    public function downloadReport()
    {
        $schoolId = Session::get('school_id');
        $report = AssessmentSchoolReport::where('school_id', $schoolId)
            ->where('assessment_type', 'realq')
            ->latest('id')
            ->first();

        if (!$report) {
            return redirect()->route('school.realq-assessment.index')->with('message1', 'RealQ report not found.');
        }

        $path = public_path('tenants/' . ltrim((string) $report->report_path, '/'));
        if (!File::exists($path)) {
            return redirect()->route('school.realq-assessment.index')->with('message1', 'Report file not found.');
        }

        return response()->download($path, basename((string) $report->report_path));
    }
}
