<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StandardAssessmentCategory;
use App\Models\StandardAssessmentQuestion;
use App\Models\StandardAssessmentStudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StandardAssessmentQuestionController extends Controller
{
    public function questionList()
    {
        $categories = StandardAssessmentCategory::with('grade')
            ->orderBy('category_name')
            ->get();

        return view('backend.standard_assessment.questions.list', compact('categories'));
    }

    public function getQuestions(Request $request)
    {
        $questions = StandardAssessmentQuestion::with('category.grade')
            ->select('id', 'category_id', 'question_text', 'correct_option', 'active');

        if ($request->filled('category_id')) {
            $questions->where('category_id', $request->category_id);
        }

        return datatables()->of($questions)
            ->addColumn('category', function ($row) {
                if (!$row->category) {
                    return '-';
                }

                $grade = $row->category->grade ? $row->category->grade->grade : '-';

                return $grade . ' - ' . $row->category->category_name;
            })
            ->addColumn('question', function ($row) {
                return Str::limit($row->question_text, 100);
            })
            ->addColumn('correct_answer', function ($row) {
                return strtoupper($row->correct_option);
            })
            ->addColumn('status', function ($row) {
                return $row->active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $isLocked = $this->hasAttemptedAnswersForQuestion((int) $row->id);
                $actionbtn = '<div class="ActionBtns">';
                if ($isLocked) {
                    $actionbtn .= '<a href="' . route('backend.standard_assessment.questions.show', $row->id) . '"
                            class="btn btn-block btn-secondary btn-sm" title="View">
                            <i class="fas fa-eye"></i>
                        </a>';
                } else {
                    $actionbtn .= '<a href="' . route('backend.standard_assessment.questions.edit', $row->id) . '"
                            class="btn btn-block btn-info btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>';
                    $actionbtn .= '<a href="' . route('backend.standard_assessment.questions.delete', $row->id) . '"
                            class="btn btn-block btn-danger btn-sm delete-question"
                            id="deleteQuestion"
                            data-url="' . route('backend.standard_assessment.questions.delete', $row->id) . '">
                            <i class="fas fa-trash"></i>
                        </a>';
                }
                $actionbtn .= '</div>';

                return $actionbtn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function questionCreate()
    {
        $categories = StandardAssessmentCategory::with('grade')
            ->orderBy('category_name')
            ->get();

        return view('backend.standard_assessment.questions.create', compact('categories'));
    }

    public function questionStore(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:standard_assessment_categories,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_a_score' => 'required|integer|between:0,4',
            'option_b' => 'required|string',
            'option_b_score' => 'required|integer|between:0,4',
            'option_c' => 'required|string',
            'option_c_score' => 'required|integer|between:0,4',
            'option_d' => 'required|string',
            'option_d_score' => 'required|integer|between:0,4',
            // 'correct_option' => 'required|in:a,b,c,d',
            'active' => 'required|in:0,1',
        ]);

        StandardAssessmentQuestion::create([
            'category_id' => $request->category_id,
            'question_text' => $request->question_text,
            'option_a' => $request->option_a,
            'option_a_score' => $request->option_a_score,
            'option_b' => $request->option_b,
            'option_b_score' => $request->option_b_score,
            'option_c' => $request->option_c,
            'option_c_score' => $request->option_c_score,
            'option_d' => $request->option_d,
            'option_d_score' => $request->option_d_score,
            'correct_option' => $request->correct_option,
            'active' => $request->active,
        ]);

        return redirect()
            ->route('backend.standard_assessment.questions.list')
            ->with('success', 'Question created successfully.');
    }

    public function questionEdit($id)
    {
        $categories = StandardAssessmentCategory::with('grade')
            ->orderBy('category_name')
            ->get();
        $question = StandardAssessmentQuestion::findOrFail($id);
        if ($this->hasAttemptedAnswersForQuestion((int) $question->id)) {
            return redirect()
                ->route('backend.standard_assessment.questions.list')
                ->with('error', 'This question cannot be edited because students have attempted it.');
        }

        return view('backend.standard_assessment.questions.edit', compact('categories', 'question'));
    }

    public function questionShow($id)
    {
        $categories = StandardAssessmentCategory::with('grade')
            ->orderBy('category_name')
            ->get();
        $question = StandardAssessmentQuestion::findOrFail($id);

        return view('backend.standard_assessment.questions.show', compact('categories', 'question'));
    }

    public function questionUpdate(Request $request, $id)
    {
        $question = StandardAssessmentQuestion::findOrFail($id);
        if ($this->hasAttemptedAnswersForQuestion((int) $question->id)) {
            return redirect()
                ->route('backend.standard_assessment.questions.list')
                ->with('error', 'This question cannot be updated because students have attempted it.');
        }

        $request->validate([
            'category_id' => 'required|exists:standard_assessment_categories,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_a_score' => 'required|integer|between:0,4',
            'option_b' => 'required|string',
            'option_b_score' => 'required|integer|between:0,4',
            'option_c' => 'required|string',
            'option_c_score' => 'required|integer|between:0,4',
            'option_d' => 'required|string',
            'option_d_score' => 'required|integer|between:0,4',
            // 'correct_option' => 'required|in:a,b,c,d',
            'active' => 'required|in:0,1',
        ]);

        $question->update([
            'category_id' => $request->category_id,
            'question_text' => $request->question_text,
            'option_a' => $request->option_a,
            'option_a_score' => $request->option_a_score,
            'option_b' => $request->option_b,
            'option_b_score' => $request->option_b_score,
            'option_c' => $request->option_c,
            'option_c_score' => $request->option_c_score,
            'option_d' => $request->option_d,
            'option_d_score' => $request->option_d_score,
            'correct_option' => $request->correct_option,
            'active' => $request->active,
        ]);

        return redirect()
            ->route('backend.standard_assessment.questions.list')
            ->with('success', 'Question updated successfully.');
    }

    public function questionDelete($id)
    {
        $question = StandardAssessmentQuestion::findOrFail($id);
        if ($this->hasAttemptedAnswersForQuestion((int) $question->id)) {
            return response()->json([
                'success' => false,
                'message' => 'This question cannot be deleted because students have attempted it.',
            ], 422);
        }

        $question->delete();

        return response()->json(['success' => true]);
    }

    public function setOptionCWeightage($que_id)
    {
        $question = StandardAssessmentQuestion::findOrFail($que_id);
        
        $question->update([
            'option_a_score' => 0,
            'option_b_score' => 0,
            'option_c_score' => 1,
            'option_d_score' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question weightage updated successfully.',
            'data' => [
                'question_id' => $question->id,
                'option_a_score' => 0,
                'option_b_score' => 0,
                'option_c_score' => 1,
                'option_d_score' => 0,
            ],
        ]);
    }

    private function hasAttemptedAnswersForQuestion(int $questionId): bool
    {
        return StandardAssessmentStudentAnswer::where('question_id', $questionId)->exists();
    }
}

