<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectSection;
use App\Models\ProjectQuestion;
use App\Models\StudentProjectAnswer;
use App\Models\StudentProjectAttachment;

class ProjectSectionController extends Controller
{
    public function projectSectionCreate()
    {
        $nextDisplayOrder = (ProjectSection::max('display_order') ?? 0) + 1;

        return view('backend.projectSections.add_projectSection', compact('nextDisplayOrder'));
    }

    public function projectSectionStore(Request $request)
    {
        $request->validate([
            'section_title' => 'required|string|max:255',
            'display_order' => 'nullable|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        $section = new ProjectSection();
        $section->section_title = $request->section_title;
        $section->status = $request->status;
        $section->display_order = $request->filled('display_order')
            ? $request->display_order
            : (ProjectSection::max('display_order') ?? 0) + 1;
        $section->save();

        return redirect()->route('backend.projectSectionlist.projectSectionList')->with('success', 'Project Section created successfully!');
    }

    public function getProjectSection(Request $request)
    {
        $sections = ProjectSection::select('id', 'section_title', 'display_order', 'status')
            ->orderBy('display_order');

        $usedSectionIds = $this->usedSectionIds();

        return datatables()->of($sections)
        ->addColumn('status', function ($row) {
            return $row->status
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';
        })
        ->addColumn('action', function ($row) use ($usedSectionIds) {
            $actionbtn = '<div class="ActionBtns">';

            $actionbtn .= '<a href="' . route('backend.projectSectionedit.projectSectionEdit', $row->id) . '"
                  class="btn btn-info btn-sm" title="Edit">
                  <i class="fas fa-edit"></i>
               </a>';

            if ($usedSectionIds->contains($row->id)) {
                $actionbtn .= '<button type="button" class="btn btn-danger btn-sm" disabled
                      title="Cannot delete - one or more questions in this section already have student submissions">
                      <i class="fas fa-trash"></i>
                   </button>';
            } else {
                $actionbtn .= '<a href="' . route('backend.projectSectiondelete.projectSectionDelete', $row->id) . '"
                      class="btn btn-danger btn-sm delete-section"
                      id="deleteProjectSection"
                      data-id="' . $row->id . '"
                      data-url="' . route('backend.projectSectiondelete.projectSectionDelete', $row->id) . '"
                      title="Delete">
                      <i class="fas fa-trash"></i>
                   </a>';
            }

            $actionbtn .= '<a href="' . route('backend.projectQuestionlist.projectQuestionList', $row->id) . '"
                  class="btn btn-primary btn-sm" title="Add Question">
                  <i class="fas fa-plus"></i> Add Question
               </a>';

            $actionbtn .= '</div>';

            return $actionbtn;
        })
        ->rawColumns(['status', 'action'])
        ->make(true);
    }

    private function usedSectionIds()
    {
        $usedQuestionIds = StudentProjectAnswer::pluck('project_question_id')
            ->merge(StudentProjectAttachment::pluck('project_question_id'))
            ->unique();

        if ($usedQuestionIds->isEmpty()) {
            return collect();
        }

        return ProjectQuestion::whereIn('id', $usedQuestionIds)->pluck('project_section_id')->unique();
    }

    public function projectSectionList()
    {
        return view('backend.projectSections.list_projectSection');
    }

    public function projectSectionEdit($id)
    {
        $section = ProjectSection::findOrFail($id);

        return view('backend.projectSections.edit_projectSection', compact('section'));
    }

    public function projectSectionUpdate(Request $request, $id)
    {
        $section = ProjectSection::findOrFail($id);

        $request->validate([
            'section_title' => 'required|string|max:255',
            'display_order' => 'nullable|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        $section->update([
            'section_title' => $request->section_title,
            'display_order' => $request->filled('display_order') ? $request->display_order : $section->display_order,
            'status' => $request->status,
        ]);

        return redirect()->route('backend.projectSectionlist.projectSectionList')->with('success', 'Project Section updated successfully!');
    }

    public function projectSectionDelete($id)
    {
        $section = ProjectSection::findOrFail($id);

        if ($this->usedSectionIds()->contains($id)) {
            return response()->json([
                'success' => false,
                'message' => 'This section has questions with student submissions and cannot be deleted.',
            ], 422);
        }

        $section->delete();

        return response()->json(['success' => true]);
    }
}
