<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RealQAssessmentRubric;
use App\Models\RealQAssessmentScale;
use App\Models\RealQStudentParameterScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealQAssessmentRubricController extends Controller
{
    public function index()
    {
        $rubrics = RealQAssessmentRubric::with('scale')->orderBy('name')->get()->map(function ($rubric) {
            $rubric->can_delete = !$this->isRubricInUse((int) $rubric->id);
            return $rubric;
        });

        return view('backend.realq_assessment.rubrics.index', compact('rubrics'));
    }

    public function create()
    {
        $scales = RealQAssessmentScale::orderBy('id')->get();

        return view('backend.realq_assessment.rubrics.create', compact('scales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'scale_id' => 'required|integer|exists:realq_assessment_scale,id',
            'rubrics' => 'required|array|min:1',
            'rubrics.*.score' => 'required|integer|min:1|max:10',
            'rubrics.*.name' => 'required|string|max:255',
            'rubrics.*.description' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->rubrics as $rubric) {
                RealQAssessmentRubric::create([
                    'scale_id' => $request->scale_id,
                    'score' => $rubric['score'],
                    'name' => $rubric['name'],
                    'description' => $rubric['description'] ?? null,
                ]);
            }
        });

        return redirect()->route('backend.realqassessment.rubrics.index')
            ->with('success', 'Rubrics added successfully.');
    }

    public function edit($id)
    {
        $rubric = RealQAssessmentRubric::findOrFail($id);
        $scales = RealQAssessmentScale::orderBy('id')->get();

        return view('backend.realq_assessment.rubrics.edit', compact('rubric', 'scales'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'scale_id' => 'required|integer|exists:realq_assessment_scale,id',
            'rubrics' => 'required|array|min:1',
            'rubrics.*.id' => 'nullable|integer|exists:realq_assessment_rubrics,id',
            'rubrics.*.score' => 'required|integer|min:1|max:10',
            'rubrics.*.name' => 'required|string|max:255',
            'rubrics.*.description' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->rubrics as $rubricData) {
                $rubric = !empty($rubricData['id'])
                    ? RealQAssessmentRubric::findOrFail($rubricData['id'])
                    : new RealQAssessmentRubric();

                $rubric->scale_id = $request->scale_id;
                $rubric->score = $rubricData['score'];
                $rubric->name = $rubricData['name'];
                $rubric->description = $rubricData['description'] ?? null;
                $rubric->save();
            }
        });

        return redirect()->route('backend.realqassessment.rubrics.index')
            ->with('success', 'Rubrics saved successfully.');
    }

    public function destroy($id)
    {
        $rubric = RealQAssessmentRubric::findOrFail($id);

        if ($this->isRubricInUse((int) $id)) {
            return redirect()->route('backend.realqassessment.rubrics.index')
                ->with('error', 'Rubric is in use and cannot be deleted.');
        }

        $rubric->delete();

        return redirect()->route('backend.realqassessment.rubrics.index')
            ->with('success', 'Rubric deleted successfully.');
    }

    private function isRubricInUse(int $rubricId): bool
    {
        return RealQStudentParameterScore::where('rubric_id', $rubricId)->exists();
    }
}
