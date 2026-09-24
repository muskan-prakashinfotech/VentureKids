<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\StudentRewardPointsHelper;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentStudentReport;
use App\Models\Country;
use App\Models\RealQAssessmentParameter;
use App\Models\RealQAssessmentRubric;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\School;
use App\Models\StudentBoard;
use App\Models\StudentGrade;
use App\Models\Students;
use App\Models\StudentRewardPoints;
use App\Services\OpenAIService;
use App\Traits\RealQReportParser;
use App\Mail\RealQReportApprovedMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PDF;

class RealQAssessmentStudentReportController extends Controller
{
    use RealQReportParser;

    public function index(Request $request)
    {
        $schools = applyCountryScope(
            School::orderBy('school_name'),
            'country_id'
        )->get(['id', 'school_name']);

        $filterSchoolId  = $request->input('school_id');
        $filterStudentId = $request->input('student_id');
        $filterStatus    = $request->input('status');

        if ($filterSchoolId && !applyCountryScope(School::query()->whereKey($filterSchoolId), 'country_id')->exists()) {
            return view('backend.realq_assessment.student_reports.index', [
                'reports'         => collect(),
                'gradeMap'        => collect(),
                'schools'         => $schools,
                'filterSchoolId'  => null,
                'filterStudentId' => null,
                'filterStatus'    => null,
            ]);
        }

        return view('backend.realq_assessment.student_reports.index', compact(
            'schools',
            'filterSchoolId',
            'filterStudentId',
            'filterStatus'
        ));
    }

    public function datatable(Request $request)
    {
        $filterSchoolId  = $request->input('school_id');
        $filterStudentId = $request->input('student_id');
        $filterStatus    = $request->input('status');

        if ($filterSchoolId && !applyCountryScope(School::query()->whereKey($filterSchoolId), 'country_id')->exists()) {
            return datatables()->of([])->make(true);
        }

        // Require a school to be selected — show empty table otherwise
        if (!$filterSchoolId) {
            return datatables()->of([])->make(true);
        }

        $query = AssessmentStudentReport::query()
            ->select(['id', 'student_id', 'status', 'created_at'])
            ->where('assessment_type', 'realq')
            ->with([
                'student' => fn ($q) => $q->select(['id', 'name', 'school_id', 'student_grade_id']),
                'student.school' => fn ($q) => $q->select(['id', 'school_name']),
            ])
            ->whereHas('student', fn ($q) => $q->where('school_id', $filterSchoolId))
            ->whereHas('student.school', fn ($q) => applyCountryScope($q, 'country_id'))
            ->latest('id');

        if ($filterStudentId) {
            $query->where('student_id', $filterStudentId);
        }

        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        $reports  = $query->get();
        $gradeIds = $reports->pluck('student.student_grade_id')->filter()->unique()->values()->all();
        $gradeMap = StudentGrade::whereIn('id', $gradeIds)->pluck('name', 'id');

        $data = $reports->toArray();

        return datatables()->of($data)
            ->addIndexColumn()
            ->addColumn('student_name', function ($row) {
                return $row['student']['name'] ?? '—';
            })
            ->addColumn('school_name', function ($row) {
                return $row['student']['school']['school_name'] ?? '—';
            })
            ->addColumn('grade_name', function ($row) use ($gradeMap) {
                $gradeId = $row['student']['student_grade_id'] ?? 0;
                return $gradeMap[$gradeId] ?? '—';
            })
            ->addColumn('submitted_at', function ($row) {
                return $row['created_at'] ? Carbon::parse($row['created_at'])->format('d M Y') : '—';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row['status'] === 'approved') {
                    return '<span class="badge badge-success">Approved</span>';
                }
                if ($row['status'] === 'generated') {
                    return '<span class="badge badge-info">Generated</span>';
                }
                return '<span class="badge badge-warning">Pending</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row['status'] === 'approved') {
                    return '<span class="text-success font-weight-bold">'
                        . '<i class="fas fa-check-circle"></i> Approved &amp; Published</span>';
                }

                $html = '';
                if ($row['status'] === 'pending') {
                    $html .= '<button class="btn btn-sm btn-primary btn-generate" data-id="' . $row['id'] . '" title="Generate">'
                        . '<i class="fas fa-magic"></i> Generate</button> ';
                }
                if ($row['status'] === 'generated') {
                    $editUrl = route('backend.realqassessment.student-reports.edit', $row['id']);
                    $html .= '<a href="' . $editUrl . '" class="btn btn-sm btn-secondary" title="Edit">'
                        . '<i class="fas fa-edit"></i> Edit</a> ';
                    $html .= '<button class="btn btn-sm btn-success btn-approve" data-id="' . $row['id'] . '" title="Approve">'
                        . '<i class="fas fa-check"></i> Approve</button>';
                }
                return $html;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * AJAX — return students who have a realq report for the given school.
     */
    public function studentsBySchool(int $schoolId)
    {
        if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
            return response()->json([], 403);
        }

        $studentIds = AssessmentStudentReport::where('assessment_type', 'realq')
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->pluck('student_id')
            ->unique()
            ->values();

        $students = Students::whereIn('id', $studentIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($students);
    }

    // -------------------------------------------------------------------------
    // Admin-triggered OpenAI flow. Runs generation, stores report_data + PDF,
    // awards reward points, stores parameter scores.
    // -------------------------------------------------------------------------

    public function generate(int $reportId, OpenAIService $openAIService)
    {
        $reportRow = AssessmentStudentReport::find($reportId);
        if (!$reportRow) {
            return response()->json(['success' => false, 'message' => 'Report record not found.'], 404);
        }

        $student = Students::with(['school'])->find($reportRow->student_id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        if (!applyCountryScope(School::query()->whereKey(optional($student->school)->id), 'country_id')->exists()) {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 403);
        }

        // Only subjective mode is supported for OpenAI report generation.
        $mode = 'subjective';

        $answers = AssessmentAnswer::query()
            ->join('realq_assessment_questions', 'realq_assessment_student_answers.assessment_question_id', '=', 'realq_assessment_questions.id')
            ->leftJoin('realq_assessment_subjects', 'realq_assessment_questions.subject_id', '=', 'realq_assessment_subjects.id')
            ->leftJoin('realq_assessment_topics', 'realq_assessment_questions.topic_id', '=', 'realq_assessment_topics.id')
            ->where('realq_assessment_student_answers.student_id', $student->id)
            ->where('realq_assessment_student_answers.assessment_type', 'baseline')
            ->where('realq_assessment_questions.question_type', $mode)
            ->select([
                'realq_assessment_student_answers.*',
                'realq_assessment_questions.grade_id as q_grade_id',
                'realq_assessment_questions.board_id as q_board_id',
                'realq_assessment_questions.country_id as q_country_id',
                'realq_assessment_questions.question_text as q_question_text',
                'realq_assessment_questions.challenge as q_scenario',
                'realq_assessment_subjects.name as q_subject_name',
                'realq_assessment_topics.topic as q_topic_name',
            ])
            ->orderBy('realq_assessment_student_answers.id')
            ->get();

        if ($answers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No subjective answers found for this student.'], 422);
        }

        $first       = $answers->first();
        $gradeName   = optional(StudentGrade::find($first->q_grade_id))->name ?? '';
        $boardName   = optional(StudentBoard::find($first->q_board_id))->name ?? '';
        $countryName = optional(Country::find($first->q_country_id))->name ?? '';

        $assignmentRow = RealQAssessmentSchoolAssignment::where('school_id', optional($student->school)->id)
            ->where('realq_assessment_assigned', 1)
            ->where('realq_assessment_assigned_grade_id', $student->student_grade_id)
            ->first();

        $responses = $answers->map(function ($row) {
            return [
                'subject'  => (string) ($row->q_subject_name ?? ''),
                'topic'    => (string) ($row->q_topic_name ?? ''),
                'scenario' => (string) ($row->q_scenario ?? ''),
                'question' => (string) ($row->q_question_text ?? ''),
                'answer'   => (string) ($row->answer_text ?? ''),
            ];
        })->values()->all();

        [$parameterText, $rubricText] = $this->buildPromptParametersAndRubrics($assignmentRow);

        $prompt = $openAIService->buildSubjectiveAssessmentReportPrompt(
            (string) $gradeName,
            (string) $boardName,
            (string) $countryName,
            $parameterText,
            $rubricText,
            $responses
        );

        $rawAiResponse = (string) $openAIService->generateAssessmentReport($prompt, 2200);
        if ($rawAiResponse === '') {
            return response()->json(['success' => false, 'message' => 'OpenAI report generation failed. Please try again.'], 500);
        }

        $questionTexts = [];
        foreach ($responses as $idx => $row) {
            $questionTexts['Question ' . ($idx + 1)] = (string) ($row['question'] ?? '');
        }

        $reportSections = $this->parseSubjectiveReportSections($rawAiResponse, $questionTexts, $assignmentRow);
        $narrativeText  = $reportSections['summary_text'] ?? '';
        if ($narrativeText === '') {
            $narrativeText = $this->extractNarrativeFromReport($rawAiResponse);
        }

        $overviewText = "This report evaluates {$student->name}'s performance based on their responses to RealQ scenarios. It reflects their current thinking skills across key parameters.";

        $reportData = $this->buildReportDataJson($rawAiResponse, $reportSections, $questionTexts, $assignmentRow, $overviewText);

        // Generate PDF
        $schoolLogoPath = public_path('asset/images/kids-tm-logo.png');
        $pdfSections    = $this->reportDataToSections($reportData);

        $pdf = PDF::loadView('student.assessment.report_pdf', [
            'studentName'    => $student->name,
            'schoolLogoPath' => $schoolLogoPath,
            'reportOverview' => $overviewText,
            'mode'           => $mode,
            'generatedAt'    => now()->toDateTimeString(),
            'total'          => $answers->count(),
            'answered'       => $answers->where('is_submitted', 1)->count(),
            'completion'     => 100,
            'reportBody'     => $rawAiResponse,
            'reportSections' => $pdfSections,
        ]);

        $tenantId        = optional(optional($student)->school)->tenant_id ?: 'common';
        $fileName        = 'realq_report_' . substr(hash('sha256', 'realq-report-' . $reportRow->id), 0, 16) . '.pdf';
        $reportPath      = trim($tenantId, '/') . '/student/reports/realq_assessment/' . $fileName;
        $absoluteDir     = public_path('tenants/' . trim($tenantId, '/') . '/student/reports/realq_assessment');

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0755, true);
        }

        // Remove any previously generated PDF for this report (e.g. from an older random filename)
        if (!empty($reportRow->report_path) && $reportRow->report_path !== $reportPath) {
            File::delete(public_path('tenants/' . $reportRow->report_path));
        }

        File::put(public_path('tenants/' . $reportPath), $pdf->output());

        // Update report record
        $reportRow->update([
            'status'      => 'generated',
            'report_data' => $reportData,
            'report_path' => $reportPath,
            'report_text' => $narrativeText,
        ]);

        // Store parameter scores (feeds school-level report aggregation)
        // Use performance_rows from report_data (they have rubric_id already)
        $performanceRowsForScores = array_map(function ($row) {
            return [
                'parameter' => $row['parameter'],
                'level'     => $row['level'],
                'rubric_id' => $row['rubric_id'] ?? null,
                'summary'   => $row['evidence'] ?? '',
            ];
        }, $reportData['performance_rows'] ?? []);

        $this->storeRealQParameterScores($student, $reportRow, $performanceRowsForScores, $assignmentRow);

        // Award reward points (first time generating — skip if already awarded)
        $alreadyRewarded = \App\Models\StudentRewardPoints::where('student_id', $student->id)
            ->where('reward_type', 'pre_assessment_score')
            ->where('item_type', 'realq')
            ->where('item_id', $reportRow->id)
            ->exists();

        if (!$alreadyRewarded) {
            StudentRewardPointsHelper::storeRewardPoints([
                'student_id'    => $student->id,
                'reward_type'   => 'pre_assessment_score',
                'item_id'       => $reportRow->id,
                'item_type'     => 'realq',
                'reward_points' => 5,
            ]);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Report generated successfully.',
            'report_id'  => $reportRow->id,
            'edit_url'   => route('backend.realqassessment.student-reports.edit', $reportRow->id),
        ]);
    }



    public function edit(int $reportId)
    {
        $reportRow = AssessmentStudentReport::find($reportId);
        if (!$reportRow || $reportRow->assessment_type !== 'realq') {
            return redirect()->route('backend.realqassessment.student-reports.index')
                ->with('error', 'Report not found.');
        }

        if (!in_array($reportRow->status, ['generated', 'approved'], true)) {
            return redirect()->route('backend.realqassessment.student-reports.index')
                ->with('error', 'Report must be generated before it can be edited.');
        }

        $student    = Students::with(['school'])->find($reportRow->student_id);
        if ($student && !applyCountryScope(School::query()->whereKey(optional($student->school)->id), 'country_id')->exists()) {
            return redirect()->route('backend.realqassessment.student-reports.index')
                ->with('error', 'Report not found.');
        }
        $reportData = is_array($reportRow->report_data) ? $reportRow->report_data : [];

        // Load rubrics for dropdowns (scoped to the student's assignment if available)
        $assignmentRow = $student ? RealQAssessmentSchoolAssignment::where('school_id', optional($student->school)->id)
            ->where('realq_assessment_assigned', 1)
            ->where('realq_assessment_assigned_grade_id', $student->student_grade_id)
            ->first() : null;

        $rubrics = $assignmentRow && !empty($assignmentRow->realq_assessment_assigned_scale_id)
            ? RealQAssessmentRubric::where('scale_id', $assignmentRow->realq_assessment_assigned_scale_id)->orderBy('score')->get()
            : RealQAssessmentRubric::orderBy('score')->get();

        if (!empty($reportData['scenario_rows'])) {
            $rubricDefs = $this->getRubricDefinitionsForAssignment($assignmentRow);
            foreach ($reportData['scenario_rows'] as &$sRow) {
                foreach ($sRow['evidence'] as &$eItem) {
                    if (empty($eItem['rubric_id']) && !empty($eItem['text'])) {
                        $resolved = $this->resolveRubricFromEvidenceValue((string) $eItem['text'], $rubricDefs);
                        if ($resolved) {
                            $eItem['rubric_id'] = (int) $resolved['id'];
                            if (empty($eItem['level'])) {
                                $eItem['level'] = $resolved['name'];
                            }
                        }
                    }
                }
                unset($eItem);
            }
            unset($sRow);
        }

        // Load student's subjective answers so admin can review them alongside evidence
        $studentAnswers = AssessmentAnswer::query()
            ->join('realq_assessment_questions', 'realq_assessment_student_answers.assessment_question_id', '=', 'realq_assessment_questions.id')
            ->where('realq_assessment_student_answers.student_id', $reportRow->student_id)
            ->where('realq_assessment_student_answers.assessment_type', 'baseline')
            ->where('realq_assessment_questions.question_type', 'subjective')
            ->select([
                'realq_assessment_student_answers.answer_text',
                'realq_assessment_questions.question_text as q_question_text',
            ])
            ->orderBy('realq_assessment_student_answers.id')
            ->get()
            ->values();

        return view('backend.realq_assessment.student_reports.edit', compact(
            'reportRow',
            'student',
            'reportData',
            'rubrics',
            'studentAnswers'
        ));
    }

    // -------------------------------------------------------------------------
    // Step 10 — update(int $reportId)
    // -------------------------------------------------------------------------

    public function update(Request $request, int $reportId)
    {
        $reportRow = AssessmentStudentReport::find($reportId);
        if (!$reportRow || $reportRow->assessment_type !== 'realq') {
            return redirect()->route('backend.realqassessment.student-reports.index')
                ->with('error', 'Report not found.');
        }

        $student = Students::with(['school'])->find($reportRow->student_id);
        if ($student && !applyCountryScope(School::query()->whereKey(optional($student->school)->id), 'country_id')->exists()) {
            return redirect()->route('backend.realqassessment.student-reports.index')
                ->with('error', 'Report not found.');
        }

        // Build rubric score map (id → score) so we can populate rubric_score on save
        $rubricScoreMap = RealQAssessmentRubric::pluck('score', 'id')->all();

        // Rebuild performance_rows from posted form data
        $performanceRows = [];
        foreach ((array) $request->input('performance_rows', []) as $row) {
            $rubricId = isset($row['rubric_id']) && $row['rubric_id'] !== '' ? (int) $row['rubric_id'] : null;
            $performanceRows[] = [
                'parameter'    => (string) ($row['parameter'] ?? ''),
                'level'        => (string) ($row['level'] ?? ''),
                'rubric_id'    => $rubricId,
                'rubric_score' => $rubricId ? ($rubricScoreMap[$rubricId] ?? null) : null,
                'evidence'     => (string) ($row['evidence'] ?? ''),
            ];
        }

        // Rebuild scenario_rows from posted form data
        $scenarioRows = [];
        foreach ((array) $request->input('scenario_rows', []) as $sRow) {
            $evidenceItems = [];
            foreach ((array) ($sRow['evidence'] ?? []) as $eItem) {
                $evidenceItems[] = [
                    'parameter' => (string) ($eItem['parameter'] ?? ''),
                    'level'     => (string) ($eItem['level'] ?? ''),
                    'rubric_id' => isset($eItem['rubric_id']) && $eItem['rubric_id'] !== '' ? (int) $eItem['rubric_id'] : null,
                    'text'      => (string) ($eItem['text'] ?? ''),
                ];
            }
            $scenarioRows[] = [
                'label'    => (string) ($sRow['label'] ?? ''),
                'question' => (string) ($sRow['question'] ?? ''),
                'evidence' => $evidenceItems,
            ];
        }

        $existingData   = is_array($reportRow->report_data) ? $reportRow->report_data : [];
        $newReportData  = [
            'overview'         => (string) $request->input('overview', $existingData['overview'] ?? ''),
            'performance_rows' => $performanceRows,
            'scenario_rows'    => $scenarioRows,
            'summary'          => (string) $request->input('summary', ''),
            'graph_rubrics'    => $existingData['graph_rubrics'] ?? [],
        ];

        // Re-resolve rubric levels for parameter scores (in case rubric_id changed)
        $assignmentRow = $student ? RealQAssessmentSchoolAssignment::where('school_id', optional($student->school)->id)
            ->where('realq_assessment_assigned', 1)
            ->where('realq_assessment_assigned_grade_id', $student->student_grade_id)
            ->first() : null;

        // Regenerate PDF from edited data
        $pdfSections    = $this->reportDataToSections($newReportData);
        $schoolLogoPath = public_path('asset/images/kids-tm-logo.png');

        $pdf = PDF::loadView('student.assessment.report_pdf', [
            'studentName'    => optional($student)->name ?? '',
            'schoolLogoPath' => $schoolLogoPath,
            'reportOverview' => $newReportData['overview'],
            'mode'           => 'subjective',
            'generatedAt'    => now()->toDateTimeString(),
            'total'          => count($scenarioRows),
            'answered'       => count($scenarioRows),
            'completion'     => 100,
            'reportBody'     => '',
            'reportSections' => $pdfSections,
        ]);

        $tenantId    = optional(optional($student)->school)->tenant_id ?: 'common';
        $fileName    = 'realq_report_' . substr(hash('sha256', 'realq-report-' . $reportRow->id), 0, 16) . '.pdf';
        $reportPath  = trim($tenantId, '/') . '/student/reports/realq_assessment/' . $fileName;
        $absoluteDir = public_path('tenants/' . trim($tenantId, '/') . '/student/reports/realq_assessment');

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0755, true);
        }

        // Remove any previously generated PDF for this report (e.g. from an older random filename)
        if (!empty($reportRow->report_path) && $reportRow->report_path !== $reportPath) {
            File::delete(public_path('tenants/' . $reportRow->report_path));
        }

        File::put(public_path('tenants/' . $reportPath), $pdf->output());

        // Re-store parameter scores from the edited performance rows
        $performanceRowsForScores = array_map(function ($row) {
            return [
                'parameter' => $row['parameter'],
                'level'     => $row['level'],
                'rubric_id' => $row['rubric_id'] ?? null,
                'summary'   => $row['evidence'] ?? '',
            ];
        }, $performanceRows);

        if ($student) {
            $this->storeRealQParameterScores($student, $reportRow, $performanceRowsForScores, $assignmentRow);
        }

        $reportRow->update([
            'report_data' => $newReportData,
            'report_path' => $reportPath,
            'report_text' => $newReportData['summary'],
            // status stays 'generated' — admin must explicitly approve
        ]);

        $schoolId = optional(optional($student)->school)->id;

        return redirect()
            ->route('backend.realqassessment.student-reports.index', ['school_id' => $schoolId, 'status' => ''])
            ->with('success', 'Report updated successfully. Use the Approve button when ready to publish to the student.');
    }

    public function approve(int $reportId)
    {
        $reportRow = AssessmentStudentReport::find($reportId);
        if (!$reportRow || $reportRow->assessment_type !== 'realq') {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 404);
        }

        $student = Students::with(['school'])->find($reportRow->student_id);
        if (!$student || !applyCountryScope(School::query()->whereKey(optional($student->school)->id), 'country_id')->exists()) {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 403);
        }

        if ($reportRow->status !== 'generated') {
            return response()->json(['success' => false, 'message' => 'Only generated reports can be approved.'], 422);
        }

        $reportRow->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Send email notification to student or parent
        $this->sendApprovalEmail($reportRow);

        return response()->json([
            'success' => true,
            'message' => 'Report approved. The student can now view and download their report.',
        ]);
    }

    private function sendApprovalEmail(AssessmentStudentReport $reportRow): void
    {

        $student = Students::with(['user:id,email'])->find($reportRow->student_id);
        if (!$student) {
            return;
        }

        // Recipient priority: student email → parent email
        $toEmail       = null;
        $recipientType = 'student';

        if (!empty($student->user?->email)) {
            $toEmail = $student->user->email;
        } elseif (!empty($student->parent_email)) {
            $toEmail       = $student->parent_email;
            $recipientType = 'parent';
        }

        if (!$toEmail) {
            Log::warning("RealQ report approved (id={$reportRow->id}) but no email found for student id={$student->id}.");
            return;
        }

        $studentName = $student->name ?? 'Student';

        safeMailAction('realq report approved mail', [
            'report_id' => $reportRow->id,
            'recipient' => $toEmail,
            'recipient_type' => $recipientType,
        ], function () use ($toEmail, $studentName, $recipientType) {
            Mail::to($toEmail)->queue(new RealQReportApprovedMail($studentName, $recipientType));
        });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildPromptParametersAndRubrics(?RealQAssessmentSchoolAssignment $assignmentRow): array
    {
        $parameterLines = [];
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_parameters_id)) {
            $paramIds = array_values(array_filter(array_map('intval', explode(',', (string) $assignmentRow->realq_assessment_assigned_parameters_id))));
            if (!empty($paramIds)) {
                $paramIdList = implode(',', $paramIds);
                $parameters  = RealQAssessmentParameter::whereIn('id', $paramIds)
                    ->orderByRaw("FIELD(id, {$paramIdList})")
                    ->get();
                foreach ($parameters as $param) {
                    $name = trim((string) ($param->name ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $desc             = trim((string) ($param->description ?? ''));
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
        $parameterText = implode("\n", array_map(fn ($l, $i) => ($i + 1) . '. ' . $l, $parameterLines, array_keys($parameterLines)));

        $rubricLines = [];
        if ($assignmentRow && !empty($assignmentRow->realq_assessment_assigned_scale_id)) {
            $rubrics = RealQAssessmentRubric::where('scale_id', $assignmentRow->realq_assessment_assigned_scale_id)
                ->orderBy('score')
                ->get();
            foreach ($rubrics as $rubric) {
                $name = trim((string) ($rubric->name ?? ''));
                if ($name === '') {
                    continue;
                }
                $desc        = trim((string) ($rubric->description ?? ''));
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
        $rubricText = implode("\n", array_map(fn ($l, $i) => ($i + 1) . '. ' . $l, $rubricLines, array_keys($rubricLines)));

        return [$parameterText, $rubricText];
    }
}
