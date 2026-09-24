<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAnswer;
use App\Models\Country;
use App\Models\RealQAssessmentSubject;
use App\Models\RealQAssessmentQuestion;
use App\Models\RealQAssessmentParameter;
use App\Models\RealQAssessmentRubric;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\RealQStudentParameterScore;
use App\Models\StudentBoard;
use App\Services\OpenAIService;
use App\Models\RealQAssessmentTopic;
use Illuminate\Support\Facades\Auth;
use App\Models\AssessmentStudentReport;
use App\Models\StandardAssessmentCategory;
use App\Models\StandardAssessmentQuestion;
use App\Models\StandardAssessmentStudentAnswer;
use App\Models\Students;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StudentGrade;
use App\Helpers\StudentRewardPointsHelper;
use Illuminate\Http\JsonResponse;
use PDF;
use App\Traits\RealQReportParser;

class AssessmentController extends Controller
{
    use RealQReportParser; // Provides protected versions; class's own private methods take precedence.
    public function index()
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$student->school) {
            return redirect()->route('student.dashboard')->with('message1', 'Student or school data not found.');
        }

        $school = $student->school;

        $isEligible = false;
        $attemptCount = 0;
        $submittedCount = 0;
        $hasStarted = false;
        $totalQuestions = 0;
        $isCompleted = false;
        $canAttempt = false;
        $hasStandardReport = false;
        $grades = StudentGrade::all();

        $realqAvailable = false;
        $realqCompletedMode = null;
        $realqResumeMode = null;
        $realqReportExists = false;
        $realqReportStatus = null;
        
        if ((int) $school->standard_assessment_assigned === 1) {
            $isEligible = $this->isSchoolAssessmentAvailable($student);
            $attemptQuery = StandardAssessmentStudentAnswer::where('student_id', $student->id);
            $attemptCount = (clone $attemptQuery)->count();
            $submittedCount = (clone $attemptQuery)->where('is_submitted', 1)->count();

            $hasStarted = $attemptCount > 0;
            if ($hasStarted) {
                $totalQuestions = $attemptCount;
            } else {
                $assessmentGradeId = $this->resolveAssessmentGradeId($student);
                if ($assessmentGradeId) {
                    $categoryIds = $this->getAssessmentCategoryIds($assessmentGradeId);
                    if (!empty($categoryIds)) {
                        $totalQuestions = StandardAssessmentQuestion::whereIn('category_id', $categoryIds)
                            ->where('active', 1)
                            ->count();
                    }
                }
            }
            $isCompleted = $hasStarted && $attemptCount === $submittedCount;
            $canAttempt = $isEligible && $totalQuestions > 0 && !$isCompleted;
            $hasStandardReport = AssessmentStudentReport::where('student_id', $student->id)
                ->where('assessment_type', 'standard')
                ->exists();
        }

        $hasRealqAssignment = RealQAssessmentSchoolAssignment::where('school_id', $school->id)
            ->where('realq_assessment_assigned', 1)
            ->exists();

        if ($hasRealqAssignment) {
            $assignmentRow = RealQAssessmentSchoolAssignment::where('school_id', $school->id)
                ->where('realq_assessment_assigned', 1)
                ->where('realq_assessment_assigned_grade_id', $student->student_grade_id)
                ->first();
            
            if ($assignmentRow) {
                $countryId = $this->getStudentCountryId($student);
                $boardId = $assignmentRow->realq_assessment_assigned_board_id;
                $dateOk = true;
                if (!empty($assignmentRow->realq_assessment_enabled_from) && !empty($assignmentRow->realq_assessment_enabled_to)) {
                    $now = Carbon::today();
                    $dateOk = $now->between($assignmentRow->realq_assessment_enabled_from, $assignmentRow->realq_assessment_enabled_to);
                }

                $questionExists = false;
                if (!empty($countryId) && !empty($boardId)) {
                    $questionExists = RealQAssessmentQuestion::where('moderation_status', 'approved')
                        ->whereIn('question_type', ['subjective', 'mcq'])
                        ->where('grade_id', $student->student_grade_id)
                        ->where('board_id', $boardId)
                        ->where('country_id', $countryId)
                        ->exists();
                }

                $realqAvailable = (bool) $dateOk && (bool) $questionExists;
                $realqLatestReport = AssessmentStudentReport::where('student_id', $student->id)
                    ->where('assessment_type', 'realq')
                    ->latest('id')
                    ->first();
                $realqReportExists = $realqLatestReport !== null;
                $realqReportStatus = $realqLatestReport ? (string) ($realqLatestReport->status ?? 'pending') : null;

                $realqSubjectiveProgress = $this->getRealQProgress($student->id, 'subjective');
                $realqMcqProgress = $this->getRealQProgress($student->id, 'mcq');

                if ($realqSubjectiveProgress['completed']) {
                    $realqCompletedMode = 'subjective';
                } elseif ($realqMcqProgress['completed']) {
                    $realqCompletedMode = 'mcq';
                }

                if (!$realqCompletedMode) {
                    $candidates = [];
                    if ($realqSubjectiveProgress['started']) {
                        $candidates[] = ['mode' => 'subjective', 'updated_at' => $realqSubjectiveProgress['last_updated_at']];
                    }
                    if ($realqMcqProgress['started']) {
                        $candidates[] = ['mode' => 'mcq', 'updated_at' => $realqMcqProgress['last_updated_at']];
                    }
                    if (!empty($candidates)) {
                        usort($candidates, function ($a, $b) {
                            return strcmp((string) ($b['updated_at'] ?? ''), (string) ($a['updated_at'] ?? ''));
                        });
                        $realqResumeMode = $candidates[0]['mode'] ?? null;
                    }
                }
            }
        }

        $standardActionLabel = 'Start now';
        $standardActionUrl = null;

        if ((int) $school->standard_assessment_assigned === 1) {
            if ($hasStandardReport) {
                $standardActionLabel = 'Download Report';
                $standardActionUrl = route('student.assessment.baseline.report');
            } elseif ($canAttempt) {
                $standardActionLabel = $hasStarted ? 'Resume' : 'Start now';
                $standardActionUrl = route('student.assessment.baseline');
            }
        }

        $realqActionLabel = 'Start now';
        $realqActionUrl = null;
        if ($realqReportExists && $realqReportStatus === 'approved' && $realqCompletedMode) {
            $realqActionLabel = 'Download Report';
            $realqActionUrl = $realqCompletedMode === 'mcq'
                ? route('student.assessment.report.download', ['mode' => 'mcq'])
                : route('student.assessment.report.download');
        } elseif ($realqReportExists && in_array($realqReportStatus, ['pending', 'generated'], true)) {
            $realqActionLabel = 'Report Under Review';
            $realqActionUrl = null;
        } elseif ($realqAvailable) {
            if ($realqResumeMode === 'subjective') {
                $realqActionLabel = 'Resume';
                $realqActionUrl = route('student.assessment.subjective.setup');
            } elseif ($realqResumeMode === 'mcq') {
                $realqActionLabel = 'Resume';
                $realqActionUrl = route('student.assessment.mcq.setup');
            } else {
                $realqActionLabel = 'Start now';
                $realqActionUrl = route('student.assessment.mode');
            }
        }

        return view('student.assessment.index', compact(
            'isEligible',
            'totalQuestions',
            'hasStarted',
            'isCompleted',
            'canAttempt',
            'submittedCount',
            'hasStandardReport',
            'grades',
            'realqAvailable',
            'realqCompletedMode',
            'realqResumeMode',
            'standardActionLabel',
            'standardActionUrl',
            'realqActionLabel',
            'realqActionUrl',
            'realqReportExists',
            'realqReportStatus'
        ));
    }

    public function baseline()
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$student->school) {
            return redirect()->route('student.dashboard')->with('message1', 'Student or school data not found.');
        }

        if (!$this->isSchoolAssessmentAvailable($student)) {
            return redirect()->route('student.assessment')->with('message1', 'Standard Assessment is not active for your school right now.');
        }

        $attemptCount = StandardAssessmentStudentAnswer::where('student_id', $student->id)->count();
        if (!$attemptCount) {
            $assessmentGradeId = $this->resolveAssessmentGradeId($student);
            if (!$assessmentGradeId) {
                return redirect()->route('student.assessment')->with('message1', 'No standard assessment is available for your assigned levels.');
            }
            $categoryIds = $this->getAssessmentCategoryIds($assessmentGradeId);
            if (empty($categoryIds)) {
                return redirect()->route('student.assessment')->with('message1', 'No standard assessment categories found for your level.');
            }

            $questionIds = StandardAssessmentQuestion::whereIn('category_id', $categoryIds)
                ->where('active', 1)
                ->inRandomOrder()
                ->pluck('id')
                ->toArray();
            if (empty($questionIds)) {
                return redirect()->route('student.assessment')->with('message1', 'No assessment questions found.');
            }

            $now = Carbon::now();
            $insertRows = [];
            foreach ($questionIds as $questionId) {
                $insertRows[] = [
                    'student_id' => $student->id,
                    'question_id' => $questionId,
                    'selected_option' => null,
                    'is_submitted' => 0,
                    'started_at' => $now,
                    'answered_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('standard_assessment_student_answers')->insert($insertRows);
        }

        $nextAnswer = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->where('is_submitted', 0)
            ->orderBy('id')
            ->first();

        if (!$nextAnswer) { // Completed already
            return redirect()->route('student.assessment')->with('message', 'Successfully submitted.');
        }

        return view('student.assessment.baseline_question', [
            'initial_answer_id' => $nextAnswer->id,
        ]);
    }

    public function getBaselineQuestion(Request $request): JsonResponse
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$student->school || !$this->isSchoolAssessmentAvailable($student)) {
            return response()->json(['success' => false, 'message' => 'Assessment is not available.'], 403);
        }

        $answerId = (int) $request->query('answer_id');
        $baseAnswerQuery = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->select(['id', 'question_id', 'selected_option']);

        if ($answerId > 0) {
            $currentRow = (clone $baseAnswerQuery)->where('id', $answerId)->first();
        } else {
            $currentRow = (clone $baseAnswerQuery)
                ->where('is_submitted', 0)
                ->orderBy('id')
                ->first();

            if (!$currentRow) {
                $currentRow = (clone $baseAnswerQuery)
                    ->orderBy('id')
                    ->first();
            }
        }

        if (!$currentRow) {
            return response()->json(['success' => false, 'message' => 'No attempt found.'], 404);
        }

        $question = StandardAssessmentQuestion::select(['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d'])
            ->find($currentRow->question_id);
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Question not found.'], 404);
        }

        $previousAnswerId = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->where('id', '<', $currentRow->id)
            ->orderBy('id', 'desc')
            ->value('id');

        $nextAnswerId = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->where('id', '>', $currentRow->id)
            ->orderBy('id')
            ->value('id');

        $totalCount = StandardAssessmentStudentAnswer::where('student_id', $student->id)->count();
        $answeredCount = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->where('is_submitted', 1)
            ->count();
        $questionPosition = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->where('id', '<=', $currentRow->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'answer_id' => $currentRow->id,
                'question_text' => $question->question_text,
                'option_a' => $question->option_a,
                'option_b' => $question->option_b,
                'option_c' => $question->option_c,
                'option_d' => $question->option_d,
                'selected_option' => $currentRow->selected_option ? strtoupper($currentRow->selected_option) : null,
                'previous_answer_id' => $previousAnswerId,
                'next_answer_id' => $nextAnswerId,
                'question_position' => $questionPosition,
                'answered_count' => $answeredCount,
                'total_count' => $totalCount,
            ],
        ]);
    }

    public function baselineAction(Request $request): JsonResponse
    {
        $request->validate([
            'answer_id' => 'required|exists:standard_assessment_student_answers,id',
            'action' => 'required|in:next,prev,save_exit',
            'selected_option' => 'nullable|in:A,B,C,D',
        ]);

        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$student->school || !$this->isSchoolAssessmentAvailable($student)) {
            return response()->json(['success' => false, 'message' => 'Assessment is not available.'], 403);
        }

        $currentRow = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->where('id', (int) $request->answer_id)
            ->select(['id', 'selected_option'])
            ->first();
        if (!$currentRow) {
            return response()->json(['success' => false, 'message' => 'Answer row not found.'], 404);
        }

        $selectedOption = $request->selected_option ? strtolower($request->selected_option) : null;
        if ($selectedOption) {
            $currentRow->selected_option = $selectedOption;
            StandardAssessmentStudentAnswer::where('id', $currentRow->id)->update([
                'selected_option' => $selectedOption,
                'is_submitted' => 1,
                'answered_at' => Carbon::now(),
            ]);
        }

        if ($request->action === 'save_exit') {
            return response()->json(['success' => true, 'save_exit' => true, 'redirect_url' => route('student.assessment')]);
        }

        if ($request->action === 'next') {
            if (!$selectedOption && !$currentRow->selected_option) {
                return response()->json(['success' => false, 'message' => 'Please select an option.'], 422);
            }

            $nextAnswerId = StandardAssessmentStudentAnswer::where('student_id', $student->id)
                ->where('id', '>', $currentRow->id)
                ->orderBy('id')
                ->value('id');

            if (!$nextAnswerId) {
                $storeResult = $this->generateAndStoreBaselineReport($student);
                if (!(bool) ($storeResult['success'] ?? false)) {
                    return response()->json([
                        'success' => false,
                        'message' => $storeResult['message'] ?? 'Report generation failed.',
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'completed' => true,
                    'report_url' => route('student.assessment.baseline.report'),
                    'completion_text' => 'Use this self-assessment to identify areas where you can focus your personal development efforts. Remember, self-awareness is the first step towards improvement.',
                    'message' => 'Successfully submitted.',
                ]);
            }

            return response()->json(['success' => true, 'next_answer_id' => $nextAnswerId]);
        }

        $prevAnswerId = StandardAssessmentStudentAnswer::where('student_id', $student->id)
            ->where('id', '<', $currentRow->id)
            ->orderBy('id', 'desc')
            ->value('id');

        return response()->json(['success' => true, 'previous_answer_id' => $prevAnswerId ?: $currentRow->id]);
    }

    public function downloadBaselineReport()
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student) {
            return redirect()->route('student.assessment')->with('message1', 'Student not found.');
        }

        $latestReport = AssessmentStudentReport::where('student_id', $student->id)
            ->where('assessment_type', 'standard')
            ->latest('id')
            ->first();

        if ($latestReport && File::exists(public_path('tenants/' . $latestReport->report_path))) {
            return response()->download(
                public_path('tenants/' . $latestReport->report_path),
                basename($latestReport->report_path)
            );
        }

        $storeResult = $this->generateAndStoreBaselineReport($student);
        if (!(bool) ($storeResult['success'] ?? false)) {
            return redirect()->route('student.assessment')->with('message1', $storeResult['message'] ?? 'Report generation failed.');
        }

        $reportPath = (string) $storeResult['report_path'];
        if (!File::exists(public_path('tenants/' . $reportPath))) {
            return redirect()->route('student.assessment')->with('message1', 'Report file not found.');
        }

        return response()->download(
            public_path('tenants/' . $reportPath),
            basename($reportPath)
        );
    }

    private function getRubricByScore(int $totalScore): array
    {
        if ($totalScore <= 4) {
            return [
                'level' => 'Beginning',
                'evidence' => 'Consider seeking resources or training to develop this skill.',
            ];
        }

        if ($totalScore <= 6) {
            return [
                'level' => 'Developing',
                'evidence' => 'You have a basic level of this skill, but there is room for growth.',
            ];
        }

        if ($totalScore <= 8) {
            return [
                'level' => 'Promising',
                'evidence' => 'You are competent in this skill but can still improve.',
            ];
        }

        if ($totalScore <= 11) {
            return [
                'level' => 'Proficient',
                'evidence' => 'You are competent in this skill but can still improve.',
            ];
        }

        return [
            'level' => 'Excellent',
            'evidence' => 'You have a strong proficiency in this skill.',
        ];
    }

    private function generateAndStoreBaselineReport(Students $student): array
    {
        $reportData = $this->prepareBaselineReportData($student);
        if (!(bool) ($reportData['success'] ?? false)) {
            return $reportData;
        }

        try {
            $pdf = PDF::loadView('student.assessment.standard_report_pdf', [
                'student' => $student,
                'evaluationRows' => $reportData['evaluationRows'],
                'schoolLogoPath' => $reportData['schoolLogoPath'],
                'attemptedOn' => $reportData['attemptedOn'],
            ])->setPaper('a4', 'portrait');

            $tenantId = optional($student->school)->tenant_id ?: 'common';
            $fileName = Str::random(12) . dechex(time()) . '.pdf';
            $reportPath = trim($tenantId, '/') . '/student/reports/standard_assessment/' . $fileName;
            $absoluteDirectory = public_path('tenants/' . trim($tenantId, '/') . '/student/reports/standard_assessment');
            if (!File::exists($absoluteDirectory)) {
                File::makeDirectory($absoluteDirectory, 0755, true);
            }
            File::put(public_path('tenants/' . $reportPath), $pdf->output());

            $reportRow = AssessmentStudentReport::create([
                'student_id' => $student->id,
                'student_grade_id' => $student->student_grade_id,
                'assessment_type' => 'standard',
                'report_path' => $reportPath,
            ]);

            /* START - Pre Assessment Reward Points */
            StudentRewardPointsHelper::storeRewardPoints([
                'student_id' =>  $student->id,
                'reward_type' => 'pre_assessment_score',
                'item_id' => $reportRow->id,
                'item_type' => 'standard',
                'reward_points' => 5,
            ]);
            /* END - Pre Assessment Reward Points */

            return [
                'success' => true,
                'report_path' => $reportRow->report_path,
                'download_url' => url('tenants/' . $reportRow->report_path),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Unable to generate report PDF.',
            ];
        }
    }

    private function prepareBaselineReportData(Students $student): array
    {
        $answers = StandardAssessmentStudentAnswer::where('student_id', $student->id)->orderBy('id')->get();
        if ($answers->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No assessment attempt found.',
            ];
        }

        $totalCount = $answers->count();
        $submittedCount = $answers->where('is_submitted', 1)->count();
        if ($submittedCount !== $totalCount) {
            return [
                'success' => false,
                'message' => 'Please complete the assessment before downloading report.',
            ];
        }

        $questions = StandardAssessmentQuestion::with('category:id,category_name')
            ->whereIn('id', $answers->pluck('question_id')->unique()->toArray())
            ->get()
            ->keyBy('id');

        $categoryScores = [];
        foreach ($answers as $answer) {
            if (empty($answer->selected_option)) {
                continue;
            }

            $question = $questions->get($answer->question_id);
            if (!$question) {
                continue;
            }

            $selectedOption = strtolower((string) $answer->selected_option);
            $scoreField = 'option_' . $selectedOption . '_score';
            $earnedScore = (int) ($question->{$scoreField} ?? 0);

            $categoryId = (int) ($question->category_id ?? 0);
            $categoryName = optional($question->category)->category_name ?: 'Uncategorized';
            $categoryKey = $categoryId > 0 ? (string) $categoryId : 'uncategorized';

            if (!isset($categoryScores[$categoryKey])) {
                $categoryScores[$categoryKey] = [
                    'category_name' => $categoryName,
                    'total_score' => 0,
                ];
            }

            $categoryScores[$categoryKey]['total_score'] += $earnedScore;
        }

        $evaluationRows = [];
        foreach ($categoryScores as $scoreRow) {
            $rubric = $this->getRubricByScore((int) $scoreRow['total_score']);
            $evaluationRows[] = [
                'parameter' => $scoreRow['category_name'],
                'total_score' => (int) $scoreRow['total_score'],
                'rubric_level' => $rubric['level'],
                'evidence' => $rubric['evidence'],
            ];
        }

        usort($evaluationRows, function ($a, $b) {
            return strcmp($a['parameter'], $b['parameter']);
        });

        $schoolLogoPath = public_path('asset/images/kids-tm-logo.png');
        /*if ($student->school && !empty($student->school->school_logo)) {
            $tenantLogoPath = public_path('tenants/' . ltrim((string) $student->school->school_logo, '/'));
            if (File::exists($tenantLogoPath)) {
                $schoolLogoPath = $tenantLogoPath;
            }
        }*/

        $maxAnsweredAt = $answers->max('answered_at');
        $attemptedOn = $maxAnsweredAt ? Carbon::parse($maxAnsweredAt)->toDateString() : Carbon::today()->toDateString();

        return [
            'success' => true,
            'evaluationRows' => $evaluationRows,
            'schoolLogoPath' => $schoolLogoPath,
            'attemptedOn' => $attemptedOn,
        ];
    }

    private function isSchoolAssessmentAvailable(Students $student): bool
    {
        $school = $student->school;
        if (!$school) {
            return false;
        }

        if ((int) $school->standard_assessment_assigned !== 1) {
            return false;
        }

        if ((int) $school->standard_assessment_enabled !== 1) {
            return false;
        }

        $fromRaw = $school->standard_assessment_enabled_from;
        $toRaw = $school->standard_assessment_enabled_to;

        if (empty($fromRaw) || empty($toRaw)) {
            return false;
        }

        if (!strtotime($fromRaw) || !strtotime($toRaw)) {
            return false;
        }

        try {
            $fromDate = Carbon::parse($fromRaw)->startOfDay();
            $toDate = Carbon::parse($toRaw)->endOfDay();
        } catch (\Throwable $e) {
            return false;
        }

        $today = Carbon::today();
        return $today->between($fromDate, $toDate);
    }

    private function isSchoolRealQAssessmentAvailable(Students $student): bool
    {
        $school = $student->school;
        if (!$school) {
            return false;
        }

        $assignmentRow = RealQAssessmentSchoolAssignment::where('school_id', $school->id)
            ->where('realq_assessment_assigned', 1)
            ->where('realq_assessment_assigned_grade_id', $student->student_grade_id)
            ->first();
        if (!$assignmentRow) {
            return false;
        }

        $fromRaw = $assignmentRow->realq_assessment_enabled_from;
        $toRaw = $assignmentRow->realq_assessment_enabled_to;

        if (empty($fromRaw) || empty($toRaw)) {
            return false;
        }

        if (!strtotime($fromRaw) || !strtotime($toRaw)) {
            return false;
        }

        try {
            $fromDate = Carbon::parse($fromRaw)->startOfDay();
            $toDate = Carbon::parse($toRaw)->endOfDay();
        } catch (\Throwable $e) {
            return false;
        }

        $today = Carbon::today();
        return $today->between($fromDate, $toDate);
    }

    private function getRealQAvailabilityFailureReason(?Students $student): string
    {
        if (!$student || !$student->school) {
            return 'School data not found.';
        }
        $school = $student->school;

        $assignmentRow = RealQAssessmentSchoolAssignment::where('school_id', $school->id)
            ->where('realq_assessment_assigned', 1)
            ->where('realq_assessment_assigned_grade_id', $student->student_grade_id)
            ->first();
        if (!$assignmentRow) {
            return 'RealQ Assessment is not assigned to your school.';
        }

        $fromRaw = $assignmentRow->realq_assessment_enabled_from;
        $toRaw = $assignmentRow->realq_assessment_enabled_to;
        if (empty($fromRaw) || empty($toRaw)) {
            return 'RealQ Assessment start/end dates are not configured.';
        }

        try {
            $fromDate = Carbon::parse($fromRaw)->startOfDay();
            $toDate = Carbon::parse($toRaw)->endOfDay();
        } catch (\Throwable $e) {
            return 'RealQ Assessment dates are invalid.';
        }

        $today = Carbon::today();
        if (!$today->between($fromDate, $toDate)) {
            return 'RealQ Assessment is not active for your school right now.';
        }

        return 'RealQ Assessment is not available.';
    }

    private function getRealQProgress(int $studentId, string $mode): array
    {
        $rows = AssessmentAnswer::query()
            ->join('realq_assessment_questions', 'realq_assessment_student_answers.assessment_question_id', '=', 'realq_assessment_questions.id')
            ->where('realq_assessment_student_answers.student_id', $studentId)
            ->where('realq_assessment_student_answers.assessment_type', 'baseline')
            ->where('realq_assessment_questions.question_type', $mode)
            ->select([
                'realq_assessment_student_answers.id',
                'realq_assessment_student_answers.is_submitted',
                'realq_assessment_student_answers.updated_at',
            ])
            ->orderBy('realq_assessment_student_answers.id')
            ->get();

        $total = $rows->count();
        $submitted = $rows->where('is_submitted', 1)->count();
        $lastUpdated = $rows->max('updated_at');

        return [
            'total' => $total,
            'submitted' => $submitted,
            'started' => $total > 0,
            'completed' => $total > 0 && $submitted === $total,
            'last_updated_at' => $lastUpdated,
        ];
    }

    private function getExistingRealQAnswers(int $studentId, string $mode): array
    {
        $rows = AssessmentAnswer::query()
            ->join('realq_assessment_questions', 'realq_assessment_student_answers.assessment_question_id', '=', 'realq_assessment_questions.id')
            ->where('realq_assessment_student_answers.student_id', $studentId)
            ->where('realq_assessment_student_answers.assessment_type', 'baseline')
            ->where('realq_assessment_questions.question_type', $mode)
            ->select([
                'realq_assessment_student_answers.*',
                'realq_assessment_questions.question_text as q_question_text',
                'realq_assessment_questions.challenge as q_scenario',
                'realq_assessment_questions.option_a as q_option_a',
                'realq_assessment_questions.option_b as q_option_b',
                'realq_assessment_questions.option_c as q_option_c',
                'realq_assessment_questions.option_d as q_option_d',
            ])
            ->orderBy('realq_assessment_student_answers.id')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'total' => 0,
                'completed' => false,
                'questions' => [],
                'answers' => [],
                'current_index' => 0,
            ];
        }

        $answersMap = [];
        $questions = [];
        $currentIndex = null;
        foreach ($rows as $index => $row) {
            $answersMap[$row->assessment_question_id] = $mode === 'mcq'
                ? (string) ($row->selected_option ?? '')
                : (string) ($row->answer_text ?? '');

            if (!$row->is_submitted && $currentIndex === null) {
                $currentIndex = $index;
            }

            $questions[] = [
                'id' => (int) $row->assessment_question_id,
                'order' => $index + 1,
                'title' => 'Question ' . ($index + 1),
                'scenario' => (string) ($row->q_scenario ?? ''),
                'question_text' => (string) ($row->q_question_text ?? ''),
                'option_a' => (string) ($row->q_option_a ?? ''),
                'option_b' => (string) ($row->q_option_b ?? ''),
                'option_c' => (string) ($row->q_option_c ?? ''),
                'option_d' => (string) ($row->q_option_d ?? ''),
            ];
        }

        $total = $rows->count();
        $submitted = $rows->where('is_submitted', 1)->count();
        if ($currentIndex === null) {
            $currentIndex = 0;
        }

        return [
            'total' => $total,
            'completed' => $total > 0 && $submitted === $total,
            'questions' => $questions,
            'answers' => $answersMap,
            'current_index' => $currentIndex,
        ];
    }

    private function isStudentGradeAllowed(int $studentId, int $gradeId): bool
    {
        $student = Students::find($studentId);
        if (!$student) {
            return false;
        }

        if ($student->student_grade_id) {
            if ((int) $student->student_grade_id !== $gradeId) {
                return false;
            }

            $school = $student->school;
            if ($school) {
                $assignedGrades = $this->getRealqAssignedGradeIds($school);
                if (!empty($assignedGrades) && !in_array((int) $student->student_grade_id, $assignedGrades, true)) {
                    return false;
                }
            }
            return true;
        }

        return true;
    }

    private function validateTopicSelectionLimit(array $topicIds, int $limit): bool
    {
        $topics = RealQAssessmentTopic::whereIn('id', $topicIds)->get(['id', 'subject_id']);
        if ($topics->isEmpty()) {
            return false;
        }

        $counts = $topics->groupBy('subject_id')->map(function ($rows) {
            return $rows->count();
        });

        foreach ($counts as $count) {
            if ($count > $limit) {
                return false;
            }
        }

        return true;
    }

    private function isStudentRealQGradeAllowed(Students $student): bool
    {
        if (!$student->student_grade_id) {
            return false;
        }

        $school = $student->school;
        if (!$school) {
            return false;
        }

        $assignedGrades = $this->getRealqAssignedGradeIds($school);
        if (empty($assignedGrades)) {
            return false;
        }

        return in_array((int) $student->student_grade_id, $assignedGrades, true);
    }

    private function getRealqAssignedGradeIds($school): array
    {
        if (!$school) {
            return [];
        }
        return RealQAssessmentSchoolAssignment::where('school_id', $school->id)
            ->where('realq_assessment_assigned', 1)
            ->pluck('realq_assessment_assigned_grade_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function getRealqAssignedBoardId($school): ?int
    {
        if (!$school) {
            return null;
        }
        $row = RealQAssessmentSchoolAssignment::where('school_id', $school->id)
            ->where('realq_assessment_assigned', 1)
            ->first();
        return $row ? (int) $row->realq_assessment_assigned_board_id : null;
    }

    private function getRealqAssignedBoardName($school): string
    {
        $boardId = $this->getRealqAssignedBoardId($school);
        if (!$boardId) {
            return '';
        }
        return (string) (optional(StudentBoard::find($boardId))->name ?? '');
    }

    private function getStudentCountryId(Students $student): ?int
    {
        if (!empty($student->country_id)) {
            return (int) $student->country_id;
        }
        if ($student->school && !empty($student->school->country_id)) {
            return (int) $student->school->country_id;
        }
        return null;
    }

    private function getStudentCountryName(Students $student): string
    {
        $countryId = $this->getStudentCountryId($student);
        if (!$countryId) {
            return '';
        }
        return (string) (optional(Country::find($countryId))->name ?? '');
    }

    private function resolveAssessmentGradeId(Students $student): ?int
    {
        $gradeIds = array_filter(array_map('intval', explode(',', (string) $student->grade_id)));
        if (empty($gradeIds)) {
            return null;
        }

        $orderedGrades = \App\Models\Grade::select('id', 'assessment_order')
            ->whereIn('id', $gradeIds)
            ->orderByRaw('assessment_order IS NULL, assessment_order DESC, id DESC')
            ->get();

        $highestGrade = $orderedGrades->first();
        if (!$highestGrade) {
            return null;
        }

        return $this->getAssessmentCategoryIds((int) $highestGrade->id)
            ? (int) $highestGrade->id
            : null;

        return null;
    }

    private function getAssessmentCategoryIds(int $gradeId): array
    {
        return StandardAssessmentCategory::where('grade_id', $gradeId)
            ->pluck('id')
            ->toArray();
    }

    public function baselineMode()
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$student->school) {
            return redirect()->route('student.dashboard')->with('message1', 'Student or school data not found.');
        }

        if (!$this->isSchoolRealQAssessmentAvailable($student)) {
            return redirect()->route('student.assessment')->with('message1', $this->getRealQAvailabilityFailureReason($student));
        }

        if (!$this->isStudentRealQGradeAllowed($student)) {
            return redirect()->route('student.assessment')->with('message1', 'RealQ Assessment is not assigned for your grade.');
        }

        $subjectiveProgress = $this->getRealQProgress($student->id, 'subjective');
        $mcqProgress = $this->getRealQProgress($student->id, 'mcq');

        $completedMode = null;
        if ($subjectiveProgress['completed']) {
            $completedMode = 'subjective';
        } elseif ($mcqProgress['completed']) {
            $completedMode = 'mcq';
        }

        $resumeMode = null;
        if (!$completedMode) {
            if ($subjectiveProgress['started']) {
                $resumeMode = 'subjective';
            } elseif ($mcqProgress['started']) {
                $resumeMode = 'mcq';
            }
        }

        return view('student.assessment.baseline_mode', compact('completedMode', 'resumeMode'));
    }

    public function saveBaselineMode(Request $request)
    {
        $validated = $request->validate([
            'assessment_mode' => 'required|in:mcq,subjective',
        ]);

        $student = Students::with('school')->find(Session::get('student_id'));
        if ($student) {
            $progress = $this->getRealQProgress($student->id, $validated['assessment_mode']);
            if ($progress['completed']) {
                return redirect()
                    ->route('student.assessment.report', $validated['assessment_mode'] === 'mcq' ? ['mode' => 'mcq'] : [])
                    ->with('message', 'You have already completed this assessment.');
            }
        }

        session(['baseline_assessment_mode' => $validated['assessment_mode']]);

        if ($validated['assessment_mode'] === 'subjective') {
            return redirect()->route('student.assessment.subjective.setup');
        }

        return redirect()->route('student.assessment.mcq.setup');
    }

    public function subjectiveSetup()
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$this->isSchoolRealQAssessmentAvailable($student)) {
            return redirect()->route('student.assessment')->with('message1', $this->getRealQAvailabilityFailureReason($student));
        }
        if (!$this->isStudentRealQGradeAllowed($student)) {
            return redirect()->route('student.assessment')->with('message1', 'RealQ Assessment is not assigned for your grade.');
        }
        $progress = $this->getRealQProgress($student->id, 'subjective');
        $resumeRedirect = $progress['started'] && !$progress['completed'];
        $assignedGrade = null;
        if ($student && $student->student_grade_id) {
            $assignedGrade = StudentGrade::find($student->student_grade_id);
        }
        $grades = $assignedGrade ? collect([$assignedGrade]) : collect();
        $subjects = RealQAssessmentSubject::orderBy('name')->get(['id', 'name']);

        $boardName = $this->getRealqAssignedBoardName($student->school);
        $countryName = $this->getStudentCountryName($student);

        return view('student.assessment.subjective_setup', compact('grades', 'subjects', 'assignedGrade', 'boardName', 'countryName', 'resumeRedirect'));
    }

    public function subjectiveTopics(Request $request)
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$this->isSchoolRealQAssessmentAvailable($student)) {
            return response()->json(['success' => false, 'message' => $this->getRealQAvailabilityFailureReason($student)], 403);
        }
        if (!$this->isStudentRealQGradeAllowed($student)) {
            return response()->json(['success' => false, 'message' => 'RealQ Assessment is not assigned for your grade.'], 403);
        }
        $validated = $request->validate([
            'grade_id' => 'required|integer',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'integer',
        ]);

        if (!$this->isStudentGradeAllowed($student->id, (int) $validated['grade_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected grade is not allowed for your profile.',
            ], 422);
        }

        $boardId = $this->getRealqAssignedBoardId($student->school);
        $countryId = $this->getStudentCountryId($student);

        if (!$boardId || !$countryId) {
            return response()->json([
                'success' => false,
                'message' => 'Board or country not configured for this student.',
            ], 422);
        }

        $subjects = RealQAssessmentSubject::whereIn('id', $validated['subject_ids'])->get(['id', 'name']);
        $topics = RealQAssessmentTopic::where('grade_id', $validated['grade_id'])
            ->where('board_id', $boardId)
            ->where('country_id', $countryId)
            ->whereIn('subject_id', $validated['subject_ids'])
            ->where('moderation_status', 'approved')
            ->orderBy('topic')
            ->get(['id', 'subject_id', 'topic']);

        $grouped = [];
        foreach ($subjects as $subject) {
            $grouped[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'topics' => $topics->where('subject_id', $subject->id)->values()->all(),
            ];
        }

        return response()->json([
            'success' => true,
            'board' => $this->getRealqAssignedBoardName($student->school),
            'country' => $this->getStudentCountryName($student),
            'topics_by_subject' => $grouped,
        ]);
    }

    public function startSubjectiveAssessment(Request $request)
    {
        $studentId = (int) Session::get('student_id');
        $student = Students::with('school')->find($studentId);
        if (!$student || !$this->isSchoolRealQAssessmentAvailable($student)) {
            return response()->json(['success' => false, 'message' => $this->getRealQAvailabilityFailureReason($student)], 403);
        }
        if (!$this->isStudentRealQGradeAllowed($student)) {
            return response()->json(['success' => false, 'message' => 'RealQ Assessment is not assigned for your grade.'], 403);
        }
        $resume   = (int) $request->get('resume', 0) === 1;
        $existing = $this->getExistingRealQAnswers($studentId, 'subjective');

        if ($existing['total'] > 0) {
            if ($existing['completed']) {
                return response()->json([
                    'success'    => false,
                    'completed'  => true,
                    'report_url' => route('student.assessment.report'),
                    'message'    => 'You have already completed this assessment.',
                ], 409);
            }

            if ($resume) {
                return response()->json([
                    'success' => true,
                    'questions' => $existing['questions'],
                    'answers' => $existing['answers'],
                    'current_index' => $existing['current_index'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'You already started this assessment. Please resume from the assessment page.',
            ], 409);
        }

        $validated = $request->validate([
            'grade_id' => 'required|integer',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'integer',
            'topic_ids' => 'required|array|min:1',
            'topic_ids.*' => 'integer',
        ]);

        if (!$this->isStudentGradeAllowed($studentId, (int) $validated['grade_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected grade is not allowed for your profile.',
            ], 422);
        }

        if (!$this->validateTopicSelectionLimit($validated['topic_ids'], 2)) {
            return response()->json([
                'success' => false,
                'message' => 'You can select up to 2 topics per subject.',
            ], 422);
        }

        $boardId = $this->getRealqAssignedBoardId($student->school);
        $countryId = $this->getStudentCountryId($student);

        if (!$boardId || !$countryId) {
            return response()->json([
                'success' => false,
                'message' => 'Board or country not configured for this student.',
            ], 422);
        }

        $maxQuestions = 6;

        $strictQuery = RealQAssessmentQuestion::where('question_type', 'subjective')
            ->where('moderation_status', 'approved')
            ->where('grade_id', $validated['grade_id'])
            ->where('board_id', $boardId)
            ->where('country_id', $countryId)
            ->whereIn('subject_id', $validated['subject_ids'])
            ->whereIn('topic_id', $validated['topic_ids']);

       $questions = $this->pickDiversifiedQuestions($strictQuery, $maxQuestions, $validated['topic_ids']);

        // Allow starting with any available count up to 5.
        if ($questions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No approved subjective questions found for selected filters.',
            ], 422);
        }

        foreach ($questions as $question) {
            AssessmentAnswer::firstOrCreate(
                [
                    'student_id' => $studentId,
                    'assessment_type' => 'baseline',
                    'assessment_question_id' => $question->id,
                ],
                [
                    'answer_text' => null,
                    'selected_option' => null,
                    'is_submitted' => false,
                    'started_at' => now(),
                ]
            );
        }

        $questionPayload = $questions->values()->map(function ($question, $index) {
            return [
                'id' => $question->id,
                'order' => $index + 1,
                'title' => 'Question ' . ($index + 1),
                'scenario' => (string) ($question->challenge ?? ''),
                'question_text' => (string) ($question->question_text ?? ''),
            ];
        })->all();

        return response()->json([
            'success' => true,
            'questions' => $questionPayload,
            'answers' => [],
            'current_index' => 0,
        ]);
    }

    public function saveSubjectiveAnswer(Request $request)
    {
        $validated = $request->validate([
            'assessment_question_id' => 'required|integer',
            'answer_text' => 'nullable|string',
            'is_submitted' => 'nullable|boolean',
        ]);

        $answer = AssessmentAnswer::where('student_id', (int) Session::get('student_id'))
            ->where('assessment_type', 'baseline')
            ->where('assessment_question_id', $validated['assessment_question_id'])
            ->first();

        if (!$answer) {
            return response()->json([
                'success' => false,
                'message' => 'Answer record not found.',
            ], 404);
        }

        $answerText = isset($validated['answer_text']) ? trim((string) $validated['answer_text']) : '';
        $answer->answer_text = $answerText !== '' ? $answerText : null;
        $answer->answered_at = now();
        // Only mark submitted when an answer exists.
        $answer->is_submitted = $answerText !== '';
        $answer->save();

        return response()->json(['success' => true]);
    }

    public function mcqSetup()
    {
        $student = Students::with('school')->find(Session::get('student_id'));
        if (!$student || !$this->isSchoolRealQAssessmentAvailable($student)) {
            return redirect()->route('student.assessment')->with('message1', $this->getRealQAvailabilityFailureReason($student));
        }
        if (!$this->isStudentRealQGradeAllowed($student)) {
            return redirect()->route('student.assessment')->with('message1', 'RealQ Assessment is not assigned for your grade.');
        }
        $progress = $this->getRealQProgress($student->id, 'mcq');
        $resumeRedirect = $progress['started'] && !$progress['completed'];
        $assignedGrade = null;
        if ($student && $student->student_grade_id) {
            $assignedGrade = StudentGrade::find($student->student_grade_id);
        }
        $grades = $assignedGrade ? collect([$assignedGrade]) : collect();
        $subjects = RealQAssessmentSubject::orderBy('name')->get(['id', 'name']);

        $boardName = $this->getRealqAssignedBoardName($student->school);
        $countryName = $this->getStudentCountryName($student);

        return view('student.assessment.mcq_setup', compact('grades', 'subjects', 'assignedGrade', 'boardName', 'countryName', 'resumeRedirect'));
    }

    public function startMcqAssessment(Request $request)
    {
        $studentId = (int) Session::get('student_id');
        $student = Students::with('school')->find($studentId);
        if (!$student || !$this->isSchoolRealQAssessmentAvailable($student)) {
            return response()->json(['success' => false, 'message' => $this->getRealQAvailabilityFailureReason($student)], 403);
        }
        if (!$this->isStudentRealQGradeAllowed($student)) {
            return response()->json(['success' => false, 'message' => 'RealQ Assessment is not assigned for your grade.'], 403);
        }
        $resume = (int) $request->get('resume', 0) === 1;

        if ($resume) {
            $existing = $this->getExistingRealQAnswers($studentId, 'mcq');
            if ($existing['total'] > 0) {
                if ($existing['completed']) {
                    return response()->json([
                        'success' => false,
                        'completed' => true,
                        'report_url' => route('student.assessment.report', ['mode' => 'mcq']),
                        'message' => 'You have already completed this assessment.',
                    ], 409);
                }

                return response()->json([
                    'success' => true,
                    'questions' => $existing['questions'],
                    'answers' => $existing['answers'],
                    'current_index' => $existing['current_index'],
                ]);
            }
        }

        $existing = $this->getExistingRealQAnswers($studentId, 'mcq');
        if ($existing['total'] > 0) {
            if ($existing['completed']) {
                return response()->json([
                    'success' => false,
                    'completed' => true,
                    'report_url' => route('student.assessment.report', ['mode' => 'mcq']),
                    'message' => 'You have already completed this assessment.',
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'You already started this assessment. Please resume from the assessment page.',
            ], 409);
        }

        $validated = $request->validate([
            'grade_id' => 'required|integer',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'integer',
            'topic_ids' => 'required|array|min:1',
            'topic_ids.*' => 'integer',
        ]);

        if (!$this->isStudentGradeAllowed($studentId, (int) $validated['grade_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected grade is not allowed for your profile.',
            ], 422);
        }

        if (!$this->validateTopicSelectionLimit($validated['topic_ids'], 2)) {
            return response()->json([
                'success' => false,
                'message' => 'You can select up to 2 topics per subject.',
            ], 422);
        }

        $boardId = $this->getRealqAssignedBoardId($student->school);
        $countryId = $this->getStudentCountryId($student);

        if (!$boardId || !$countryId) {
            return response()->json([
                'success' => false,
                'message' => 'Board or country not configured for this student.',
            ], 422);
        }

        $maxQuestions = 10;

        $strictQuery = RealQAssessmentQuestion::where('question_type', 'mcq')
            ->where('moderation_status', 'approved')
            ->where('grade_id', $validated['grade_id'])
            ->where('board_id', $boardId)
            ->where('country_id', $countryId)
            ->whereIn('subject_id', $validated['subject_ids'])
            ->whereIn('topic_id', $validated['topic_ids']);

       $questions = $this->pickDiversifiedQuestions($strictQuery, $maxQuestions, $validated['topic_ids']);

        if ($questions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No approved MCQ questions found for selected filters.',
            ], 422);
        }

        foreach ($questions as $question) {
            AssessmentAnswer::firstOrCreate(
                [
                    'student_id' => $studentId,
                    'assessment_type' => 'baseline',
                    'assessment_question_id' => $question->id,
                ],
                [
                    'answer_text' => null,
                    'selected_option' => null,
                    'is_submitted' => false,
                    'started_at' => now(),
                ]
            );
        }

        $questionPayload = $questions->values()->map(function ($question, $index) {
            return [
                'id' => $question->id,
                'order' => $index + 1,
                'title' => 'Question ' . ($index + 1),
                'scenario' => (string) ($question->challenge ?? ''),
                'question_text' => (string) ($question->question_text ?? ''),
                'option_a' => (string) ($question->option_a ?? ''),
                'option_b' => (string) ($question->option_b ?? ''),
                'option_c' => (string) ($question->option_c ?? ''),
                'option_d' => (string) ($question->option_d ?? ''),
            ];
        })->all();

        return response()->json([
            'success' => true,
            'questions' => $questionPayload,
            'answers' => [],
            'current_index' => 0,
        ]);
    }

    public function saveMcqAnswer(Request $request)
    {
        $validated = $request->validate([
            'assessment_question_id' => 'required|integer',
            'selected_option' => 'nullable|in:A,B,C,D',
            'is_submitted' => 'nullable|boolean',
        ]);

        $answer = AssessmentAnswer::where('student_id', (int) Session::get('student_id'))
            ->where('assessment_type', 'baseline')
            ->where('assessment_question_id', $validated['assessment_question_id'])
            ->first();

        if (!$answer) {
            return response()->json([
                'success' => false,
                'message' => 'Answer record not found.',
            ], 404);
        }

        $selectedOption = $validated['selected_option'] ?? null;
        $answer->selected_option = $selectedOption;
        $answer->answered_at = now();
        // Only mark submitted when an answer exists.
        $answer->is_submitted = !empty($selectedOption);
        $answer->save();

        return response()->json(['success' => true]);
    }

    public function report(Request $request)
    {
        $studentId = (int) Session::get('student_id');
        $student = Students::select('id', 'name', 'student_grade_id')->find($studentId);

        $mode = strtolower((string) $request->get('mode', (string) session('baseline_assessment_mode', 'subjective')));
        if (!in_array($mode, ['subjective', 'mcq'], true)) {
            $mode = 'subjective';
        }

        $answers = AssessmentAnswer::query()
            ->join('realq_assessment_questions', 'realq_assessment_student_answers.assessment_question_id', '=', 'realq_assessment_questions.id')
            ->where('realq_assessment_student_answers.student_id', $studentId)
            ->where('realq_assessment_student_answers.assessment_type', 'baseline')
            ->where('realq_assessment_questions.question_type', $mode)
            ->select('realq_assessment_student_answers.*')
            ->orderBy('id')
            ->get();

        if ($answers->isEmpty()) {
            return redirect()
                ->route('student.assessment')
                ->with('error', 'No '.$mode.' assessment responses found to generate report.');
        }

        $total = $answers->count();
        $answered = $answers->filter(function ($row) {
            return !empty($row->answer_text) || !empty($row->selected_option);
        })->count();
        $allSubmitted = $answers->every(fn ($row) => (int) $row->is_submitted === 1);

        $latestReport = AssessmentStudentReport::where('student_id', $studentId)
            ->where('assessment_type', 'realq')
            ->latest('id')
            ->first();

        // If the student has just completed all answers and no report record exists yet,
        // create a pending record and redirect to the assessment home with a notification message.
        if (!$latestReport && $allSubmitted) {
            $reportRow = AssessmentStudentReport::create([
                'student_id'       => $studentId,
                'student_grade_id' => $student->student_grade_id,
                'assessment_type'  => 'realq',
                'status'           => 'pending',
            ]);

            /* START - RealQ Submission Reward Points */
            $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist($studentId, 'pre_assessment_score', $reportRow->id);
            if ($alreadyRewarded->isEmpty()) {
                StudentRewardPointsHelper::storeRewardPoints([
                    'student_id'    => $studentId,
                    'reward_type'   => 'pre_assessment_score',
                    'item_id'       => $reportRow->id,
                    'item_type'     => 'realq',
                    'reward_points' => 5,
                ]);
            }
            /* END - RealQ Submission Reward Points */

            return redirect()
                ->route('student.assessment')
                ->with('message', 'Your assessment has been submitted successfully. You will be notified once your report is approved.');
        }

        // If a report exists but is not yet approved, redirect to assessment home.
        if ($latestReport && in_array((string) ($latestReport->status ?? 'pending'), ['pending', 'generated'], true)) {
            return redirect()
                ->route('student.assessment')
                ->with('message', 'Your report is currently under review. You will be notified once it is approved.');
        }

        $firstName = trim($student->name);
        if (Str::contains($firstName, ' ')) {
            $firstName = explode(' ', $firstName)[0];
        }

        $completion = $total > 0 ? (int) round(($answered / $total) * 100) : 0;

        $reportText = '';
        if ($latestReport && (string) ($latestReport->status ?? '') === 'approved') {
            $reportText = $this->extractNarrativeFromReport((string) ($latestReport->report_text ?? ''));
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'text' => $reportText,
            ]);
        }

        return view('student.assessment.report', compact(
            'mode',
            'firstName',
            'total',
            'answered',
            'completion',
            'reportText'
        ));
    }


    public function downloadReport(Request $request, OpenAIService $openAIService)
    {
        $studentId = (int) Session::get('student_id');
        $student = Students::find($studentId);

        $mode = strtolower((string) $request->get('mode', (string) session('baseline_assessment_mode', 'subjective')));
        if (!in_array($mode, ['subjective', 'mcq'], true)) {
            $mode = 'subjective';
        }

        $latestReport = AssessmentStudentReport::where('student_id', $studentId)
            ->where('assessment_type', 'realq')
            ->latest('id')
            ->first();

        
        if (!$latestReport) {
            return redirect()
                ->route('student.assessment')
                ->with('message1', 'No report found. Please complete the assessment first.');
        }

        $reportStatus = (string) ($latestReport->status ?? 'pending');

        if (in_array($reportStatus, ['pending', 'generated'], true)) {
            return redirect()
                ->route('student.assessment')
                ->with('message', 'Your report is currently under review. You will be notified once it is available.');
        }
        // --- END APPROVAL GATE ---

        $generateOnly = (int) $request->get('generate', 0) === 1;
        $existingReportPath = $latestReport ? (string) ($latestReport->report_path ?? '') : '';
        $existingReportFile = $existingReportPath !== '' ? public_path('tenants/' . $existingReportPath) : '';
        $hasExistingFile = $existingReportFile !== '' && File::exists($existingReportFile);
        $hasExistingText = $latestReport && !empty($latestReport->report_text);

        if ($latestReport && $hasExistingFile && $hasExistingText) {
            if ($generateOnly) {
                return response()->json([
                    'success' => true,
                    'text' => $this->extractNarrativeFromReport((string) ($latestReport->report_text ?? '')),
                ]);
            }
            return response()->download($existingReportFile, basename($existingReportPath));
        }

        /*
         * START - NOT IN USE: Old auto-generate flow.
         * Admin now generates and approves reports via the backend manual review workflow.
         * The approval gate above (pending/generated states) prevents this block from ever
         * being reached. Kept for reference only.
         *
        $answers = AssessmentAnswer::query()
            ->join('realq_assessment_questions', 'realq_assessment_student_answers.assessment_question_id', '=', 'realq_assessment_questions.id')
            ->leftJoin('realq_assessment_subjects', 'realq_assessment_questions.subject_id', '=', 'realq_assessment_subjects.id')
            ->leftJoin('realq_assessment_topics', 'realq_assessment_questions.topic_id', '=', 'realq_assessment_topics.id')
            ->where('realq_assessment_student_answers.student_id', $studentId)
            ->where('realq_assessment_student_answers.assessment_type', 'baseline')
            ->where('realq_assessment_questions.question_type', $mode)
            ->select([
                'realq_assessment_student_answers.*',
                'realq_assessment_questions.grade_id as q_grade_id',
                'realq_assessment_questions.board_id as q_board_id',
                'realq_assessment_questions.country_id as q_country_id',
                'realq_assessment_questions.question_text as q_question_text',
                'realq_assessment_questions.challenge as q_scenario',
                'realq_assessment_questions.option_a as q_option_a',
                'realq_assessment_questions.option_b as q_option_b',
                'realq_assessment_questions.option_c as q_option_c',
                'realq_assessment_questions.option_d as q_option_d',
                'realq_assessment_subjects.name as q_subject_name',
                'realq_assessment_topics.topic as q_topic_name',
            ])
            ->orderBy('realq_assessment_student_answers.id')
            ->get();

        if ($answers->isEmpty()) {
            return redirect()
                ->route('student.assessment')
                ->with('error', 'No '.$mode.' assessment responses found to download report.');
        }

        $total = $answers->count();
        $answered = $answers->filter(function ($row) {
            return !empty($row->answer_text) || !empty($row->selected_option);
        })->count();
        $completion = $total > 0 ? (int) round(($answered / $total) * 100) : 0;

        $first = $answers->first();
        $gradeName = optional(StudentGrade::find($first->q_grade_id))->name ?? '';
        $boardName = optional(StudentBoard::find($first->q_board_id))->name ?? '';
        $countryName = optional(Country::find($first->q_country_id))->name ?? '';

        $assignmentRow = null;

        if ($mode === 'mcq') {
            $responses = $answers->map(function ($row) {
                return [
                    'assessment_question_id' => $row->assessment_question_id,
                    'scenario' => (string) ($row->q_scenario ?? ''),
                    'question' => (string) ($row->q_question_text ?? ''),
                    'options' => [
                        'A' => (string) ($row->q_option_a ?? ''),
                        'B' => (string) ($row->q_option_b ?? ''),
                        'C' => (string) ($row->q_option_c ?? ''),
                        'D' => (string) ($row->q_option_d ?? ''),
                    ],
                    'selected_option' => (string) ($row->selected_option ?? ''),
                ];
            })->values()->all();
        
            $prompt = $openAIService->buildMcqAssessmentReportPrompt(
                (string) $gradeName,
                (string) $boardName,
                (string) $countryName,
                $responses
            );
        } else {
            $responses = $answers->map(function ($row) {
                return [
                    'subject' => (string) ($row->q_subject_name ?? ''),
                    'topic' => (string) ($row->q_topic_name ?? ''),
                    'scenario' => (string) ($row->q_scenario ?? ''),
                    'question' => (string) ($row->q_question_text ?? ''),
                    'answer' => (string) ($row->answer_text ?? ''),
                ];
            })->values()->all();

            $assignmentRow = RealQAssessmentSchoolAssignment::where('school_id', optional($student->school)->id)
                ->where('realq_assessment_assigned', 1)
                ->where('realq_assessment_assigned_grade_id', $student->student_grade_id)
                ->first();

            $parameterLines = [];
            if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_parameters_id)) {
                $paramIds = array_values(array_filter(array_map('intval', explode(',', (string) $assignmentRow->realq_assessment_assigned_parameters_id))));
                if (!empty($paramIds)) {
                    $paramIdList = implode(',', $paramIds);
                    $parameters = RealQAssessmentParameter::whereIn('id', $paramIds)
                        ->orderByRaw("FIELD(id, {$paramIdList})")
                        ->get();
                    foreach ($parameters as $row) {
                        $name = trim((string) ($row->name ?? ''));
                        if ($name === '') {
                            continue;
                        }
                        $desc = trim((string) ($row->description ?? ''));
                        $parameterLines[] = $desc !== '' ? ($name . ' - ' . $desc) : $name;
                    }
                }
            }
            if (empty($parameterLines)) {
                $parameterLines = [
                    'Problem Awareness - Noticing issues around them',
                    'Creative Thinking - Imagining simple solutions',
                    'Empathy - Thinking about how others feel',
                    'Value Creation - What the student will do to help solve the problem and create value',
                    'Subject Matter Expertise - Use of Math, Science, and/or English knowledge relevant to the selected topics',
                ];
            }
            $parameterLines = array_map(function ($line, $idx) {
                return ($idx + 1) . '. ' . $line;
            }, $parameterLines, array_keys($parameterLines));
            $parameterText = implode("\n", $parameterLines);

            $rubricLines = [];
            if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_scale_id)) {
                $rubrics = RealQAssessmentRubric::where('scale_id', $assignmentRow->realq_assessment_assigned_scale_id)
                    ->orderBy('score')
                    ->get();
                foreach ($rubrics as $row) {
                    $name = trim((string) ($row->name ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $desc = trim((string) ($row->description ?? ''));
                    $rubricLines[] = $desc !== '' ? ($name . ' - ' . $desc) : $name;
                }
            }
            if (empty($rubricLines)) {
                $rubricLines = [
                    'Beginning - Minimal or unclear evidence',
                    'Emerging - Some relevant signs, but limited, partial, or inconsistent',
                    'Good Evidence - Clear, relevant, and reasonably well-applied',
                    'Strong Evidence - Clear, thoughtful, well-reasoned, and well-applied',
                ];
            }
            $rubricLines = array_map(function ($line, $idx) {
                return ($idx + 1) . '. ' . $line;
            }, $rubricLines, array_keys($rubricLines));
            $rubricText = implode("\n", $rubricLines);

            $prompt = $openAIService->buildSubjectiveAssessmentReportPrompt(
                (string) $gradeName,
                (string) $boardName,
                (string) $countryName,
                $parameterText,
                $rubricText,
                $responses
            );
        }
        
        $generatedReport = $openAIService->generateAssessmentReport($prompt, 2200);
        if (!$generatedReport) {
            $generatedReport = 'Report generation is temporarily unavailable. Please try again.';
        }
        $questionTexts = [];
        foreach ($responses as $idx => $row) {
            $label = 'Question ' . ($idx + 1);
            $questionTexts[$label] = (string) ($row['question'] ?? '');
        }
        $reportSections = $this->parseSubjectiveReportSections((string) $generatedReport, $questionTexts, $assignmentRow);
        $narrativeText = $reportSections['summary_text'] ?? '';
        if ($narrativeText === '') {
            $narrativeText = $this->extractNarrativeFromReport((string) $generatedReport);
        }
        if ($narrativeText === '') {
            $narrativeText = trim((string) $generatedReport);
        }
        $schoolLogoPath = public_path('asset/images/kids-tm-logo.png');

        if ($latestReport && $hasExistingFile) {
            $latestReport->report_text = $narrativeText;
            $latestReport->save();

            if ($generateOnly) {
                return response()->json([
                    'success' => true,
                    'text' => $narrativeText,
                ]);
            }
            return response()->download($existingReportFile, basename($existingReportPath));
        }

        $fullName = $student->name;
        
        $pdf = \PDF::loadView('student.assessment.report_pdf', [
            'studentName' => $fullName,
            'schoolLogoPath' => $schoolLogoPath,
            'reportOverview' => "This report evaluates a student's performance based on their responses to various scenarios. The student demonstrated strong ability to identify real-world problems and offered practical solutions, often with empathy and a focus on fairness.",
            'mode' => $mode,
            'generatedAt' => now()->toDateTimeString(),
            'total' => $total,
            'answered' => $answered,
            'completion' => $completion,
            'reportBody' => $generatedReport,
            'reportSections' => $reportSections,
        ]);

        $tenantId = optional(optional($student)->school)->tenant_id ?: 'common';
        $fileName = Str::random(12) . dechex(time()) . '.pdf';
        $reportPath = trim($tenantId, '/') . '/student/reports/realq_assessment/' . $fileName;
        $absoluteDirectory = public_path('tenants/' . trim($tenantId, '/') . '/student/reports/realq_assessment');
        if (!File::exists($absoluteDirectory)) {
            File::makeDirectory($absoluteDirectory, 0755, true);
        }
        File::put(public_path('tenants/' . $reportPath), $pdf->output());

        $reportRow = AssessmentStudentReport::create([
            'student_id' => $studentId,
            'student_grade_id' => optional($student)->student_grade_id,
            'assessment_type' => 'realq',
            'report_path' => $reportPath,
            'report_text' => $narrativeText,
        ]);

        // START - Post Assessment Reward Points (old auto-generate flow — not in use, moved to report() submission point)
        // StudentRewardPointsHelper::storeRewardPoints([
        //     'student_id' =>  $studentId,
        //     'reward_type' => 'pre_assessment_score',
        //     'item_id' => $reportRow->id,
        //     'item_type' => 'realq',
        //     'reward_points' => 5,
        // ]);
        // END - Post Assessment Reward Points

        if ($mode === 'subjective') {
            $this->storeRealQParameterScores(
                $student,
                $reportRow,
                $reportSections['performance_rows'] ?? [],
                $assignmentRow ?? null
            );
        }

        if ($generateOnly) {
            return response()->json([
                'success' => true,
                'text' => $narrativeText,
            ]);
        }

        return response()->download(
            public_path('tenants/' . $reportRow->report_path),
            basename($reportRow->report_path)
        );
         * END - NOT IN USE: Old auto-generate flow.
         */

        return redirect()
            ->route('student.assessment')
            ->with('message1', 'Report file could not be found. Please contact support.');
    }

    /*
     * START - NOT IN USE: The following private methods are duplicates of methods in
     * app/Traits/RealQReportParser.php. They were part of the old auto-generate flow
     * (commented out above). The trait versions are the active implementations used by
     * RealQAssessmentStudentReportController via `use RealQReportParser`.
     *
    private function parseSubjectiveReportSections(string $text, array $questionTexts = [], ?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        $sections = [
            'section_a' => '',
            'section_b' => '',
            'section_c' => '',
            'performance_rows' => [],
            'scenario_rows' => [],
            'summary_text' => '',
            'graph_rubrics' => [],
        ];

        $clean = trim($text);
        if ($clean === '') {
            return $sections;
        }

        $json = json_decode($clean, true);
        if (!is_array($json)) {
            $jsonCandidate = $this->extractJsonBlock($clean);
            if ($jsonCandidate !== '') {
                $json = json_decode($jsonCandidate, true);
            }
        }
        if (is_array($json) && isset($json['Section A'], $json['Section B'], $json['Section C'])) {
            $sections['section_a'] = is_array($json['Section A']) ? json_encode($json['Section A'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $sections['section_b'] = is_array($json['Section B']) ? json_encode($json['Section B'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
            $sections['section_c'] = is_string($json['Section C']) ? trim($json['Section C']) : '';
            $sections['scenario_rows'] = $this->parseSectionAJson($json['Section A'], $questionTexts);
            $sections['performance_rows'] = $this->buildPerformanceRowsFromSectionAJson($json['Section A'], $json['Section B'], $assignmentRow);
            $sections['graph_rubrics'] = $this->getRubricDefinitionsForAssignment($assignmentRow)['ordered'] ?? [];
            if (empty($sections['performance_rows'])) {
                $sections['performance_rows'] = $this->parseSectionBJson($json['Section B']);
            }
            $sections['summary_text'] = is_string($json['Section C']) ? trim($json['Section C']) : '';
            return $sections;
        }

        $positions = [];
        $labels = [
            'a' => 'Section A: Per-Question Evidence',
            'b' => 'Section B: Aggregated Parameter Profile',
            'c' => 'Section C: Narrative Report',
        ];
        foreach ($labels as $key => $label) {
            $pos = stripos($clean, $label);
            if ($pos !== false) {
                $positions[$key] = $pos;
            }
        }

        if (isset($positions['a'])) {
            $end = $positions['b'] ?? $positions['c'] ?? strlen($clean);
            $sections['section_a'] = trim(substr($clean, $positions['a'], $end - $positions['a']));
        }
        if (isset($positions['b'])) {
            $end = $positions['c'] ?? strlen($clean);
            $sections['section_b'] = trim(substr($clean, $positions['b'], $end - $positions['b']));
        }
        if (isset($positions['c'])) {
            $sections['section_c'] = trim(substr($clean, $positions['c']));
        }

        $sections['scenario_rows'] = $this->parseSectionAQuestions($sections['section_a']);
        $sections['performance_rows'] = $this->buildPerformanceRowsFromScenarioRows(
            $sections['scenario_rows'],
            $this->parseSectionBParameters($sections['section_b']),
            $assignmentRow
        );
        $sections['graph_rubrics'] = $this->getRubricDefinitionsForAssignment($assignmentRow)['ordered'] ?? [];
        if (empty($sections['performance_rows'])) {
            $sections['performance_rows'] = $this->parseSectionBParameters($sections['section_b']);
        }
        $sections['summary_text'] = $this->parseSectionCSummary($sections['section_c']);

        return $sections;
    }

    private function extractJsonBlock(string $text): string
    {
        $clean = trim($text);
        if ($clean === '') {
            return '';
        }
        $clean = preg_replace('/^```(?:json)?/i', '', $clean);
        $clean = preg_replace('/```$/', '', $clean);
        $first = strpos($clean, '{');
        $last = strrpos($clean, '}');
        if ($first === false || $last === false || $last <= $first) {
            return '';
        }
        return substr($clean, $first, $last - $first + 1);
    }

    private function parseSectionAJson($section, array $questionTexts = []): array
    {
        $rows = [];
        if (!is_array($section)) {
            return $rows;
        }
        foreach ($section as $questionLabel => $params) {
            if (!is_array($params)) {
                continue;
            }
            $evidence = [];
            foreach ($params as $paramName => $value) {
                $label = trim((string) $paramName);
                if (strpos($label, ' - ') !== false) {
                    $label = trim(explode(' - ', $label, 2)[0]);
                }
                $evidence[] = $label . ': ' . trim((string) $value);
            }
            $rows[] = [
                'label' => (string) $questionLabel,
                'question' => $questionTexts[$questionLabel] ?? '',
                'evidence' => $evidence,
            ];
        }
        return $rows;
    }

    private function parseSectionBJson($section): array
    {
        $rows = [];
        if (!is_array($section)) {
            return $rows;
        }
        foreach ($section as $paramName => $data) {
            if (!is_array($data)) {
                continue;
            }
            $label = trim((string) $paramName);
            if (strpos($label, ' - ') !== false) {
                $label = trim(explode(' - ', $label, 2)[0]);
            }
            $rows[] = [
                'parameter' => $label,
                'level' => (string) ($data['Overall Level'] ?? ''),
                'summary' => (string) ($data['Summary'] ?? ''),
                'rubric_id' => null,
                'rubric_score' => null,
            ];
        }
        return $rows;
    }

    private function buildPerformanceRowsFromSectionAJson($sectionA, $sectionB, ?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        if (!is_array($sectionA)) {
            return [];
        }

        $rubricDefs = $this->getRubricDefinitionsForAssignment($assignmentRow);
        if (empty($rubricDefs['ordered'])) {
            return [];
        }

        $sectionBSummaryMap = $this->getSectionBSummaryMap($sectionB);
        $parameterBuckets = [];

        foreach ($sectionA as $params) {
            if (!is_array($params)) {
                continue;
            }

            foreach ($params as $paramName => $value) {
                $label = $this->stripParameterDescription((string) $paramName);
                $normalizedLabel = $this->normalizeLabel($label);
                if ($normalizedLabel === '') {
                    continue;
                }

                $rubric = $this->resolveRubricFromEvidenceValue((string) $value, $rubricDefs);
                if (!$rubric) {
                    continue;
                }

                if (!isset($parameterBuckets[$normalizedLabel])) {
                    $parameterBuckets[$normalizedLabel] = [
                        'parameter' => $label,
                        'scores' => [],
                    ];
                }

                $parameterBuckets[$normalizedLabel]['scores'][] = (int) $rubric['score'];
            }
        }

        return $this->finalizePerformanceRows($parameterBuckets, $sectionBSummaryMap, $rubricDefs);
    }

    private function buildPerformanceRowsFromScenarioRows(array $scenarioRows, array $sectionBRows = [], ?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        if (empty($scenarioRows)) {
            return [];
        }

        $rubricDefs = $this->getRubricDefinitionsForAssignment($assignmentRow);
        if (empty($rubricDefs['ordered'])) {
            return [];
        }

        $sectionBSummaryMap = [];
        foreach ($sectionBRows as $row) {
            $label = $this->normalizeLabel((string) ($row['parameter'] ?? ''));
            if ($label === '') {
                continue;
            }
            $sectionBSummaryMap[$label] = (string) ($row['summary'] ?? '');
        }

        $parameterBuckets = [];
        foreach ($scenarioRows as $row) {
            foreach (($row['evidence'] ?? []) as $line) {
                $line = trim((string) $line);
                if ($line === '') {
                    continue;
                }

                $leftParts = explode(' - ', $line, 2);
                $left = trim((string) ($leftParts[0] ?? ''));
                $labelParts = explode(':', $left, 2);
                $label = $this->stripParameterDescription((string) ($labelParts[0] ?? ''));
                $normalizedLabel = $this->normalizeLabel($label);
                if ($normalizedLabel === '') {
                    continue;
                }

                $rubric = $this->resolveRubricFromEvidenceValue($line, $rubricDefs);
                if (!$rubric) {
                    continue;
                }

                if (!isset($parameterBuckets[$normalizedLabel])) {
                    $parameterBuckets[$normalizedLabel] = [
                        'parameter' => $label,
                        'scores' => [],
                    ];
                }

                $parameterBuckets[$normalizedLabel]['scores'][] = (int) $rubric['score'];
            }
        }

        return $this->finalizePerformanceRows($parameterBuckets, $sectionBSummaryMap, $rubricDefs);
    }

    private function finalizePerformanceRows(array $parameterBuckets, array $sectionBSummaryMap, array $rubricDefs): array
    {
        $rows = [];
        foreach ($parameterBuckets as $normalizedLabel => $bucket) {
            $scores = array_values(array_filter($bucket['scores'] ?? [], function ($score) {
                return is_numeric($score);
            }));
            if (empty($scores)) {
                continue;
            }

            $averageScore = (int) round(array_sum($scores) / count($scores));
            $rubric = $this->findRubricByAverageScore($averageScore, $rubricDefs['ordered']);
            if (!$rubric) {
                continue;
            }

            $rows[] = [
                'parameter' => (string) ($bucket['parameter'] ?? ''),
                'level' => (string) ($rubric['name'] ?? ''),
                'summary' => (string) ($sectionBSummaryMap[$normalizedLabel] ?? ''),
                'rubric_id' => (int) ($rubric['id'] ?? 0) ?: null,
                'rubric_score' => (int) ($rubric['score'] ?? 0) ?: null,
            ];
        }

        return $rows;
    }

    private function getRubricDefinitionsForAssignment(?RealQAssessmentSchoolAssignment $assignmentRow = null): array
    {
        $query = RealQAssessmentRubric::query();
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_scale_id)) {
            $query->where('scale_id', $assignmentRow->realq_assessment_assigned_scale_id);
        }

        $rubrics = $query->orderBy('score')->get(['id', 'name', 'score']);
        $ordered = [];
        $byName = [];

        foreach ($rubrics as $rubric) {
            $name = trim((string) ($rubric->name ?? ''));
            $score = (int) ($rubric->score ?? 0);
            if ($name === '' || $score <= 0) {
                continue;
            }

            $item = [
                'id' => (int) $rubric->id,
                'name' => $name,
                'score' => $score,
                'normalized_name' => $this->normalizeLabel($name),
            ];

            $ordered[] = $item;
            $byName[$item['normalized_name']] = $item;
        }

        return [
            'ordered' => $ordered,
            'by_name' => $byName,
        ];
    }

    private function getSectionBSummaryMap($sectionB): array
    {
        $summaryMap = [];
        if (!is_array($sectionB)) {
            return $summaryMap;
        }

        foreach ($sectionB as $paramName => $data) {
            if (!is_array($data)) {
                continue;
            }

            $label = $this->normalizeLabel($this->stripParameterDescription((string) $paramName));
            if ($label === '') {
                continue;
            }

            $summaryMap[$label] = (string) ($data['Summary'] ?? '');
        }

        return $summaryMap;
    }

    private function resolveRubricFromEvidenceValue(string $value, array $rubricDefs): ?array
    {
        $value = trim($value);
        if ($value === '' || empty($rubricDefs['ordered'])) {
            return null;
        }

        $beforeDash = trim(explode(' - ', $value, 2)[0]);
        $candidates = array_filter([
            $this->normalizeLabel($beforeDash),
            $this->normalizeLabel((string) preg_replace('/^.*?:\s* /', '', $beforeDash)),
            $this->normalizeLabel($value),
        ]);

        foreach ($candidates as $candidate) {
            if (isset($rubricDefs['by_name'][$candidate])) {
                return $rubricDefs['by_name'][$candidate];
            }
        }

        foreach ($rubricDefs['ordered'] as $rubric) {
            $rubricName = (string) ($rubric['normalized_name'] ?? '');
            foreach ($candidates as $candidate) {
                if ($rubricName !== '' && (str_contains($candidate, $rubricName) || str_contains($rubricName, $candidate))) {
                    return $rubric;
                }
            }
        }

        return null;
    }

    private function findRubricByAverageScore(int $averageScore, array $orderedRubrics): ?array
    {
        if (empty($orderedRubrics)) {
            return null;
        }

        $closest = null;
        $smallestGap = null;
        foreach ($orderedRubrics as $rubric) {
            $gap = abs(((int) ($rubric['score'] ?? 0)) - $averageScore);
            if ($closest === null || $gap < $smallestGap) {
                $closest = $rubric;
                $smallestGap = $gap;
            }
        }

        return $closest;
    }

    private function stripParameterDescription(string $label): string
    {
        $label = trim($label);
        if (strpos($label, ' - ') !== false) {
            return trim(explode(' - ', $label, 2)[0]);
        }
        return $label;
    }

    private function storeRealQParameterScores($student, AssessmentStudentReport $reportRow, array $performanceRows, ?RealQAssessmentSchoolAssignment $assignmentRow = null): void
    {
        if (empty($performanceRows)) {
            return;
        }

        $paramMap = [];
        $paramQuery = RealQAssessmentParameter::query();
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_parameters_id)) {
            $paramIds = array_values(array_filter(array_map('intval', explode(',', (string) $assignmentRow->realq_assessment_assigned_parameters_id))));
            if (!empty($paramIds)) {
                $paramQuery->whereIn('id', $paramIds);
            }
        }
        foreach ($paramQuery->get() as $param) {
            $name = trim((string) ($param->name ?? ''));
            if ($name === '') {
                continue;
            }
            $paramMap[$this->normalizeLabel($name)] = (int) $param->id;
        }

        $rubricMap = [];
        $rubricQuery = RealQAssessmentRubric::query();
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_scale_id)) {
            $rubricQuery->where('scale_id', $assignmentRow->realq_assessment_assigned_scale_id);
        }
        foreach ($rubricQuery->get() as $rubric) {
            $name = trim((string) ($rubric->name ?? ''));
            if ($name === '') {
                continue;
            }
            $rubricMap[$this->normalizeLabel($name)] = (int) $rubric->id;
        }

        RealQStudentParameterScore::where('assessment_report_id', $reportRow->id)->delete();

        $rows = [];
        foreach ($performanceRows as $row) {
            $paramName = $this->normalizeLabel((string) ($row['parameter'] ?? ''));
            if ($paramName === '' || !isset($paramMap[$paramName])) {
                continue;
            }
            $rubricId = (int) ($row['rubric_id'] ?? 0);
            if ($rubricId <= 0) {
                $levelName = $this->normalizeLabel((string) ($row['level'] ?? ''));
                $rubricId = $rubricMap[$levelName] ?? $this->matchClosestRubricId($levelName, $rubricMap);
            }

            $rows[] = [
                'student_id' => (int) ($student->id ?? 0),
                'school_id' => (int) (optional($student->school)->id ?? 0) ?: null,
                'student_grade_id' => (int) ($student->student_grade_id ?? 0) ?: null,
                'assessment_report_id' => (int) $reportRow->id,
                'parameter_id' => (int) $paramMap[$paramName],
                'rubric_id' => $rubricId ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            RealQStudentParameterScore::insert($rows);
        }
    }

    private function normalizeLabel(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        return $value;
    }

    private function matchClosestRubricId(string $levelName, array $rubricMap): ?int
    {
        if ($levelName === '' || empty($rubricMap)) {
            return null;
        }
        foreach ($rubricMap as $name => $id) {
            if (str_contains($levelName, $name) || str_contains($name, $levelName)) {
                return (int) $id;
            }
        }
        return null;
    }

    private function parseSectionAQuestions(string $section): array
    {
        $rows = [];
        if ($section === '') {
            return $rows;
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $section))));
        $current = null;
        foreach ($lines as $line) {
            if (stripos($line, 'Section A:') === 0) {
                continue;
            }
            if (preg_match('/^Question\s*(\d+)\s*[:\-]?/i', $line, $match)) {
                if ($current) {
                    $rows[] = $current;
                }
                $current = [
                    'label' => 'Question ' . $match[1],
                    'evidence' => [],
                ];
                continue;
            }
            if (!$current) {
                continue;
            }
            $current['evidence'][] = $line;
        }
        if ($current) {
            $rows[] = $current;
        }
        return $rows;
    }

    private function parseSectionBParameters(string $section): array
    {
        $rows = [];
        if ($section === '') {
            return $rows;
        }
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $section))));
        $current = null;
        foreach ($lines as $line) {
            if (stripos($line, 'Section B:') === 0) {
                continue;
            }
            if (preg_match('/^\d+\.\s*(.+)$/', $line, $match)) {
                if ($current) {
                    $rows[] = $current;
                }
                $label = trim($match[1]);
                $name = trim(preg_split('/\s*-\s* /', $label, 2)[0]);
                $current = [
                    'parameter' => $name !== '' ? $name : $label,
                    'level' => '',
                    'summary' => '',
                ];
                continue;
            }
            if (!$current) {
                continue;
            }
            if (preg_match('/^[-•]?\s*Overall Level[:\s-]*(.+)$/i', $line, $match)) {
                $current['level'] = trim($match[1]);
                continue;
            }
            if (preg_match('/^[-•]?\s*Summary[:\s-]*(.+)$/i', $line, $match)) {
                $current['summary'] = trim($match[1]);
                continue;
            }
            if ($current['summary'] === '' && $current['level'] !== '') {
                $current['summary'] = $line;
            }
        }
        if ($current) {
            $rows[] = $current;
        }
        return $rows;
    }

    private function parseSectionCSummary(string $section): string
    {
        if ($section === '') {
            return '';
        }
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $section))));
        $lines = array_values(array_filter($lines, function ($line) {
            if (stripos($line, 'Section C:') === 0) {
                return false;
            }
            if (stripos($line, 'A short, clear narrative') === 0) {
                return false;
            }
            if (preg_match('/^[-•]/', $line)) {
                return false;
            }
            return true;
        }));
        return trim(implode("\n", $lines));
    }
     * END - NOT IN USE
     */
    private function extractNarrativeFromReport(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $pattern = '/Section C:.*?\R+/i';
        if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            $start = $matches[0][1] + strlen($matches[0][0]);
            $candidate = trim(substr($text, $start));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return $text;
    }


    private function pickDiversifiedQuestions($query, int $maxCount, array $topicIds = [])
    {
        if (!empty($topicIds)) {
            // Fetch slightly more than the even share per topic to give the diversifier room.
            $perTopicFetch = (int) ceil($maxCount / count($topicIds)) + 2;
            $pool = collect();
            foreach ($topicIds as $topicId) {
                $rows = (clone $query)
                    ->where('topic_id', $topicId)
                    ->inRandomOrder()
                    ->take($perTopicFetch)
                    ->get();
                $pool = $pool->merge($rows);
            }
        } else {
            $pool = $query->inRandomOrder()->take($maxCount * 4)->get();
        }

       //$pool = $query->inRandomOrder()->get();
        if ($pool->isEmpty()) {
            return collect();
        }

        $byTopic = $pool->groupBy('topic_id')->map(function ($rows) {
            return $rows->shuffle()->values();
        });

        $topicIds = $byTopic->keys()->shuffle()->values();
        $selected = collect();

        // First pass: take one from each topic (if available).
        foreach ($topicIds as $topicId) {
            if ($selected->count() >= $maxCount) {
                break;
            }
            $rows = $byTopic->get($topicId, collect());
            if ($rows->isNotEmpty()) {
                $selected->push($rows->shift());
                $byTopic[$topicId] = $rows;
            }
        }

        if ($selected->count() >= $maxCount) {
            return $selected;
        }

        // Fill remaining from the leftover pool, shuffled.
        $remaining = $byTopic->flatten(1)->shuffle();
        foreach ($remaining as $row) {
            $selected->push($row);
            if ($selected->count() >= $maxCount) {
                break;
            }
        }

        return $selected;
    }
}

