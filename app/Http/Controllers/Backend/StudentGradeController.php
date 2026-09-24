<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AssessmentStudentReport;
use App\Models\ProjectTheme;
use App\Models\RealQAssessmentQuestion;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\RealQAssessmentTopic;
use App\Models\StudentGrade;
use App\Models\Students;
use Illuminate\Http\Request;

class StudentGradeController extends Controller
{
    public function index()
    {
        $grades = StudentGrade::orderBy('name')->get()->map(function ($grade) {
            $grade->can_delete = !$this->isGradeInUse((int) $grade->id);
            return $grade;
        });

        return view('backend.realq_assessment.grades.index', compact('grades'));
    }

    public function create()
    {
        return view('backend.realq_assessment.grades.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        StudentGrade::create([
            'name' => $request->name,
        ]);

        return redirect()->route('backend.realqassessment.grades.index')
            ->with('success', 'Grade added successfully.');
    }

    public function edit($id)
    {
        $grade = StudentGrade::findOrFail($id);

        return view('backend.realq_assessment.grades.edit', compact('grade'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grade_id' => 'required|integer',
        ]);

        $grade = StudentGrade::findOrFail($request->grade_id);
        $grade->name = $request->name;
        $grade->save();

        return redirect()->route('backend.realqassessment.grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    public function destroy($id)
    {
        $grade = StudentGrade::findOrFail($id);

        if ($this->isGradeInUse((int) $id)) {
            return redirect()->route('backend.realqassessment.grades.index')
                ->with('error', 'Grade is in use and cannot be deleted.');
        }

        $grade->delete();

        return redirect()->route('backend.realqassessment.grades.index')
            ->with('success', 'Grade deleted successfully.');
    }

    private function isGradeInUse(int $gradeId): bool
    {
        return Students::where('student_grade_id', $gradeId)->exists()
            || ProjectTheme::where('student_grade_id', $gradeId)->exists()
            || AssessmentStudentReport::where('student_grade_id', $gradeId)->exists()
            || RealQAssessmentQuestion::where('grade_id', $gradeId)->exists()
            || RealQAssessmentSchoolAssignment::where('realq_assessment_assigned_grade_id', $gradeId)->exists()
            || RealQAssessmentTopic::where('grade_id', $gradeId)->exists();
    }
}
