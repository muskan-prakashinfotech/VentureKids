<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDetails;
use App\Models\Projectfiles;
use App\Models\Students;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\StudentGrade;
use App\Models\ProjectTheme;
use Illuminate\Support\Facades\Log;
use App\Models\ProjectFeedback;
use App\Helpers\StudentRewardPointsHelper;
use App\Models\StudentProject;
use App\Models\StudentProjectAnswer;
use App\Models\StudentProjectAttachment;
use App\Models\StudentProjectFeedback;
use App\Models\ProjectSection;
use App\Models\ProjectQuestion;
use App\Services\OpenAIService;
use App\Services\ProjectPdfExportService;

class StudentProjectController extends Controller
{
    public function index()
    {
        $student = Students::where('user_id', Session::get('user_id'))->first();
        $projects = [];
        if (!empty($student)) {
            $studentId = $student->id;

           // $projects = Project::with('project')->where('student_id', $studentId)->get();
            $projects = Project::with('theme:id,project_theme_name')
            ->where('student_id', $studentId)
            ->select('id', 'student_id', 'title','project_theme_id', 'project_overview', 'project_skills', 'project_challenges', 'project_status','is_publish')
            ->get();
        }

        return view('student.project.project_list', [
            'projects' => $projects,
        ]);
    }

    public function myProjects()
    {
        $student = Students::where('user_id', Session::get('user_id'))->first();
        $projects = collect();

        if (!empty($student)) {
            $projects = StudentProject::where('student_id', $student->id)
                ->with('feedback')
                ->latest('updated_at')
                ->get();

            $this->attachProjectSummaries($projects);
        }

        return view('student.project.my_projects', [
            'projects' => $projects,
        ]);
    }

    private function attachProjectSummaries($projects)
    {
        $projectIds = $projects->pluck('id');

        if ($projectIds->isEmpty()) {
            return;
        }

        $answersByProject = StudentProjectAnswer::whereIn('student_project_answers.student_project_id', $projectIds)
            ->join('project_questions', 'project_questions.id', '=', 'student_project_answers.project_question_id')
            ->where(function ($query) {
                $query->where('project_questions.field_text', 'like', '%title%')
                    ->orWhere('project_questions.field_text', 'like', '%theme%');
            })
            ->get([
                'student_project_answers.student_project_id',
                'student_project_answers.answer_text',
                'project_questions.field_text',
            ])
            ->groupBy('student_project_id');

        $imagesByProject = StudentProjectAttachment::whereIn('student_project_id', $projectIds)
            ->where('file_type', 'images')
            ->get()
            ->groupBy('student_project_id');

        foreach ($projects as $project) {
            $answers = $answersByProject->get($project->id, collect());

            $project->display_title = optional(
                $answers->first(fn ($answer) => stripos($answer->field_text, 'title') !== false)
            )->answer_text;

            $project->display_theme = optional(
                $answers->first(fn ($answer) => stripos($answer->field_text, 'theme') !== false)
            )->answer_text;

            $images = $imagesByProject->get($project->id, collect());
            $project->display_image = $images->isNotEmpty() ? $images->random() : null;
        }
    }

    public function downloadAttachment($id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();

        $attachment = StudentProjectAttachment::where('id', $id)
            ->whereHas('studentProject', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->firstOrFail();

        if ($attachment->file_type === 'videos' || !$attachment->full_path || !file_exists($attachment->full_path)) {
            abort(404);
        }

        return response()->download($attachment->full_path, $attachment->file_name);
    }

    public function createProject(Request $request, $id = null)
    {
        $student = Students::where('user_id', Session::get('user_id'))->first();
        $projectId = $id;

        $sections = $this->orderedSectionsWithQuestions();
        $hasActiveQuestions = $sections->contains(fn ($section) => $section->questions->isNotEmpty());

        if ($sections->isEmpty() || !$hasActiveQuestions) {
            $message = 'Project creation is temporarily unavailable.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('student.my-projects')->with('error', $message);
        }

        $totalSteps = max($sections->count(), 1);
        $step = 1;

        $studentProject = null;
        if ($projectId) {
            $studentProject = StudentProject::where('student_id', optional($student)->id)->findOrFail($projectId);

            if ((int) $studentProject->status === 2) {
                $studentProject->update(['status' => 3]);
            }

            if ((int) $studentProject->status >= 4) {
                $redirectUrl = route('student.confirm-submission', $studentProject->id);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['redirect' => $redirectUrl]);
                }

                return redirect()->to($redirectUrl);
            }
        }

        $answers = $this->loadAnswers(optional($studentProject)->id);
        $attachments = $this->loadAttachments(optional($studentProject)->id);

        $viewData = $this->builderViewData($step, $totalSteps, $sections, $studentProject, $projectId, $answers, $attachments);

        return view('student.project.create_project', $viewData);
    }

    public function saveProjectStep(Request $request)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $step = (int) $request->input('step', 1);
        $projectId = $request->input('project_id');
        $action = $request->input('action', 'continue');

        $sections = $this->orderedSections();
        $totalSteps = max($sections->count(), 1);
        $sectionRow = $sections->get($step - 1);
        abort_if(!$sectionRow, 404);

        $section = $this->loadSectionWithQuestions($sectionRow->id);
        $existingAttachmentQuestionIds = $this->existingAttachmentQuestionIds($projectId, $student->id);

        $validator = Validator::make(
            $request->all(),
            $this->stepValidationRules($section),
            [
                'answers.*.required' => 'This field is required.',
            ]
        );

        $validator->after(function ($validator) use ($request, $section, $existingAttachmentQuestionIds) {
            foreach ($section->questions as $question) {
                if ($question->field_type !== 'file' || !$question->is_required) {
                    continue;
                }

                $hasNewFile = count(array_filter((array) $request->file("attachments.{$question->id}", []))) > 0;
                $hasNewVideo = count(array_filter((array) $request->input("video_urls.{$question->id}", []))) > 0;
                $hasExisting = $existingAttachmentQuestionIds->contains($question->id);

                if (!$hasNewFile && !$hasNewVideo && !$hasExisting) {
                    $validator->errors()->add("attachments.{$question->id}", 'This field is required.');
                    $validator->errors()->add("video_urls.{$question->id}", 'This field is required.');
                }
            }
        });

        $validator->validate();

        $studentProject = $projectId
            ? StudentProject::where('student_id', $student->id)->findOrFail($projectId)
            : StudentProject::create(['student_id' => $student->id, 'status' => 0]);

        if ((int) $studentProject->status >= 4) {
            $redirectUrl = route('student.confirm-submission', $studentProject->id);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['redirect' => $redirectUrl]);
            }

            return redirect()->to($redirectUrl);
        }

        $this->removeQueuedAttachments($request, $studentProject);
        $this->saveStepAnswers($request, $studentProject, $section);

        if ($step === 1 && (int) $studentProject->status < 1) {
            $studentProject->update(['status' => 1]);
        }

        if ($action === 'draft') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['redirect' => route('student.my-projects')]);
            }
            return redirect()->route('student.my-projects')->with('message', 'Project saved as draft!');
        }

        $nextStep = $step + 1;

        if ($nextStep > $totalSteps) {
            if ($studentProject->improvement_comments) {
                if ((int) $studentProject->status < 4) {
                    $studentProject->update(['status' => 4]);
                }

                $redirectUrl = route('student.confirm-submission', $studentProject->id);
            } else {
                $redirectUrl = route('student.confirm-improvement-ideas', $studentProject->id);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['redirect' => $redirectUrl]);
            }

            return redirect()->to($redirectUrl);
        }

        if ($request->expectsJson() || $request->ajax()) {
            $savedAnswers = $this->loadAnswers($studentProject->id)->only($section->questions->pluck('id')->all());

            return response()->json([
                'success' => true,
                'step' => $nextStep,
                'totalSteps' => $totalSteps,
                'projectId' => $studentProject->id,
                'savedStep' => $step,
                'answers' => $savedAnswers,
            ]);
        }

        return redirect()->route('student.create-project', ['id' => $studentProject->id]);
    }

    public function confirmImprovementIdeas($id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::where('student_id', $student->id)->findOrFail($id);

        if ((int) $studentProject->status >= 4) {
            return redirect()->route('student.confirm-submission', $studentProject->id);
        }

        if ($studentProject->improvement_comments) {
            return redirect()->route('student.project-suggestions', $studentProject->id);
        }

        return view('student.project.get_improvement_ideas', [
            'studentProject' => $studentProject,
            'sections' => $this->orderedSections(),
            'generateUrl' => route('student.generate-improvement-report', $studentProject->id),
        ]);
    }

    public function generateImprovementReport(Request $request, $id, OpenAIService $openAIService)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::where('student_id', $student->id)->findOrFail($id);

        if ((int) $studentProject->status >= 4) {
            return response()->json([
                'success' => true,
                'redirect' => route('student.confirm-submission', $studentProject->id),
            ]);
        }

        if ((int) $studentProject->status < 2) {
            $studentProject->update(['status' => 2]);
        }

        if (!$studentProject->improvement_comments) {
            $report = $this->generateAIProjectImprovementReport($studentProject, $student, $openAIService);

            if (str_starts_with($report, 'The AI report could not be generated.')) {
                Log::error('AI improvement report generation failed for student_project_id ' . $studentProject->id);

                return response()->json([
                    'success' => false,
                    'message' => 'We could not generate your improvement ideas right now. Please try again.',
                ], 422);
            }

            $studentProject->update(['improvement_comments' => $report]);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('student.project-suggestions', $studentProject->id),
        ]);
    }

    public function confirmSubmission($id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::with('feedback')->where('student_id', $student->id)->findOrFail($id);

        if ((int) $studentProject->status >= 5 || ((int) $studentProject->status == 4 && $studentProject->feedback && (int) $studentProject->feedback->is_publish === 1)) {
            return redirect()->route('student.project-feedback', $studentProject->id);
        }

        $alreadySubmitted = (bool) $studentProject->is_submitted;

        $meta = $this->projectDisplayMeta($studentProject->id);
        $attachments = $this->loadAttachments($studentProject->id)->flatten();

        $checklist = array_values(array_filter([
            $meta['title'] ? 'Project title added' : null,
            'All sections completed',
            $attachments->contains('file_type', 'images') ? 'Photos uploaded' : null,
            $attachments->contains('file_type', 'videos') ? 'Videos added' : null,
            $studentProject->improvement_comments ? 'Improvement ideas reviewed' : null,
        ]));

        return view('student.project.confirm_submission', [
            'studentProject' => $studentProject,
            'displayTitle' => $meta['title'],
            'displayTheme' => $meta['theme'],
            'checklist' => $checklist,
            'alreadySubmitted' => $alreadySubmitted,
            'submitUrl' => route('student.submit-project', $studentProject->id),
            'makeChangesUrl' => route('student.make-changes', $studentProject->id),
        ]);
    }

    public function submitProject($id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::where('student_id', $student->id)->findOrFail($id);

        if (!$studentProject->is_submitted) {
            $studentProject->update(['status' => 4, 'is_submitted' => 1]);
        }
        
        /* Store reward points for project submission if not already rewarded */
        $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist($student->id, 'project', $id);
        if (!$alreadyRewarded->count()) {
            StudentRewardPointsHelper::storeRewardPoints([
                'student_id' => $student->id,
                'reward_type' => 'project',
                'item_id' => $id,
                'reward_points' => 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('student.my-projects'),
        ]);
    }

    public function makeChanges($id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::with('feedback')
            ->where('student_id', $student->id)
            ->findOrFail($id);

        $latestFeedback = StudentProjectFeedback::where('student_project_id', $studentProject->id)
            ->orderByDesc('id')
            ->first();

        if ($latestFeedback) {
            $latestFeedback->update(['is_publish' => 3]);
        }

        $studentProject->update([
            'status' => 1,
            'is_submitted' => 0,
            'published_sections' => null,
        ]);

        return redirect()->route('student.create-project', ['id' => $studentProject->id]);
    }

    public function viewProjectFeedback($id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::with(['feedback.trainer'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        if ((int) $studentProject->status < 4) {
            return redirect()->route('student.confirm-submission', $studentProject->id);
        }

        if (!$studentProject->feedback) {
            return redirect()->route('student.my-projects');
        }

        if ((int) $studentProject->status === 4 && (int) $studentProject->feedback->is_publish === 0) {
            return redirect()->route('student.confirm-submission', $studentProject->id);
        }

        $sections = $this->orderedSectionsWithQuestions();
        $answers = $this->loadAnswers($studentProject->id);
        $attachments = $this->loadAttachments($studentProject->id);
        $meta = $this->projectDisplayMeta($studentProject->id);

        $publishModalData = $sections->map(function ($section) use ($answers, $attachments) {
            return [
                'id' => $section->id,
                'title' => $section->section_title,
                'questions' => $section->questions->map(function ($question) use ($answers, $attachments) {
                    $images = $question->allow_attachments
                        ? $attachments->get($question->id, collect())
                            ->where('file_type', 'images')
                            ->map(fn ($attachment) => [
                                'url' => $attachment->url,
                                'name' => $attachment->file_name,
                            ])->values()
                        : collect();

                    return [
                        'id' => $question->id,
                        'text' => $question->field_text,
                        'answer' => $question->field_type !== 'file' ? $answers->get($question->id) : null,
                        'images' => $images,
                    ];
                })->values(),
            ];
        })->values();

        return view('student.project.project_feedback', [
            'studentProject' => $studentProject,
            'feedback' => $studentProject->feedback,
            'wizardUrl' => route('student.create-project', ['id' => $studentProject->id]),
            'sections' => $sections,
            'answers' => $answers,
            'attachments' => $attachments,
            'displayTitle' => $meta['title'],
            'displayTheme' => $meta['theme'],
            'publishModalData' => $publishModalData,
            'publishSectionsUrl' => route('student.save-publish-sections', $studentProject->id),
        ]);
    }

    public function savePublishSections(Request $request, $id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::with('feedback')->where('student_id', $student->id)->findOrFail($id);

        if ((int) optional($studentProject->feedback)->is_publish !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Your project must be approved by your teacher before you can select sections to publish.',
            ], 422);
        }

        $activeSectionIds = $this->orderedSections()->pluck('id');

        $validated = $request->validate([
            'sections' => 'nullable|array',
            'sections.*' => 'integer',
        ]);

        $selectedSectionIds = collect($validated['sections'] ?? [])
            ->map(fn ($sectionId) => (int) $sectionId)
            ->intersect($activeSectionIds)
            ->values()
            ->all();

        // Basic Details is the first active section and must always be published.
        $requiredSectionId = $activeSectionIds->first();
        if ($requiredSectionId !== null && !in_array((int) $requiredSectionId, $selectedSectionIds, true)) {
            array_unshift($selectedSectionIds, (int) $requiredSectionId);
        }

        $studentProject->update([
            'published_sections' => $selectedSectionIds,
            'status' => 6,
        ]);

        return response()->json([
            'success' => true,
            'published_sections' => $selectedSectionIds,
        ]);
    }

    public function downloadProjectPdf($id, ProjectPdfExportService $projectPdfExportService)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::with(['student.school', 'student.getAssignedBatch'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        $pdfBinary = $projectPdfExportService->renderPdfBinary($studentProject);
        $fileName = $projectPdfExportService->buildFileName($studentProject);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function projectSuggestions($id)
    {
        $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
        $studentProject = StudentProject::where('student_id', $student->id)->findOrFail($id);

        if ((int) $studentProject->status >= 4) {
            return redirect()->route('student.confirm-submission', $studentProject->id);
        }

        if (!$studentProject->improvement_comments) {
            return redirect()->route('student.create-project', ['id' => $studentProject->id])
                ->with('message', 'Please complete all project steps to get your improvement ideas.');
        }

        $report = $this->parseImprovementReport($studentProject->improvement_comments);
        $attachments = $this->loadAttachments($studentProject->id);
        $photos = $attachments->flatten()->where('file_type', 'images')->values();
        $meta = $this->projectDisplayMeta($studentProject->id);

        return view('student.project.project_suggestions', [
            'studentProject' => $studentProject,
            'displayTitle' => $meta['title'],
            'displayTheme' => $meta['theme'],
            'photos' => $photos,
            'summary' => $report['summary'],
            'workingWell' => $report['workingWell'],
            'canImprove' => $report['canImprove'],
            'evidenceToAdd' => $report['evidenceToAdd'],
            'tips' => $report['tips'],
            'readinessLevel' => $report['readinessLevel'],
            'readinessExplanation' => $report['readinessExplanation'],
            'rawReport' => $studentProject->improvement_comments,
        ]);
    }

    private function projectDisplayMeta($studentProjectId)
    {
        $rows = StudentProjectAnswer::where('student_project_id', $studentProjectId)
            ->join('project_questions', 'project_questions.id', '=', 'student_project_answers.project_question_id')
            ->where(function ($query) {
                $query->where('project_questions.field_text', 'like', '%title%')
                    ->orWhere('project_questions.field_text', 'like', '%theme%');
            })
            ->get(['student_project_answers.answer_text', 'project_questions.field_text']);

        return [
            'title' => optional($rows->first(fn ($row) => stripos($row->field_text, 'title') !== false))->answer_text,
            'theme' => optional($rows->first(fn ($row) => stripos($row->field_text, 'theme') !== false))->answer_text,
        ];
    }

    /**
     * Parses the fixed "Final Output Format" structure produced by
     * OpenAIService::buildProjectImprovementPrompt() into display-friendly pieces.
     * Falls back to empty/null values (view renders the raw report instead) if the
     * model's output doesn't match the expected section labels.
     */
    private function parseImprovementReport(string $report): array
    {
        $text = str_replace('**', '', $report);

        $result = [
            'summary' => null,
            'workingWell' => null,
            'canImprove' => null,
            'evidenceToAdd' => null,
            'tips' => [],
            'readinessLevel' => null,
            'readinessExplanation' => null,
        ];

        $segment1 = $text;
        $segment2 = '';
        if (preg_match('/(.*?)Segment\s*2\s*[-:]?\s*Portfolio Readiness(.*)/is', $text, $m)) {
            $segment1 = $m[1];
            $segment2 = $m[2];
        }

        // "Summary Understanding" is not a literal heading mandated by the prompt (item 1 is
        // only described as "Summarize understanding of the project briefly"), so the model's
        // wording for it varies run to run. The other four headings below are copied verbatim
        // in the prompt, so they're reliable anchors - summary is instead derived as "whatever
        // text comes before the first reliable anchor" further down.
        $labels = [
            'workingWell' => 'What Is Working Well',
            'canImprove' => 'What Can Be Improved',
            'evidenceToAdd' => 'Evidence to Add',
            'priorityActions' => 'Five Priority Actions Before Submission',
        ];

        $positions = [];
        $matchLengths = [];
        foreach ($labels as $key => $label) {
            if (preg_match('/\d*\.?\s*' . preg_quote($label, '/') . '\s*:?/i', $segment1, $m, PREG_OFFSET_CAPTURE)) {
                $positions[$key] = $m[0][1];
                $matchLengths[$key] = strlen($m[0][0]);
            }
        }

        $ordered = $positions;
        asort($ordered);
        $keys = array_keys($ordered);

        foreach ($keys as $i => $key) {
            $start = $positions[$key] + $matchLengths[$key];
            $end = isset($keys[$i + 1]) ? $positions[$keys[$i + 1]] : strlen($segment1);
            $chunk = trim(substr($segment1, $start, $end - $start));
            $chunk = trim($chunk, ":\n\r\t -");

            if ($key === 'priorityActions') {
                $result['tips'] = $this->parsePriorityActionTips($chunk);
            } elseif (array_key_exists($key, $result)) {
                $result[$key] = $chunk !== '' ? $chunk : null;
            }
        }

        $summaryEnd = $keys ? $positions[$keys[0]] : strlen($segment1);
        $summaryLead = substr($segment1, 0, $summaryEnd);
        $summaryLead = preg_replace('/^.*?Segment\s*1\s*[:\-]?\s*Basic\s*Details\s*/is', '', $summaryLead) ?? $summaryLead;
        $summaryLead = preg_replace('/^\d*\.?\s*[^\n:]{0,80}:\s*/s', '', trim($summaryLead), 1) ?? trim($summaryLead);
        $summaryLead = trim($summaryLead, ":\n\r\t -");
        $result['summary'] = $summaryLead !== '' ? $summaryLead : null;

        $levels = ['Not Ready Yet', 'Getting There', 'Almost Ready', 'Ready to Publish', 'Excellent Showcase'];
        if (preg_match('/(' . implode('|', array_map(fn ($l) => preg_quote($l, '/'), $levels)) . ')/i', $segment2, $m, PREG_OFFSET_CAPTURE)) {
            $result['readinessLevel'] = $m[1][0];
            $result['readinessExplanation'] = trim(substr($segment2, $m[1][1] + strlen($m[1][0])), ":\n\r\t -");
        }

        return $result;
    }

    private function parsePriorityActionTips(string $chunk): array
    {
        $tips = [];

        preg_match_all('/Action:?\s*(.+?)\s*(?:\n|\r)+.*?Why It Matters:?\s*(.+?)(?:\n\s*\n|\n\s*-?\s*(?:\d+\.|Completion Check)|$)/is', $chunk, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tips[] = [
                'action' => trim($match[1]),
                'why' => trim($match[2]),
            ];
        }

        return array_slice($tips, 0, 5);
    }

    private function removeQueuedAttachments(Request $request, StudentProject $studentProject)
    {
        $ids = array_filter((array) $request->input('remove_attachments', []));

        if (empty($ids)) {
            return;
        }

        $attachments = StudentProjectAttachment::where('student_project_id', $studentProject->id)
            ->whereIn('id', $ids)
            ->get();

        foreach ($attachments as $attachment) {
            $filePath = $this->getFullFilePath($attachment->file_path);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $attachment->delete();
        }
    }

    private function generateAIProjectImprovementReport(StudentProject $studentProject, Students $student, OpenAIService $openAIService)
    {
        $sections = ProjectSection::where('status', 1)
            ->orderBy('display_order')
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->get();

        $answers = $this->loadAnswers($studentProject->id);
        $attachments = $this->loadAttachments($studentProject->id);

        $responseLines = [];
        $attachmentLines = [];
        $imageContentParts = [];

        foreach ($sections as $section) {
            $responseLines[] = $section->section_title . ':';

            foreach ($section->questions as $question) {
                $answerText = $answers->get($question->id);
                $answerText = ($answerText !== null && trim((string) $answerText) !== '') ? $answerText : '(No answer provided)';
                $responseLines[] = '- ' . $question->field_text . ': ' . $answerText;

                foreach ($attachments->get($question->id, collect()) as $attachment) {
                    $label = $section->section_title . ' - ' . $question->field_text;
                    $fullPath = $this->getFullFilePath($attachment->file_path);

                    if ($attachment->file_type === 'images') {
                        if (file_exists($fullPath)) {
                            $mime = $this->imageMimeType($fullPath);
                            $imageContentParts[] = [
                                'type' => 'text',
                                'text' => 'Image attached to "' . $label . '": ' . $attachment->file_name,
                            ];
                            $imageContentParts[] = [
                                'type' => 'image_url',
                                'image_url' => ['url' => 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath))],
                            ];
                            $attachmentLines[] = '- [' . $label . '] ' . $attachment->file_name . ' (image) - attached below as visual evidence.';
                        } else {
                            $attachmentLines[] = '- [' . $label . '] ' . $attachment->file_name . ' (image) - file missing on disk, could not be analysed.';
                        }
                    } 
                    /*elseif ($attachment->file_type === 'pdf') {
                        $pdfText = file_exists($fullPath) ? $this->extractPdfText($fullPath) : null;
                        $attachmentLines[] = '- [' . $label . '] ' . $attachment->file_name . ' (pdf)';
                        $attachmentLines[] = '  Extracted text: ' . ($pdfText !== null && $pdfText !== '' ? $pdfText : '(No extractable text found - this PDF may be a scanned image or unreadable.)');
                    } else {
                        $attachmentLines[] = $attachment->file_type === 'videos'
                            ? '- [' . $label . '] ' . $attachment->file_name . ' (video URL) - link provided, not opened or analysed.'
                            : '- [' . $label . '] ' . $attachment->file_name . ' (' . $attachment->file_type . ') - filename only, content not analysed.';
                    }*/
                }
            }

            $responseLines[] = '';
        }

        $studentResponses = implode("\n", $responseLines);
        $attachmentsSummary = $attachmentLines ? implode("\n", $attachmentLines) : 'No supporting materials were uploaded.';
        $gradeLevel = optional(StudentGrade::find($student->student_grade_id))->name ?: 'Not specified';

        $systemPrompt = $openAIService->buildProjectImprovementPrompt();

        $userText = "Student Information:\n"
            . "Student Name: " . ($student->name ?? 'Unknown') . "\n"
            . "Student Grade Level: " . $gradeLevel . "\n\n"
            . "Student's Written Responses (all active project segments):\n"
            . $studentResponses . "\n\n"
            . "Uploaded Supporting Materials Summary:\n"
            . $attachmentsSummary . "\n\n"
            . "Important: The text above the responses block is metadata and evidence summary. The text inside the responses block is the student's actual response content that must be evaluated.";

        $userContent = $imageContentParts
            ? array_merge([['type' => 'text', 'text' => $userText]], $imageContentParts)
            : $userText;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userContent],
        ];
        
        return $openAIService->generateProjectImprovementReport($messages);
    }

    private function extractPdfText($fullPath)
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $text = trim($parser->parseFile($fullPath)->getText());
        } catch (\Exception $e) {
            Log::error('PDF text extraction failed for ' . $fullPath . ': ' . $e->getMessage());
            return null;
        }

        return $text !== '' ? $text : null;
    }

    private function imageMimeType($fullPath)
    {
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        return $map[$extension] ?? 'image/jpeg';
    }

    private function orderedSections()
    {
        return ProjectSection::where('status', 1)
            ->orderBy('display_order')
            ->select('id', 'section_title', 'display_order')
            ->get();
    }

    private function orderedSectionsWithQuestions()
    {
        return ProjectSection::where('status', 1)
            ->orderBy('display_order')
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->get();
    }

    private function loadSectionWithQuestions($sectionId)
    {
        return ProjectSection::select('id', 'section_title')
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->findOrFail($sectionId);
    }

    private function loadAnswers($studentProjectId)
    {
        if (!$studentProjectId) {
            return collect();
        }

        return StudentProjectAnswer::where('student_project_id', $studentProjectId)
            ->pluck('answer_text', 'project_question_id');
    }

    private function loadAttachments($studentProjectId)
    {
        if (!$studentProjectId) {
            return collect();
        }

        return StudentProjectAttachment::where('student_project_id', $studentProjectId)
            ->get()
            ->groupBy('project_question_id');
    }

    private function builderViewData($step, $totalSteps, $sections, $studentProject, $projectId, $answers, $attachments)
    {
        $showImprovementPointers = (int) optional($studentProject)->status === 3
            && trim((string) optional($studentProject)->improvement_comments) !== '';
        $improvementPointers = $showImprovementPointers
            ? $this->parseImprovementReport($studentProject->improvement_comments)
            : [
                'tips' => [],
                'readinessLevel' => null,
                'readinessExplanation' => null,
            ];

        return [
            'step' => $step,
            'totalSteps' => $totalSteps,
            'sections' => $sections,
            'studentProject' => $studentProject,
            'projectId' => $projectId,
            'answers' => $answers,
            'attachments' => $attachments,
            'isImproving' => (bool) optional($studentProject)->improvement_comments,
            'showImprovementPointers' => $showImprovementPointers,
            'improvementPointers' => $improvementPointers,
        ];
    }

    private function existingAttachmentQuestionIds($projectId, $studentId)
    {
        if (!$projectId) {
            return collect();
        }

        return StudentProjectAttachment::where('student_project_id', $projectId)
            ->whereHas('studentProject', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->pluck('project_question_id')
            ->unique();
    }

    private function stepValidationRules(ProjectSection $section)
    {
        $rules = [];

        foreach ($section->questions as $question) {
            if ($question->field_type !== 'file') {
                $rules["answers.{$question->id}"] = ($question->is_required ? 'required' : 'nullable') . '|string';
            }

            if ($question->allow_attachments) {
                $allowedTypes = array_filter(explode(',', (string) $question->allowed_types));
                $nonVideoTypes = implode(',', array_diff($allowedTypes, ['videos']));

                if ($nonVideoTypes !== '') {
                    $rule = 'file|max:10240';
                    if ($extensions = $this->allowedExtensions($nonVideoTypes)) {
                        $rule .= '|mimes:' . implode(',', $extensions);
                    }
                    $rules["attachments.{$question->id}.*"] = $rule;
                }

                if (in_array('videos', $allowedTypes)) {
                    $rules["video_urls.{$question->id}.*"] = 'nullable|url|max:2048';
                }
            }
        }

        return $rules;
    }

    private function saveStepAnswers(Request $request, StudentProject $studentProject, ProjectSection $section)
    {
        $now = now();
        $attachmentRows = [];
        $questionIdsToReplace = [];

        foreach ($section->questions as $question) {
            $answerText = $request->input("answers.{$question->id}");

            if ($answerText !== null) {
                StudentProjectAnswer::updateOrCreate(
                    [
                        'student_project_id' => $studentProject->id,
                        'project_question_id' => $question->id,
                    ],
                    [
                        'answer_text' => $answerText,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            } else {
                StudentProjectAnswer::where('student_project_id', $studentProject->id)
                    ->where('project_question_id', $question->id)
                    ->delete();
            }

            if (!$question->allow_attachments) {
                continue;
            }

            $newFiles = $request->hasFile("attachments.{$question->id}") ? $request->file("attachments.{$question->id}") : [];
            $newVideos = array_values(array_filter((array) $request->input("video_urls.{$question->id}", [])));

            if (!$newFiles && !$newVideos) {
                continue;
            }

            if (!$question->allowed_multiples) {
                $questionIdsToReplace[] = $question->id;
                $newFiles = array_slice($newFiles, 0, 1);
                $newVideos = array_slice($newVideos, 0, 1);
            }

            if ($newFiles) {
                $attachmentRows = array_merge(
                    $attachmentRows,
                    $this->prepareUploadedFiles($studentProject, $question, $newFiles, $now)
                );
            }

            if ($newVideos) {
                $attachmentRows = array_merge(
                    $attachmentRows,
                    $this->prepareUploadedVideos($studentProject, $question, $newVideos, $now)
                );
            }
        }

        if ($questionIdsToReplace) {
            StudentProjectAttachment::where('student_project_id', $studentProject->id)
                ->whereIn('project_question_id', $questionIdsToReplace)
                ->delete();
        }

        if ($attachmentRows) {
            StudentProjectAttachment::insert($attachmentRows);
        }
    }


    private function prepareUploadedVideos(StudentProject $studentProject, ProjectQuestion $question, array $videos, $timestamp)
    {
        $rows = [];

        foreach ($videos as $videoUrl) {
            $videoUrl = trim((string) $videoUrl);

            if ($videoUrl === '' || !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                continue;
            }

            $rows[] = [
                'student_project_id' => $studentProject->id,
                'project_question_id' => $question->id,
                'file_path' => $videoUrl,
                'file_name' => $videoUrl,
                'file_type' => 'videos',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        return $rows;
    }

    private function prepareUploadedFiles(StudentProject $studentProject, ProjectQuestion $question, array $files, $timestamp)
    {
        $tenantPath = 'student/project/attachments';
        $rows = [];

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $fileName = Str::random(20) . '.' . $extension;

            if (tenant() && tenant()->tenant_id) {
                $file->storeAs(tenant()->tenant_id . '/' . $tenantPath, $fileName, 'tenant_uploads');
                $storedPath = tenant()->tenant_id . '/' . $tenantPath . '/' . $fileName;
            } else {
                $file->move(public_path($tenantPath), $fileName);
                $storedPath = $tenantPath . '/' . $fileName;
            }

            $rows[] = [
                'student_project_id' => $studentProject->id,
                'project_question_id' => $question->id,
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $this->resolveFileType($extension),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        return $rows;
    }

    private function getFullFilePath($storedPath)
    {
        if (tenant() && tenant()->tenant_id) {
            return public_path('tenants/' . $storedPath);
        }

        return public_path($storedPath);
    }

    private function resolveFileType($extension)
    {
        foreach ($this->attachmentTypeExtensionMap() as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return 'other';
    }

    private function allowedExtensions($allowedTypes)
    {
        $map = $this->attachmentTypeExtensionMap();
        $extensions = [];

        foreach (array_filter(explode(',', (string) $allowedTypes)) as $type) {
            $extensions = array_merge($extensions, $map[$type] ?? []);
        }

        return $extensions;
    }

    private function attachmentTypeExtensionMap()
    {
        return [
            'pdf' => ['pdf'],
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'videos' => ['mp4', 'mov', 'avi', 'mkv'],
        ];
    }


    // public function addProject()
    // {
    //     $student = Students::where('user_id', Session::get('user_id'))->first();
    //     $studentId = $student->id;
    //     $grades = StudentGrade::pluck('name')->toArray();

    //     return view('student.project.project_add', [
    //         'student_id' => $studentId,
    //         'grades' =>$grades,
    //     ]);
    // }

//    public function saveProject(Request $request)
//     {
//         $request->validate([
//             'title' => 'required|string',
//             'grade' => 'required|string',
//             'project_theme' => 'required|string',
//             'overview' => 'required|string',
//             'skills' => 'required|string',
//             'projectPublish' => 'required|in:0,1',
//             'reflection' => 'required|string',
//             'supporting_files.*' => 'nullable|file|mimes:jpeg,jpg,png,mp4,mov,avi,mkv,pdf',
//             'project_reflection' => 'required|string'
//         ]);

//         $grade = StudentGrade::where('name', $request->grade)->first();
//         $theme = ProjectTheme::where('project_theme_name', $request->project_theme)->first();

//         if (!$grade || !$theme) {
//             return redirect()->back()->withInput()->with(['error' => 'Invalid grade or theme selected.']);
//         }

//         $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
//         $studentId = $student->id;

//         $project = new Project();
//         $project->title = $request->title;
//         $project->student_id = $studentId;
//         $project->student_grade_id = $grade->id;
//         $project->project_theme_id = $theme->id;
//         $project->project_overview = $request->overview;
//         $project->project_skills = $request->skills;
//         $project->project_challenges = $request->reflection;
//         $project->project_reflection = $request->project_reflection;
//         $project->project_status = 0;
//         $project->is_publish = $request->projectPublish;
//         $project->save();

//         $projectId = $project->id;

//         // Handle Supporting Files (Images, Videos, PDFs)
//         if ($request->has('supporting_files')) {
//             $supportingFiles = $request->file('supporting_files', []);

//             if (!empty($supportingFiles)) {
//                 foreach ($supportingFiles as $file) {
//                     if (!$file) continue; // Skip empty file inputs

//                     $extension = strtolower($file->getClientOriginalExtension());
//                     $name = Str::random(10);

//                     // Determine file type and paths
//                     $attachmentType = '';
//                     $uploadPath = '';
//                     $fileName = '';

//                     if (in_array($extension, ['jpeg', 'jpg', 'png'])) {
//                         $attachmentType = 'Image';
//                         $fileName = 'image_' . $name . '.' . $extension;
//                         $uploadPath = 'image/project/';
//                         $tenantPath = 'student/project/image';
//                     } elseif (in_array($extension, ['mp4', 'mov', 'avi', 'mkv'])) {
//                         $attachmentType = 'Video';
//                         $fileName = 'video_' . $name . '.' . $extension;
//                         $uploadPath = 'videos/project/';
//                         $tenantPath = 'student/project/videos';
//                     } elseif ($extension === 'pdf') {
//                         $attachmentType = 'Pdf';
//                         $fileName = 'attachment_' . $name . '.' . $extension;
//                         $uploadPath = 'attachments/project/';
//                         $tenantPath = 'student/project/attachments';
//                     } else {
//                         continue;
//                     }

//                     // Upload file
//                     if (tenant() && tenant()->tenant_id) {
//                         $file->storeAs(tenant()->tenant_id . '/' . $tenantPath, $fileName, 'tenant_uploads');
//                         $uploadPath = tenant()->tenant_id . '/' . $tenantPath . '/';
//                     } else {
//                         $file->move(public_path($uploadPath), $fileName);
//                     }

//                     $projectfiles = new Projectfiles();
//                     $projectfiles->project_id = $projectId;
//                     $projectfiles->student_id = $studentId;
//                     $projectfiles->attachment_type = $attachmentType;
//                     $projectfiles->attachment = $uploadPath . $fileName;
//                     $projectfiles->save();
//                 }
//             }
//         }

//         $pointsAwarded = $this->storeProjectRewardPoints($studentId, $projectId, $request->projectPublish);

//         $message = $request->projectPublish == 0 ? 'Project Drafted Successfully!' : 'Project Added Successfully!';

//         return redirect()->route('student.list-project')->with(['message' => $message, 'confetti_visible' =>   $pointsAwarded == 1 ? 1 : 0]);
//     }

//     public function editProject($id)
//     {
//         $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
//         $studentId = $student->id;

//         // Fetch project belonging to the student
//         $project = Project::where('id', $id)->where('student_id', $studentId)->firstOrFail();

//         if (in_array($project->project_status, [1, 2])) {
//             return redirect()->route('student.list-project')->with('error', 'You cannot edit this project.');
//         }

//         // Get grade names
//         $grades = StudentGrade::pluck('name')->toArray();

//         $projectGrade = optional(StudentGrade::find($project->student_grade_id))->name;
//         $projectTheme = optional(ProjectTheme::find($project->project_theme_id))->project_theme_name;

//         $projectFiles = Projectfiles::where('project_id', $id)->where('student_id', $studentId)->get();

//         // Organize files by type
//         $projectImages = $projectFiles->where('attachment_type', 'Image')->values();
//         $projectAttachments = $projectFiles->where('attachment_type', 'Pdf')->values();
//         $projectVideos = $projectFiles->where('attachment_type', 'Video')->values();

//         // Pass to view
//         return view('student.project.project_edit', [
//             'project' => $project,
//             'grades' => $grades,
//             'projectGrade' => $projectGrade,
//             'projectTheme' => $projectTheme,
//             'projectImages' => $projectImages,
//             'projectAttachments' => $projectAttachments,
//             'projectVideos' => $projectVideos,
//         ]);
//     }

//     public function updateProject(Request $request, $id)
//     {
//         $request->validate([
//             'title' => 'required|string',
//             'grade' => 'required|string',
//             'project_theme' => 'required|string',
//             'overview' => 'required|string',
//             'skills' => 'required|string',
//             'reflection' => 'required|string',
//             'projectPublish' => 'required|in:0,1',
//             'supporting_files.*' => 'nullable|file|mimes:jpeg,jpg,png,mp4,mov,avi,mkv,pdf'
//         ]);

//         $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
//         $studentId = $student->id;

//         $project = Project::where('id', $id)->where('student_id', $studentId)->firstOrFail();

//         $oldPublishStatus = $project->is_publish;

//         $grade = StudentGrade::where('name', $request->grade)->first();
//         $theme = ProjectTheme::where('project_theme_name', $request->project_theme)->first();

//         if (!$grade || !$theme) {
//             return redirect()->back()->withInput()->with(['error' => 'Invalid grade or theme selected.']);
//         }

//         // Update project fields
//         $project->title = $request->title;
//         $project->student_grade_id = $grade->id;
//         $project->project_theme_id = $theme->id;
//         $project->project_overview = $request->overview;
//         $project->project_skills = $request->skills;
//         $project->project_challenges = $request->reflection;
//         $project->project_reflection = $request->project_reflection;
//         $project->is_publish = $request->projectPublish;
//         $project->save();

//         $projectId = $project->id;

//         // Handle Supporting Files (Images, Videos, PDFs)
//         if ($request->has('supporting_files')) {
//             $supportingFiles = $request->file('supporting_files', []);

//             if (!empty($supportingFiles)) {
//                 foreach ($supportingFiles as $file) {
//                     if (!$file) continue;

//                     $extension = strtolower($file->getClientOriginalExtension());
//                     $name = Str::random(10);

//                     $attachmentType = '';
//                     $uploadPath = '';
//                     $fileName = '';

//                     if (in_array($extension, ['jpeg', 'jpg', 'png'])) {
//                         $attachmentType = 'Image';
//                         $fileName = 'image_' . $name . '.' . $extension;
//                         $uploadPath = 'image/project/';
//                         $tenantPath = 'student/project/image';
//                     } elseif (in_array($extension, ['mp4', 'mov', 'avi', 'mkv'])) {
//                         $attachmentType = 'Video';
//                         $fileName = 'video_' . $name . '.' . $extension;
//                         $uploadPath = 'videos/project/';
//                         $tenantPath = 'student/project/videos';
//                     } elseif ($extension === 'pdf') {
//                         $attachmentType = 'Pdf';
//                         $fileName = 'attachment_' . $name . '.' . $extension;
//                         $uploadPath = 'attachments/project/';
//                         $tenantPath = 'student/project/attachments';
//                     } else {
//                         continue;
//                     }

//                     if (tenant() && tenant()->tenant_id) {
//                         $file->storeAs(tenant()->tenant_id . '/' . $tenantPath, $fileName, 'tenant_uploads');
//                         $uploadPath = tenant()->tenant_id . '/' . $tenantPath . '/';
//                     } else {
//                         $file->move(public_path($uploadPath), $fileName);
//                     }

//                     $projectfiles = new Projectfiles();
//                     $projectfiles->project_id = $projectId;
//                     $projectfiles->student_id = $studentId;
//                     $projectfiles->attachment_type = $attachmentType;
//                     $projectfiles->attachment = $uploadPath . $fileName;
//                     $projectfiles->save();
//                 }
//             }
//         }

//         $pointsAwarded = 0;
//         if ($oldPublishStatus == 0 && $request->projectPublish == 1) {
//             $pointsAwarded = $this->storeProjectRewardPoints($studentId, $projectId, $request->projectPublish);
//         }

//         return redirect()->route('student.list-project')->with(['message' => 'Project Updated Successfully!', 'confetti_visible' => $pointsAwarded == 1 ? 1 : 0]);
//     }

//     private function getFullFilePath($storedPath)
//     {
//         if (tenant() && tenant()->tenant_id) {
//             return public_path('tenants/' . $storedPath);
//         }

//         return public_path($storedPath);
//     }


//     public function downloadProjectFile(Request $request)
//     {
//         try {
//             $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
//             $studentId = $student->id;

//             $fileId = $request->input('file_id');

//             if (!$fileId) {
//                 abort(400, 'File ID is required');
//             }

//             $file = Projectfiles::where('id', $fileId)
//                 ->where('student_id', $studentId)
//                 ->firstOrFail();

//             $filePath = $this->getFullFilePath($file->attachment);

//             if (!file_exists($filePath)) {
//                 Log::error('File not found at path: ' . $filePath . ' | DB Path: ' . $file->attachment);
//                 abort(404, 'File not found');
//             }

//             $fileName = basename($file->attachment);

//             return response()->download($filePath, $fileName);
//         } catch (\Exception $e) {
//             Log::error('Download error: ' . $e->getMessage());
//             return redirect()->back()->with('error', 'Unable to download file: ' . $e->getMessage());
//         }
//     }


//     public function deleteProjectMedia(Request $request)
//     {
//         $mediaId = $request->input('mediaId');
//         $mediaType = $request->input('mediaType');

//         try {
//             $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
//             $studentId = $student->id;

//             // Determine the attachment type based on media type
//             if ($mediaType == 'image') {
//                 $attachmentType = 'Image';
//             } elseif ($mediaType == 'attachment') {
//                 $attachmentType = 'Pdf';
//             } elseif ($mediaType == 'video') {
//                 $attachmentType = 'Video';
//             } else {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Invalid media type'
//                 ]);
//             }

//             $projectFile = Projectfiles::where('id', $mediaId)->where('student_id', $studentId)->where('attachment_type', $attachmentType)->first();

//             if (!$projectFile) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'File not found'
//                 ]);
//             }

//             $filePath = $this->getFullFilePath($projectFile->attachment);
//             if (file_exists($filePath)) {
//             @unlink($filePath);
//             }

//             $projectFile->delete();

//             return response()->json([
//                 'success' => true,
//                 'message' => ucfirst($mediaType) . ' deleted successfully'
//             ]);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Error deleting file: ' . $e->getMessage()
//             ]);
//         }
//     }

//     public function deleteProject(Request $request)
//     {
//         $projectId = $request->input('projectId');

//         try {
//             $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
//             $studentId = $student->id;

//             $project = Project::where('id', $projectId)->where('student_id', $studentId)->first();

//             if (!$project) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Project not found or you do not have permission to delete it.'
//                 ]);
//             }

//         $projectFiles = Projectfiles::where('project_id', $projectId)->where('student_id', $studentId)->get();

//             // Delete all physical files
//             foreach ($projectFiles as $file) {
//                 // Use helper method to get correct path
//                 $filePath = $this->getFullFilePath($file->attachment);
//                 if (file_exists($filePath)) {
//                     @unlink($filePath);
//                 }

//                 $file->delete();
//             }

//             $project->delete();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Project deleted successfully along with all associated files.'
//             ]);
//             } catch (\Exception $e) {
//                 return response()->json([
//                 'success' => false,
//                 'message' => 'Error deleting project: ' . $e->getMessage()
//             ]);
//         }
//     }

//     public function viewProject($id)
//     {
//         $student = Students::where('user_id', Session::get('user_id'))->first();

//         if (!$student) {
//             abort(403, 'Unauthorized access');
//         }

//         $studentId = $student->id;

//         // Fetch project with all relationships
//         $project = Project::with(['theme:id,project_theme_name','projectFiles' => function ($query) {
//             $query->select('id', 'project_id', 'student_id', 'attachment_type', 'attachment');
//         },
//         'feedback:id,project_id,is_publish'])
//         ->where('id', $id)
//         ->where('student_id', $studentId)
//         ->select('id','student_id','title','project_theme_id','project_overview','project_skills','project_challenges','is_publish','updated_at','project_reflection'
//         )->firstOrFail();

//         // Define base path
//         $basePath = 'tenants/';

//         // Normalize function for consistent URLs
//         $normalizePath = function ($path) use ($basePath) {
//             $normalized = Str::startsWith($path, 'tenants/')? $path : $basePath . ltrim($path, '/');
//             return asset($normalized);
//         };

//         // Map and normalize files by type
//         $projectImages = $project->projectFiles->where('attachment_type', 'Image')->map(fn($file) => ['id' => $file->id,'path' => $normalizePath($file->attachment)])->values()->toArray();
//         $projectVideos = $project->projectFiles->where('attachment_type', 'Video')->map(fn($file) => ['id' => $file->id, 'path' => $normalizePath($file->attachment)])->values()->toArray();
//         $projectAttachments = $project->projectFiles->where('attachment_type', 'Pdf')->map(fn($file) => ['id' => $file->id, 'path' => $normalizePath($file->attachment)])->values()->toArray();

//         return view('student.project.project_view', [
//             'project' => $project,
//             'projectImages' => $projectImages,
//             'projectAttachments' => $projectAttachments,
//             'projectVideos' => $projectVideos,
//         ]);
//     }

//     public function getThemesByGrade($gradeName)
//     {
//         $grade = StudentGrade::where('name', $gradeName)->first();

//         if (!$grade) {
//             return response()->json([]);
//         }

//         $themes = $grade->themes()->pluck('project_theme_name');

//         return response()->json($themes);
//     }

//      public function viewFeedback($feedbackId)
// {
//     try {
//         $student = Students::where('user_id', Session::get('user_id'))->firstOrFail();
//         $studentId = $student->id;

//         // Get feedback with trainer information
//         $feedback = ProjectFeedback::with(['project', 'trainer'])
//             ->where('id', $feedbackId)
//             ->where('student_id', $studentId)
//             ->where('is_publish', 1)
//             ->firstOrFail();

//         // Return the partial view for modal
//         return view('student.project.view-feedback', compact('feedback'));

//     } catch (\Exception $e) {
//         Log::error('Student View Feedback Modal Error: ' . $e->getMessage());
//         return response()->json(['error' => 'Feedback not found or not published'], 404);
//     }
// }
//     private function storeProjectRewardPoints($studentId, $projectId, $isPublish)
//     {
//         if ($isPublish == 1) {
//             $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist($studentId, 'project', $projectId);

//             if (!$alreadyRewarded->count()) {
//                 StudentRewardPointsHelper::storeRewardPoints([
//                     'student_id' => $studentId,
//                     'reward_type' => 'project',
//                     'item_id' => $projectId,
//                     'reward_points' => 1,
//                 ]);

//                 return true;
//             }
//         }

//         return false;
//     }

}
