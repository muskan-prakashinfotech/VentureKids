<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RealQAssessmentQuestion;
use App\Models\StudentBoard;
use App\Models\StudentGrade;
use App\Models\RealQAssessmentSubject;
use App\Models\RealQAssessmentTopic;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RealQAssessmentTopicController extends Controller
{
    public function index()
    {
        $grades = StudentGrade::orderBy('name')->get(['id', 'name']);
        $boards = StudentBoard::orderBy('name')->get(['id', 'name']);
        $countries = Country::orderBy('name')->get(['id', 'name']);
        $subjects = RealQAssessmentSubject::orderBy('name')->get(['id', 'name']);

        $gradeMap = $grades->pluck('name', 'id');
        $boardMap = $boards->pluck('name', 'id');
        $countryMap = $countries->pluck('name', 'id');
        $subjectMap = $subjects->pluck('name', 'id');

        return view('backend.realq_assessment.topics.index', compact(
            'grades',
            'boards',
            'countries',
            'subjects',
            'gradeMap',
            'boardMap',
            'countryMap',
            'subjectMap'
        ));
    }

    public function list()
    {
        $query = DB::table('realq_assessment_topics')
            ->leftJoin('student_grade', 'realq_assessment_topics.grade_id', '=', 'student_grade.id')
            ->leftJoin('student_board', 'realq_assessment_topics.board_id', '=', 'student_board.id')
            ->leftJoin('countrys', 'realq_assessment_topics.country_id', '=', 'countrys.id')
            ->leftJoin('realq_assessment_subjects', 'realq_assessment_topics.subject_id', '=', 'realq_assessment_subjects.id')
            ->select([
                'realq_assessment_topics.id',
                'realq_assessment_topics.topic',
                'realq_assessment_topics.moderation_status',
                'realq_assessment_topics.created_at',
                DB::raw('student_grade.name as grade'),
                DB::raw('student_board.name as board'),
                DB::raw('countrys.name as country'),
                DB::raw('realq_assessment_subjects.name as subject'),
            ])
            ->orderByDesc('realq_assessment_topics.id');

        return datatables()->of($query)
            ->addColumn('status', function ($row) {
                $status = $row->moderation_status ?: 'pending';
                $class = 'badge badge-secondary';
                if ($status === 'approved') {
                    $class = 'badge badge-success';
                } elseif ($status === 'rejected') {
                    $class = 'badge badge-danger';
                }
                return '<span class="'.$class.'">'.ucfirst($status).'</span>';
            })
            ->addColumn('action', function ($row) {
                $deleteUrl = route('backend.realqassessment.topics.delete', $row->id);
                $questionUrl = route('backend.realqassessment.questions.page', $row->id);
                $canDelete = !RealQAssessmentQuestion::where('topic_id', $row->id)->exists();
                $generateBtn = '';
                if (($row->moderation_status ?? 'pending') === 'approved') {
                    $generateBtn = '<a href="'.$questionUrl.'" class="btn btn-primary btn-sm mr-2">Generate Questions</a>';
                } else {
                    $generateBtn = '<button class="btn btn-secondary btn-sm mr-2" disabled title="Only approved topics can generate questions.">Generate Questions</button>';
                }
                $deleteBtn = $canDelete
                    ? '<button class="btn btn-danger btn-sm delete-topic" data-url="'.$deleteUrl.'"><i class="fas fa-trash-alt"></i></button>'
                    : '<button class="btn btn-danger btn-sm" disabled title="Topic is in use and cannot be deleted."><i class="fas fa-trash-alt"></i></button>';
                return $generateBtn.$deleteBtn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function generate(Request $request, OpenAIService $openAIService)
    {
        $request->validate([
            'grade_id' => 'required|integer',
            'board_id' => 'required|integer',
            'country_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'topic_count' => 'nullable|integer|min:1|max:50',
        ]);

        $grade = StudentGrade::find($request->grade_id);
        $board = StudentBoard::find($request->board_id);
        $country = Country::find($request->country_id);
        $subject = RealQAssessmentSubject::find($request->subject_id);

        if (!$grade || !$board || !$country || !$subject) {
            $message = 'Invalid grade, board, or country selection.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('backend.realqassessment.topics.index')
                ->with('error', $message);
        }

        $topicCount = (int) ($request->topic_count ?? 10);
        $prompt = $openAIService->buildTopicPrompt($grade->name, $board->name, $country->name, $subject->name, $topicCount);

        if ($prompt === '') {
            $message = 'Topic prompt is not configured yet.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('backend.realqassessment.topics.index')
                ->with('error', $message);
        }

        $rawResponse = $openAIService->generateTopics($prompt, 1500);

        if (!$rawResponse) {
            $message = 'Failed to generate topics. Please try again.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return redirect()->route('backend.realqassessment.topics.index')
                ->with('error', $message);
        }

        $topics = $this->parseTopics($rawResponse);
        $topics = array_slice($topics, 0, $topicCount);

        if (empty($topics)) {
            $message = 'No topics found in AI response.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('backend.realqassessment.topics.index')
                ->with('error', $message);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Topics generated successfully.',
                'topics' => $topics,
                'prompt' => $prompt,
                'raw_response' => $rawResponse,
            ]);
        }

        return redirect()->route('backend.realqassessment.topics.index')
            ->with('success', 'Topics generated successfully.');
    }

    public function storeGenerated(Request $request)
    {
        $validated = $request->validate([
            'grade_id' => 'required|integer',
            'board_id' => 'required|integer',
            'country_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'topic' => 'required|string|max:500',
            'moderation_status' => 'required|in:pending,approved,rejected',
            'prompt' => 'nullable|string',
            'raw_response' => 'nullable|string',
        ]);

        $topic = RealQAssessmentTopic::create([
            'grade_id' => $validated['grade_id'],
            'board_id' => $validated['board_id'],
            'country_id' => $validated['country_id'],
            'subject_id' => $validated['subject_id'],
            'topic' => $validated['topic'],
            'moderation_status' => $validated['moderation_status'],
            'prompt' => $validated['prompt'] ?? null,
            'raw_response' => $validated['raw_response'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'topic_id' => $topic->id,
            'moderation_status' => $topic->moderation_status,
        ]);
    }

    private function parseTopics(string $rawResponse): array
    {
        $json = $this->extractJson($rawResponse);
        if ($json === null) {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        if (isset($decoded['topics']) && is_array($decoded['topics'])) {
            $topics = [];
            foreach ($decoded['topics'] as $item) {
                if (is_string($item)) {
                    $topics[] = trim($item);
                }
            }
            return array_values(array_filter($topics));
        }

        $topics = [];
        foreach ($decoded as $item) {
            if (is_string($item)) {
                $topics[] = trim($item);
            }
        }

        return array_values(array_filter($topics));
    }

    private function extractJson(string $raw): ?string
    {
        if (preg_match('/```(?:json)?\\s*(\\{.*\\}|\\[.*\\])\\s*```/s', $raw, $m)) {
            return $m[1];
        }
        if (preg_match('/(\\{.*\\})/s', $raw, $m)) {
            return $m[1];
        }
        if (preg_match('/(\\[.*\\])/s', $raw, $m)) {
            return $m[1];
        }
        return null;
    }

    public function destroy($id)
    {
        $topic = RealQAssessmentTopic::findOrFail($id);

        if ($this->isTopicInUse((int) $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Topic is in use and cannot be deleted.',
            ], 422);
        }

        $topic->delete();

        return response()->json(['success' => true]);
    }

    private function isTopicInUse(int $topicId): bool
    {
        return RealQAssessmentQuestion::where('topic_id', $topicId)->exists();
    }
}
