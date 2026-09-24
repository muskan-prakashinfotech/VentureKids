<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RealQAssessmentParameter;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\RealQStudentParameterScore;
use Illuminate\Http\Request;

class RealQAssessmentParameterController extends Controller
{
    public function index()
    {
        $parameters = RealQAssessmentParameter::orderBy('name')->get()->map(function ($parameter) {
            $parameter->can_delete = !$this->isParameterInUse((int) $parameter->id);
            return $parameter;
        });

        return view('backend.realq_assessment.parameters.index', compact('parameters'));
    }

    public function create()
    {
        return view('backend.realq_assessment.parameters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        RealQAssessmentParameter::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('backend.realqassessment.parameters.index')
            ->with('success', 'Parameter added successfully.');
    }

    public function edit($id)
    {
        $parameter = RealQAssessmentParameter::findOrFail($id);

        return view('backend.realq_assessment.parameters.edit', compact('parameter'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'parameter_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $parameter = RealQAssessmentParameter::findOrFail($request->parameter_id);
        $parameter->name = $request->name;
        $parameter->description = $request->description;
        $parameter->save();

        return redirect()->route('backend.realqassessment.parameters.index')
            ->with('success', 'Parameter updated successfully.');
    }

    public function destroy($id)
    {
        $parameter = RealQAssessmentParameter::findOrFail($id);

        if ($this->isParameterInUse((int) $id)) {
            return redirect()->route('backend.realqassessment.parameters.index')
                ->with('error', 'Parameter is in use and cannot be deleted.');
        }

        $parameter->delete();

        return redirect()->route('backend.realqassessment.parameters.index')
            ->with('success', 'Parameter deleted successfully.');
    }

    private function isParameterInUse(int $parameterId): bool
    {
        $parameterIdString = (string) $parameterId;

        return RealQStudentParameterScore::where('parameter_id', $parameterId)->exists()
            || RealQAssessmentSchoolAssignment::where(function ($query) use ($parameterIdString) {
                $query->where('realq_assessment_assigned_parameters_id', $parameterIdString)
                    ->orWhere('realq_assessment_assigned_parameters_id', 'like', $parameterIdString . ',%')
                    ->orWhere('realq_assessment_assigned_parameters_id', 'like', '%,' . $parameterIdString . ',%')
                    ->orWhere('realq_assessment_assigned_parameters_id', 'like', '%,' . $parameterIdString);
            })->exists();
    }
}
