<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RealQAssessmentQuestion;
use App\Models\RealQAssessmentSubject;
use App\Models\RealQAssessmentTopic;
use Illuminate\Http\Request;

class RealQAssessmentSubjectController extends Controller
{
    public function index()
    {
        $subjects = RealQAssessmentSubject::orderBy('name')->get()->map(function ($subject) {
            $subject->can_delete = !$this->isSubjectInUse((int) $subject->id);
            return $subject;
        });

        return view('backend.realq_assessment.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('backend.realq_assessment.subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        RealQAssessmentSubject::create([
            'name' => $request->name,
        ]);

        return redirect()->route('backend.realqassessment.subjects.index')
            ->with('success', 'Subject added successfully.');
    }

    public function edit($id)
    {
        $subject = RealQAssessmentSubject::findOrFail($id);

        return view('backend.realq_assessment.subjects.edit', compact('subject'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'required|integer',
        ]);

        $subject = RealQAssessmentSubject::findOrFail($request->subject_id);
        $subject->name = $request->name;
        $subject->save();

        return redirect()->route('backend.realqassessment.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy($id)
    {
        $subject = RealQAssessmentSubject::findOrFail($id);

        if ($this->isSubjectInUse((int) $id)) {
            return redirect()->route('backend.realqassessment.subjects.index')
                ->with('error', 'Subject is in use and cannot be deleted.');
        }

        $subject->delete();

        return redirect()->route('backend.realqassessment.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    private function isSubjectInUse(int $subjectId): bool
    {
        return RealQAssessmentTopic::where('subject_id', $subjectId)->exists()
            || RealQAssessmentQuestion::where('subject_id', $subjectId)->exists();
    }
}
