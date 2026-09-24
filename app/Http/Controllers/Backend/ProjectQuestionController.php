<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectQuestion;
use App\Models\ProjectSection;
use App\Models\StudentProjectAnswer;
use App\Models\StudentProjectAttachment;

class ProjectQuestionController extends Controller
{
    public function projectQuestionList($sectionId)
    {
        $section = ProjectSection::findOrFail($sectionId);
        $attachmentQuestion = ProjectQuestion::where('project_section_id', $sectionId)
            ->where('field_type', 'file')
            ->latest('id')
            ->first();

        return view('backend.projectQuestions.list_projectQuestion', compact('section', 'attachmentQuestion'));
    }

    public function toggleAllowAttachments(Request $request, $id)
    {
        $request->validate([
            'allow_attachments' => 'required|in:0,1',
        ]);

        $question = ProjectQuestion::where('field_type', 'file')->findOrFail($id);
        $question->update(['allow_attachments' => $request->allow_attachments]);

        return response()->json(['success' => true]);
    }

    public function getProjectQuestion(Request $request, $sectionId)
    {
        $questions = ProjectQuestion::where('project_section_id', $sectionId)
            ->select('id', 'project_section_id', 'field_text', 'help_text', 'display_order', 'is_required', 'status')
            ->orderBy('display_order');

        $questionIds = ProjectQuestion::where('project_section_id', $sectionId)->pluck('id');
        $usedQuestionIds = $this->usedQuestionIds($questionIds);

        return datatables()->of($questions)
        ->addColumn('is_required', function ($row) {
            return $row->is_required
                ? '<span class="badge badge-info">Yes</span>'
                : '<span class="badge badge-secondary">No</span>';
        })
        ->addColumn('status', function ($row) {
            return $row->status
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';
        })
        ->addColumn('action', function ($row) use ($usedQuestionIds) {
            $actionbtn = '<div class="ActionBtns">';

            $actionbtn .= '<a href="' . route('backend.projectQuestionedit.projectQuestionEdit', $row->id) . '"
                  class="btn btn-info btn-sm" title="Edit">
                  <i class="fas fa-edit"></i>
               </a>';

            if ($usedQuestionIds->contains($row->id)) {
                $actionbtn .= '<button type="button" class="btn btn-danger btn-sm" disabled
                      title="Cannot delete - students have already submitted answers or attachments for this question">
                      <i class="fas fa-trash"></i>
                   </button>';
            } else {
                $actionbtn .= '<a href="' . route('backend.projectQuestiondelete.projectQuestionDelete', $row->id) . '"
                      class="btn btn-danger btn-sm delete-question"
                      id="deleteProjectQuestion"
                      data-id="' . $row->id . '"
                      data-url="' . route('backend.projectQuestiondelete.projectQuestionDelete', $row->id) . '"
                      title="Delete">
                      <i class="fas fa-trash"></i>
                   </a>';
            }

            $actionbtn .= '</div>';

            return $actionbtn;
        })
        ->rawColumns(['is_required', 'status', 'action'])
        ->make(true);
    }

    private function usedQuestionIds($questionIds)
    {
        $questionIds = collect($questionIds)->values();

        if ($questionIds->isEmpty()) {
            return collect();
        }

        return StudentProjectAnswer::whereIn('project_question_id', $questionIds)->pluck('project_question_id')
            ->merge(StudentProjectAttachment::whereIn('project_question_id', $questionIds)->pluck('project_question_id'))
            ->unique();
    }

    public function projectQuestionCreate($sectionId)
    {
        $section = ProjectSection::findOrFail($sectionId);
        $nextDisplayOrder = (ProjectQuestion::where('project_section_id', $section->id)->max('display_order') ?? 0) + 1;

        return view('backend.projectQuestions.add_projectQuestion', compact('section', 'nextDisplayOrder'));
    }

    public function projectQuestionStore(Request $request, $sectionId)
    {
        $section = ProjectSection::findOrFail($sectionId);

        $request->validate([
            'field_text' => 'required|string',
            'help_text' => 'nullable|string',
            'display_order' => 'nullable|integer|min:1',
            'is_required' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        $question = new ProjectQuestion();
        $question->project_section_id = $section->id;
        $question->field_text = $request->field_text;
        $question->help_text = $request->help_text;
        $question->field_type = 'input';
        $question->allow_attachments = false;
        $question->allowed_multiples = false;
        $question->is_required = $request->is_required;
        $question->status = $request->status;
        $question->display_order = $request->filled('display_order')
            ? $request->display_order
            : (ProjectQuestion::where('project_section_id', $section->id)->max('display_order') ?? 0) + 1;
        $question->save();

        return redirect()->route('backend.projectQuestionlist.projectQuestionList', $section->id)->with('success', 'Question created successfully!');
    }

    public function projectQuestionAttachmentStore(Request $request, $sectionId)
    {
        $section = ProjectSection::findOrFail($sectionId);

        $request->validate([
            'field_text' => 'required|string',
            'help_text' => 'nullable|string',
            'attachment_types' => 'required|array|min:1',
            'attachment_types.*' => 'in:pdf,images,videos',
            'is_required' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        $question = new ProjectQuestion();
        $question->project_section_id = $section->id;
        $question->field_text = $request->field_text;
        $question->help_text = $request->help_text;
        $question->field_type = 'file';
        $question->allow_attachments = 1;
        $question->allowed_types = implode(',', $request->attachment_types);
        $question->allowed_multiples = $request->boolean('allowed_multiples');
        $question->is_required = $request->is_required;
        $question->status = $request->status;
        $question->display_order = (ProjectQuestion::where('project_section_id', $section->id)->max('display_order') ?? 0) + 1;
        $question->save();

        return redirect()->route('backend.projectQuestionlist.projectQuestionList', $section->id)->with('success', 'Attachment question created successfully!');
    }

    public function projectQuestionEdit($id)
    {
        $question = ProjectQuestion::findOrFail($id);
        $section = $question->section;

        return view('backend.projectQuestions.edit_projectQuestion', compact('question', 'section'));
    }

    public function projectQuestionUpdate(Request $request, $id)
    {
        $question = ProjectQuestion::findOrFail($id);

        $rules = [
            'field_text' => 'required|string',
            'help_text' => 'nullable|string',
            'display_order' => 'nullable|integer|min:1',
            'is_required' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ];

        if ($question->field_type === 'file') {
            $rules['attachment_types'] = 'required|array|min:1';
            $rules['attachment_types.*'] = 'in:pdf,images,videos';
        }

        $request->validate($rules);

        $question->update([
            'field_text' => $request->field_text,
            'help_text' => $request->help_text,
            'display_order' => $request->filled('display_order') ? $request->display_order : $question->display_order,
            'is_required' => $request->is_required,
            'status' => $request->status,
            'allowed_types' => $question->field_type === 'file' ? implode(',', $request->attachment_types) : $question->allowed_types,
            'allowed_multiples' => $question->field_type === 'file' ? $request->boolean('allowed_multiples') : $question->allowed_multiples,
            'allow_attachments' => $question->field_type === 'file' ? $request->boolean('allow_attachments') : $question->allow_attachments,
        ]);

        return redirect()->route('backend.projectQuestionlist.projectQuestionList', $question->project_section_id)->with('success', 'Question updated successfully!');
    }

    public function projectQuestionDelete($id)
    {
        $question = ProjectQuestion::findOrFail($id);

        if ($this->usedQuestionIds([$id])->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'This question already has student submissions and cannot be deleted.',
            ], 422);
        }

        $question->delete();

        return response()->json(['success' => true]);
    }
}
