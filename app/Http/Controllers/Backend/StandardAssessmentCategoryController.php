<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\StandardAssessmentCategory;
use App\Models\StandardAssessmentQuestion;
use App\Models\StandardAssessmentStudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StandardAssessmentCategoryController extends Controller
{
    public function categoryList()
    {
        return view('backend.standard_assessment.categories.list');
    }

    public function getCategories(Request $request)
    {
        $categories = StandardAssessmentCategory::with('grade')
            ->select('id', 'grade_id', 'category_name', 'active');

        return datatables()->of($categories)
            ->addColumn('level', function ($row) {
                return $row->grade ? $row->grade->grade : '-';
            })
            ->addColumn('status', function ($row) {
                return $row->active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $isLocked = $this->hasAttemptedAnswersForCategory((int) $row->id);
                $actionbtn = '<div class="ActionBtns">';
                if ($isLocked) {
                    $actionbtn .= '<button type="button" class="btn btn-block btn-secondary btn-sm" disabled title="Attempted by students. Edit locked.">
                            <i class="fas fa-lock"></i>
                        </button>';
                } else {
                    $actionbtn .= '<a href="' . route('backend.standard_assessment.categories.edit', $row->id) . '"
                            class="btn btn-block btn-info btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>';
                    $actionbtn .= '<a href="' . route('backend.standard_assessment.categories.delete', $row->id) . '"
                            class="btn btn-block btn-danger btn-sm delete-category"
                            id="deleteCategory"
                            data-url="' . route('backend.standard_assessment.categories.delete', $row->id) . '">
                            <i class="fas fa-trash"></i>
                        </a>';
                }
                $actionbtn .= '</div>';

                return $actionbtn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function categoryCreate()
    {
        $grades = Grade::orderBy('grade')->get();

        return view('backend.standard_assessment.categories.create', compact('grades'));
    }

    public function categoryStore(Request $request)
    {
        $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'category_name' => [
                'required',
                'string',
                'max:255',
            ],
            'active' => 'required|in:0,1',
        ]);

        StandardAssessmentCategory::create([
            'grade_id' => $request->grade_id,
            'category_name' => $request->category_name,
            'active' => $request->active,
        ]);

        return redirect()
            ->route('backend.standard_assessment.categories.list')
            ->with('success', 'Category created successfully.');
    }

    public function categoryEdit($id)
    {
        $grades = Grade::orderBy('grade')->get();
        $category = StandardAssessmentCategory::findOrFail($id);
        if ($this->hasAttemptedAnswersForCategory((int) $category->id)) {
            return redirect()
                ->route('backend.standard_assessment.categories.list')
                ->with('error', 'This category cannot be edited because students have attempted related questions.');
        }

        return view('backend.standard_assessment.categories.edit', compact('grades', 'category'));
    }

    public function categoryUpdate(Request $request, $id)
    {
        $category = StandardAssessmentCategory::findOrFail($id);
        if ($this->hasAttemptedAnswersForCategory((int) $category->id)) {
            return redirect()
                ->route('backend.standard_assessment.categories.list')
                ->with('error', 'This category cannot be updated because students have attempted related questions.');
        }

        $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'category_name' => [
                'required',
                'string',
                'max:255',
            ],
            'active' => 'required|in:0,1',
        ]);

        $category->update([
            'grade_id' => $request->grade_id,
            'category_name' => $request->category_name,
            'active' => $request->active,
        ]);

        return redirect()
            ->route('backend.standard_assessment.categories.list')
            ->with('success', 'Category updated successfully.');
    }

    public function categoryDelete($id)
    {
        if ($this->hasAttemptedAnswersForCategory((int) $id)) {
            return response()->json([
                'success' => false,
                'message' => 'This category cannot be deleted because students have attempted related questions.',
            ], 422);
        }

        DB::transaction(function () use ($id) {
            StandardAssessmentQuestion::where('category_id', $id)->delete();

            $category = StandardAssessmentCategory::findOrFail($id);
            $category->delete();
        });

        return response()->json(['success' => true]);
    }

    private function hasAttemptedAnswersForCategory(int $categoryId): bool
    {
        $questionIds = StandardAssessmentQuestion::where('category_id', $categoryId)->pluck('id');
        if ($questionIds->isEmpty()) {
            return false;
        }

        return StandardAssessmentStudentAnswer::whereIn('question_id', $questionIds)->exists();
    }
}

