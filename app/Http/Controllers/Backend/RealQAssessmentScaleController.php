<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RealQAssessmentRubric;
use App\Models\RealQAssessmentScale;
use App\Models\RealQAssessmentSchoolAssignment;
use Illuminate\Http\Request;

class RealQAssessmentScaleController extends Controller
{
    public function index()
    {
        $scales = RealQAssessmentScale::orderBy('id')->get()->map(function ($scale) {
            $scale->can_delete = !$this->isScaleInUse((int) $scale->id);
            return $scale;
        });

        return view('backend.realq_assessment.scales.index', compact('scales'));
    }

    public function create()
    {
        return view('backend.realq_assessment.scales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        RealQAssessmentScale::create([
            'name' => $request->name,
        ]);

        return redirect()->route('backend.realqassessment.scales.index')
            ->with('success', 'Scale added successfully.');
    }

    public function edit($id)
    {
        $scale = RealQAssessmentScale::findOrFail($id);

        return view('backend.realq_assessment.scales.edit', compact('scale'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'scale_id' => 'required|integer|exists:realq_assessment_scale,id',
            'name' => 'required|string|max:255',
        ]);

        $scale = RealQAssessmentScale::findOrFail($request->scale_id);
        $scale->name = $request->name;
        $scale->save();

        return redirect()->route('backend.realqassessment.scales.index')
            ->with('success', 'Scale updated successfully.');
    }

    public function destroy($id)
    {
        $scale = RealQAssessmentScale::findOrFail($id);

        if ($this->isScaleInUse((int) $id)) {
            return redirect()->route('backend.realqassessment.scales.index')
                ->with('error', 'Scale is in use and cannot be deleted.');
        }

        $scale->delete();

        return redirect()->route('backend.realqassessment.scales.index')
            ->with('success', 'Scale deleted successfully.');
    }

    private function isScaleInUse(int $scaleId): bool
    {
        return RealQAssessmentRubric::where('scale_id', $scaleId)->exists()
            || RealQAssessmentSchoolAssignment::where('realq_assessment_assigned_scale_id', $scaleId)->exists();
    }
}
