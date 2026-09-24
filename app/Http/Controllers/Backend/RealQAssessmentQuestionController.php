<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RealQAssessmentQuestion;
use App\Models\RealQAssessmentSubject;
use App\Models\RealQAssessmentTopic;
use App\Models\StudentBoard;
use App\Models\StudentGrade;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealQAssessmentQuestionController extends Controller
{
    public function questionBank(Request $request)
    {
        $mode = strtolower((string) $request->get('mode', 'subjective'));
        if (!in_array($mode, ['subjective', 'mcq'], true)) {
            $mode = 'subjective';
        }

        $status = strtolower((string) $request->get('status', 'all'));
        $allowedStatuses = ['all', 'approved', 'rejected', 'pending', 'archived'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $countsQuery = RealQAssessmentQuestion::where('question_type', $mode);
        $this->applyQuestionBankFilters($countsQuery, $request, true);

        $counts = $countsQuery
            ->selectRaw("SUM(CASE WHEN moderation_status = 'approved' THEN 1 ELSE 0 END) as approved_count")
            ->selectRaw("SUM(CASE WHEN moderation_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count")
            ->selectRaw("SUM(CASE WHEN moderation_status = 'archived' THEN 1 ELSE 0 END) as archived_count")
            ->first();

        $grades = StudentGrade::orderBy('name')->get();
        $boards = StudentBoard::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        $subjects = RealQAssessmentSubject::orderBy('name')->get();
        $topics = RealQAssessmentTopic::where('moderation_status', 'approved')
            ->orderBy('topic')
            ->get();

        return view('backend.realq_assessment.questions.bank', [
            'mode' => $mode,
            'status' => $status,
            'approvedCount' => (int) ($counts->approved_count ?? 0),
            'rejectedCount' => (int) ($counts->rejected_count ?? 0),
            'archivedCount' => (int) ($counts->archived_count ?? 0),
            'grades' => $grades,
            'boards' => $boards,
            'countries' => $countries,
            'subjects' => $subjects,
            'topics' => $topics,
        ]);
    }

    public function questionBankData(Request $request)
    {
        $mode = strtolower((string) $request->get('mode', 'subjective'));
        if (!in_array($mode, ['subjective', 'mcq'], true)) {
            $mode = 'subjective';
        }

        $countsQuery = RealQAssessmentQuestion::where('question_type', $mode);
        $this->applyQuestionBankFilters($countsQuery, $request, true);
        $counts = $countsQuery
            ->selectRaw("SUM(CASE WHEN moderation_status = 'approved' THEN 1 ELSE 0 END) as approved_count")
            ->selectRaw("SUM(CASE WHEN moderation_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count")
            ->selectRaw("SUM(CASE WHEN moderation_status = 'archived' THEN 1 ELSE 0 END) as archived_count")
            ->first();

        $query = RealQAssessmentQuestion::query()
            ->leftJoin('realq_assessment_topics as topics', 'realq_assessment_questions.topic_id', '=', 'topics.id')
            ->leftJoin('realq_assessment_subjects as subjects', 'realq_assessment_questions.subject_id', '=', 'subjects.id')
            ->leftJoin('student_grade as grades', 'realq_assessment_questions.grade_id', '=', 'grades.id')
            ->leftJoin('student_board as boards', 'realq_assessment_questions.board_id', '=', 'boards.id')
            ->leftJoin('countrys as countries', 'realq_assessment_questions.country_id', '=', 'countries.id')
            ->where('realq_assessment_questions.question_type', $mode)
            ->select([
                'realq_assessment_questions.id',
                'realq_assessment_questions.question_text',
                'realq_assessment_questions.challenge',
                'realq_assessment_questions.moderation_status',
                DB::raw('topics.topic as topic_name'),
                DB::raw('subjects.name as subject_name'),
                DB::raw('grades.name as grade_name'),
                DB::raw('boards.name as board_name'),
                DB::raw('countries.name as country_name'),
            ])
            ->orderByDesc('realq_assessment_questions.id');

        $this->applyQuestionBankFilters($query, $request, false);

        return datatables()->of($query)
            ->addColumn('question', function ($row) {
                return Str::limit((string) $row->question_text, 120);
            })
            ->addColumn('status', function ($row) {
                if ($row->moderation_status === 'approved') {
                    return '<span class="badge badge-success">Approved</span>';
                }
                if ($row->moderation_status === 'rejected') {
                    return '<span class="badge badge-danger">Rejected</span>';
                }
                if ($row->moderation_status === 'archived') {
                    return '<span class="badge badge-secondary">Archived</span>';
                }
                return '<span class="badge badge-warning">Pending</span>';
            })
            ->addColumn('action', function ($row) {
                $actions = '<button type="button" class="btn btn-sm btn-primary view-question realq-question-view-btn"'
                    . ' data-id="' . (int) $row->id . '"'
                    . '>View Details</button>';

                if ($row->moderation_status === 'approved') {
                    $actions .= ' <button type="button" class="btn btn-sm realq-question-archive-btn archive-question"'
                        . ' data-id="' . (int) $row->id . '"'
                        . '>Archive Question</button>';
                }

                return $actions;
            })
            ->rawColumns(['status', 'action'])
            ->with('approvedCount', (int) ($counts->approved_count ?? 0))
            ->with('rejectedCount', (int) ($counts->rejected_count ?? 0))
            ->with('archivedCount', (int) ($counts->archived_count ?? 0))
            ->make(true);
    }

    public function questionBankShow($id)
    {
        $question = RealQAssessmentQuestion::select([
            'id',
            'question_type',
            'question_text',
            'challenge',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'correct_option',
            'moderation_status',
            'reject_reason',
        ])->find($id);

        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Question not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'question_text' => $question->question_text,
                'scenario' => $question->challenge,
                'option_a' => $question->option_a,
                'option_b' => $question->option_b,
                'option_c' => $question->option_c,
                'option_d' => $question->option_d,
                'correct_option' => $question->correct_option,
                'moderation_status' => $question->moderation_status,
                'reject_reason' => $question->reject_reason,
            ],
        ]);
    }

    public function archive($id)
    {
        $question = RealQAssessmentQuestion::find($id);
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Question not found.'], 404);
        }

        if ($question->moderation_status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Only approved questions can be archived.'], 422);
        }

        $question->moderation_status = 'archived';
        $question->save();

        return response()->json([
            'success' => true,
            'message' => 'Question archived successfully.',
        ]);
    }

    public function generatePage($topicId)
    {
        $topic = $this->getTopicContext($topicId);
        if (!$topic) {
            abort(404);
        }

        $existingQuestions = RealQAssessmentQuestion::where('topic_id', $topicId)
            ->orderBy('id')
            ->get();

        $existingSubjectiveQuestions = $existingQuestions
            ->where('question_type', 'subjective')
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'moderation_status' => $q->moderation_status ?: 'pending',
                    'question_text' => (string) ($q->question_text ?? ''),
                    'scenario' => (string) ($q->challenge ?? ''),
                    'reject_reason' => (string) ($q->reject_reason ?? ''),
                ];
            })
            ->values()
            ->all();

        $existingMcqQuestions = $existingQuestions
            ->where('question_type', 'mcq')
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'moderation_status' => $q->moderation_status ?: 'pending',
                    'scenario' => (string) ($q->challenge ?? ''),
                    'question_text' => (string) ($q->question_text ?? ''),
                    'option_a' => (string) ($q->option_a ?? ''),
                    'option_b' => (string) ($q->option_b ?? ''),
                    'option_c' => (string) ($q->option_c ?? ''),
                    'option_d' => (string) ($q->option_d ?? ''),
                    'correct_option' => (string) ($q->correct_option ?? ''),
                    'reject_reason' => (string) ($q->reject_reason ?? ''),
                ];
            })
            ->values()
            ->all();

        return view('backend.realq_assessment.questions.generate', compact(
            'topic',
            'existingSubjectiveQuestions',
            'existingMcqQuestions'
        ));
    }

    public function generate(Request $request, $topicId, OpenAIService $openAIService)
    {
        $request->validate([
            'question_type' => 'required|in:subjective,mcq',
            'question_count' => 'required|integer|min:1|max:20',
        ]);

        $topic = $this->getTopicContext($topicId);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $count = (int) $request->question_count;
        $type = $request->question_type;

        if ($type === 'subjective') {
            $parameterLines = \App\Models\RealQAssessmentParameter::orderBy('name')->get()
                ->map(function ($row) {
                    $name = (string) ($row->name ?? '');
                    $desc = (string) ($row->description ?? '');
                    return trim($name) !== '' ? ($name . ($desc !== '' ? ' - ' . $desc : '')) : '';
                })
                ->filter()
                ->values()
                ->all();
            if (!empty($parameterLines)) {
                $parameterLines = array_map(function ($line, $idx) {
                    return ($idx + 1) . '. ' . $line;
                }, $parameterLines, array_keys($parameterLines));
            }
            $parameters = !empty($parameterLines) ? implode("\n", $parameterLines) : '1. Problem Awareness - Notice real problems';

            $prompt = $openAIService->buildSubjectiveQuestionPrompt(
                $topic->grade_name ?? '',
                $topic->board_name ?? '',
                $topic->country_name ?? '',
                $topic->subject_name ?? '',
                $topic->topic ?? '',
                $parameters,
                $count
            );
            $rawResponse = $openAIService->generateSubjectiveQuestions($prompt, 2200);
            $questions = $this->parseSubjectiveQuestions((string) $rawResponse);
        } else {
            $prompt = $openAIService->buildMcqQuestionPrompt(
                $topic->grade_name ?? '',
                $topic->board_name ?? '',
                $topic->country_name ?? '',
                $topic->subject_name ?? '',
                $topic->topic ?? '',
                $count
            );
            $rawResponse = $openAIService->generateMcqQuestions($prompt, 2200);
            $questions = $this->parseMcqQuestions((string) $rawResponse);
        }

        if (empty($rawResponse) || empty($questions)) {
            return response()->json(['success' => false, 'message' => 'No valid questions generated.'], 422);
        }

        return response()->json([
            'success' => true,
            'question_type' => $type,
            'prompt' => $prompt,
            'raw_response' => $rawResponse,
            'questions' => $questions,
        ]);
    }

    public function store(Request $request, $topicId)
    {
        $request->validate([
            'question_type' => 'required|in:subjective,mcq',
            'prompt' => 'nullable|string',
            'raw_response' => 'nullable|string',
            'questions' => 'required|array|min:1',
        ]);

        $topic = RealQAssessmentTopic::find($topicId);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $type = $request->question_type;
        $created = 0;
        $updated = 0;

        foreach ($request->questions as $item) {
            $moderationStatus = strtolower(trim((string) ($item['moderation_status'] ?? 'pending')));
            if (!in_array($moderationStatus, ['pending', 'approved', 'rejected', 'archived'], true)) {
                $moderationStatus = 'pending';
            }

            $questionText = $type === 'subjective'
                ? trim((string) ($item['question_text'] ?? ''))
                : trim((string) ($item['question_text'] ?? ''));
            if ($questionText === '') {
                continue;
            }

            $data = [
                'topic_id' => $topic->id,
                'grade_id' => $topic->grade_id,
                'board_id' => $topic->board_id,
                'country_id' => $topic->country_id,
                'subject_id' => $topic->subject_id,
                'question_type' => $type,
                'moderation_status' => $moderationStatus,
                'reject_reason' => ($moderationStatus === 'rejected')
                    ? trim((string) ($item['reject_reason'] ?? ''))
                    : null,
                'question_text' => $questionText,
                'challenge' => $type === 'subjective'
                    ? ($item['scenario'] ?? null)
                    : ($item['scenario'] ?? null),
                'option_a' => $item['option_a'] ?? null,
                'option_b' => $item['option_b'] ?? null,
                'option_c' => $item['option_c'] ?? null,
                'option_d' => $item['option_d'] ?? null,
                'correct_option' => $item['correct_option'] ?? null,
            ];

            if ($moderationStatus === 'rejected' && $data['reject_reason'] === '') {
                return response()->json(['success' => false, 'message' => 'Reject reason is required.'], 422);
            }

            $questionId = isset($item['id']) ? (int) $item['id'] : 0;
            if ($questionId > 0) {
                $existing = RealQAssessmentQuestion::where('id', $questionId)
                    ->where('topic_id', $topic->id)
                    ->first();

                if ($existing) {
                    $data['prompt'] = $request->prompt !== null && $request->prompt !== ''
                        ? $request->prompt
                        : $existing->prompt;
                    $data['raw_response'] = $request->raw_response !== null && $request->raw_response !== ''
                        ? $request->raw_response
                        : $existing->raw_response;

                    $existing->update($data);
                    $updated++;
                    continue;
                }
            }

            $data['prompt'] = $request->prompt;
            $data['raw_response'] = $request->raw_response;
            $data['created_by'] = Auth::id();
            RealQAssessmentQuestion::create($data);
            $created++;
        }

        if (($created + $updated) === 0) {
            return response()->json(['success' => false, 'message' => 'No valid questions to save.'], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Saved successfully. Created: {$created}, Updated: {$updated}.",
        ]);
    }

    private function getTopicContext($topicId)
    {
        return DB::table('realq_assessment_topics')
            ->leftJoin('student_grade', 'realq_assessment_topics.grade_id', '=', 'student_grade.id')
            ->leftJoin('student_board', 'realq_assessment_topics.board_id', '=', 'student_board.id')
            ->leftJoin('countrys', 'realq_assessment_topics.country_id', '=', 'countrys.id')
            ->leftJoin('realq_assessment_subjects', 'realq_assessment_topics.subject_id', '=', 'realq_assessment_subjects.id')
            ->where('realq_assessment_topics.id', $topicId)
            ->select([
                'realq_assessment_topics.id',
                'realq_assessment_topics.topic',
                'realq_assessment_topics.grade_id',
                'realq_assessment_topics.board_id',
                'realq_assessment_topics.country_id',
                'realq_assessment_topics.subject_id',
                DB::raw('student_grade.name as grade_name'),
                DB::raw('student_board.name as board_name'),
                DB::raw('countrys.name as country_name'),
                DB::raw('realq_assessment_subjects.name as subject_name'),
            ])
            ->first();
    }

    private function parseSubjectiveQuestions(string $raw): array
    {
        $decoded = $this->decodeAiJson($raw);
        if (is_array($decoded)) {
            $items = $decoded['questions'] ?? $decoded;
            if (is_array($items)) {
                $rows = [];
                foreach ($items as $q) {
                    if (!is_array($q)) {
                        continue;
                    }

                    $questionText = trim((string) (
                        $q['question']
                        ?? $q['Question']
                        ?? $q['question_text']
                        ?? $q['question_statement']
                        ?? $q['title']
                        ?? $q['challenge']
                        ?? $q['problem']
                        ?? ''
                    ));

                    $scenario = trim((string) (
                        $q['scenario']
                        ?? $q['context']
                        ?? $q['background']
                        ?? ''
                    ));

                    $rows[] = [
                        'based_topics' => $this->normalizeBasedTopics(
                            $q['based_topics'] ?? $q['based_topic'] ?? $q['topic'] ?? ''
                        ),
                        'question_text' => $questionText,
                        'scenario' => $scenario,
                    ];
                }
                return array_values(array_filter($rows, function ($row) {
                    return $row['question_text'] !== '';
                }));
            }
        }

        return [];
    }

    private function parseMcqQuestions(string $raw): array
    {
        $decoded = $this->decodeAiJson($raw);
        if (is_array($decoded)) {
            $items = $decoded['questions'] ?? $decoded['mcqs'] ?? $decoded['items'] ?? $decoded;
            if (is_array($items)) {
                // If a single question object is returned, normalize to array of one.
                if (array_keys($items) !== range(0, count($items) - 1)) {
                    $items = [$items];
                }

                $rows = [];
                foreach ($items as $q) {
                    if (!is_array($q)) {
                        continue;
                    }

                    $scenario = trim((string) (
                        $q['scenario']
                        ?? $q['context']
                        ?? $q['background']
                        ?? ''
                    ));

                    $options = $q['options'] ?? [];
                    $optionA = trim((string) (
                        $options['A']
                        ?? $options['a']
                        ?? $options[0]
                        ?? $q['A']
                        ?? $q['a']
                        ?? $q['option_a']
                        ?? ''
                    ));
                    $optionB = trim((string) (
                        $options['B']
                        ?? $options['b']
                        ?? $options[1]
                        ?? $q['B']
                        ?? $q['b']
                        ?? $q['option_b']
                        ?? ''
                    ));
                    $optionC = trim((string) (
                        $options['C']
                        ?? $options['c']
                        ?? $options[2]
                        ?? $q['C']
                        ?? $q['c']
                        ?? $q['option_c']
                        ?? ''
                    ));
                    $optionD = trim((string) (
                        $options['D']
                        ?? $options['d']
                        ?? $options[3]
                        ?? $q['D']
                        ?? $q['d']
                        ?? $q['option_d']
                        ?? ''
                    ));

                    $questionText = trim((string) (
                        $q['Question']
                        ?? $q['question']
                        ?? $q['question_text']
                        ?? $q['question_statement']
                        ?? $q['title']
                        ?? ''
                    ));

                    $rows[] = [
                        'based_topics' => $this->normalizeBasedTopics(
                            $q['based_topics'] ?? $q['based_topic'] ?? $q['topic'] ?? ''
                        ),
                        'scenario' => $scenario,
                        'question_text' => $questionText,
                        'option_a' => $optionA,
                        'option_b' => $optionB,
                        'option_c' => $optionC,
                        'option_d' => $optionD,
                        'correct_option' => strtoupper(trim((string) ($q['correct_option'] ?? ''))),
                        'explanation' => trim((string) ($q['explanation'] ?? '')),
                    ];
                }
                $rows = array_values(array_filter($rows, function ($row) {
                    return $row['question_text'] !== '';
                }));

                return array_map(function ($row) {
                    return $this->shuffleMcqOptions($row);
                }, $rows);
            }
        }

        return [];
    }

    private function shuffleMcqOptions(array $row): array
    {
        $options = [
            'A' => (string) ($row['option_a'] ?? ''),
            'B' => (string) ($row['option_b'] ?? ''),
            'C' => (string) ($row['option_c'] ?? ''),
            'D' => (string) ($row['option_d'] ?? ''),
        ];

        $correct = strtoupper(trim((string) ($row['correct_option'] ?? '')));
        if (!in_array($correct, ['A', 'B', 'C', 'D'], true)) {
            $correct = '';
        }

        $keys = ['A', 'B', 'C', 'D'];
        shuffle($keys);

        $shuffled = [];
        $newCorrect = '';
        foreach (['A', 'B', 'C', 'D'] as $newKey) {
            $oldKey = array_shift($keys);
            $shuffled[$newKey] = $options[$oldKey] ?? '';
            if ($correct !== '' && $oldKey === $correct) {
                $newCorrect = $newKey;
            }
        }

        $row['option_a'] = $shuffled['A'];
        $row['option_b'] = $shuffled['B'];
        $row['option_c'] = $shuffled['C'];
        $row['option_d'] = $shuffled['D'];
        $row['correct_option'] = $newCorrect;

        return $row;
    }

    private function decodeAiJson(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $raw, $matches)) {
            $decoded = json_decode(trim($matches[1]), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $firstBrace = strpos($raw, '{');
        $lastBrace = strrpos($raw, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $jsonCandidate = substr($raw, $firstBrace, $lastBrace - $firstBrace + 1);
            $decoded = json_decode($jsonCandidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $firstBracket = strpos($raw, '[');
        $lastBracket = strrpos($raw, ']');
        if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
            $jsonCandidate = substr($raw, $firstBracket, $lastBracket - $firstBracket + 1);
            $decoded = json_decode($jsonCandidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function normalizeBasedTopics($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_filter(array_map('trim', $value)));
        }
        return trim((string) $value);
    }

    private function applyQuestionBankFilters($query, Request $request, bool $skipStatus): void
    {
        $gradeId = (int) $request->get('grade_id');
        if ($gradeId > 0) {
            $query->where('realq_assessment_questions.grade_id', $gradeId);
        }

        $boardId = (int) $request->get('board_id');
        if ($boardId > 0) {
            $query->where('realq_assessment_questions.board_id', $boardId);
        }

        $countryId = (int) $request->get('country_id');
        if ($countryId > 0) {
            $query->where('realq_assessment_questions.country_id', $countryId);
        }

        $subjectId = (int) $request->get('subject_id');
        if ($subjectId > 0) {
            $query->where('realq_assessment_questions.subject_id', $subjectId);
        }

        $topicId = (int) $request->get('topic_id');
        if ($topicId > 0) {
            $query->where('realq_assessment_questions.topic_id', $topicId);
        }

        if (!$skipStatus) {
            $status = strtolower((string) $request->get('status', 'all'));
            if (in_array($status, ['approved', 'rejected', 'pending', 'archived'], true)) {
                $query->where('realq_assessment_questions.moderation_status', $status);
            }
        }

        $searchValue = '';
        $search = $request->get('search');
        if (is_array($search) && isset($search['value'])) {
            $searchValue = trim((string) $search['value']);
        } elseif (is_string($search)) {
            $searchValue = trim($search);
        }
        if ($searchValue !== '') {
            $query->where(function ($sub) use ($searchValue) {
                $sub->where('realq_assessment_questions.question_text', 'like', '%' . $searchValue . '%')
                    ->orWhere('realq_assessment_questions.challenge', 'like', '%' . $searchValue . '%');
            });
        }
    }
}
