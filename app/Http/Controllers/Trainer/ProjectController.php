<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDetails;
use App\Models\Projectfiles;
use App\Models\Students;
use App\Models\School;
use App\Models\SchoolBatch;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Helpers\StudentRewardPointsHelper;
use App\Models\TrainerAllocationNew;
use Illuminate\Support\Carbon;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Log;
use App\Models\ProjectFeedback;
use App\Models\StudentProject;
use App\Models\StudentProjectAnswer;
use App\Models\StudentProjectAttachment;
use App\Models\StudentProjectFeedback;
use App\Models\ProjectSection;
use App\Services\ProjectPdfExportService;
use App\Helpers\SimpleZipBuilder;


class ProjectController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function index(Request $request)
    {
        $trainer_id = Session::get('trainer_id');
        $allocated_school_list = TrainerAllocationNew::with(['getSchool', 'getBatch'])->where('trainer_id', $trainer_id)->get();
        $projects = collect();
        $students = collect();
        $batches = collect();

        if ($allocated_school_list->count() && $request->school_id) {
            $allocated_school_batch_list = $allocated_school_list->pluck('school_batch_id')->filter()->unique()->values();

            $batches = SchoolBatch::whereIn('id', $allocated_school_batch_list)
                ->when($request->school_id, fn ($q) => $q->where('school_id', $request->school_id))
                ->get();

            $student_options = Students::select('id', 'name', 'school_id', 'school_batch_id')
                ->whereIn('school_batch_id', $allocated_school_batch_list)
                ->when($request->school_id, fn ($q) => $q->where('school_id', $request->school_id))
                ->when($request->batch_id, fn ($q) => $q->where('school_batch_id', $request->batch_id))
                ->get();

            $students = $request->school_id ? $student_options : collect();

            $scoped_student_ids = Students::select('id')
                ->whereIn('school_batch_id', $allocated_school_batch_list)
                ->when($request->school_id, fn ($q) => $q->where('school_id', $request->school_id))
                ->when($request->batch_id, fn ($q) => $q->where('school_batch_id', $request->batch_id))
                ->pluck('id');

            $projects = StudentProject::with(['student.school', 'student.getAssignedBatch', 'feedbacks.trainer'])
                ->whereIn('student_id', $scoped_student_ids)
                ->where('is_submitted', 1)
                ->when($request->student_id, fn ($q) => $q->where('student_id', $request->student_id))
                ->latest('updated_at')
                ->get();

            if ($request->status) {
                $projects = $projects->filter(function ($project) use ($request) {
                    $latestFeedback = $this->latestSubmissionFeedback($project);

                    return match ($request->status) {
                        'submitted' => $latestFeedback === null,
                        'feedback_draft' => $latestFeedback && (int) $latestFeedback->is_publish === 0,
                        'feedback_published', 'feedback_shared' => $latestFeedback && (int) $latestFeedback->is_publish === 1,
                        'feedback_approved' => $latestFeedback && (int) $latestFeedback->is_publish === 2,
                        'feedback_resubmitted' => $latestFeedback && (int) $latestFeedback->is_publish === 3,
                        default => true,
                    };
                })->values();
            }

            $titleAnswers = StudentProjectAnswer::whereIn('student_project_id', $projects->pluck('id'))
                ->join('project_questions', 'project_questions.id', '=', 'student_project_answers.project_question_id')
                ->where('project_questions.field_text', 'like', '%title%')
                ->pluck('student_project_answers.answer_text', 'student_project_answers.student_project_id');

            $projects->each(function ($project) use ($titleAnswers) {
                $project->display_title = $titleAnswers->get($project->id);
                $project->display_status_label = $this->projectStatusLabel($project);
                $project->display_status_class = $this->projectStatusClass($project);
            });
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('trainer.project.partials.project_grid', [
                    'projects' => $projects,
                    'showSchoolPrompt' => !$request->school_id,
                ])->render(),
                'batches' => $batches->map(fn ($batch) => ['id' => $batch->id, 'batch_name' => $batch->batch_name])->values(),
                'students' => $students->map(fn ($student) => ['id' => $student->id, 'name' => $student->name])->values(),
            ]);
        }

        return view('trainer.project.project_list', [
            'projects' => $projects,
            'allocated_school_list' => $allocated_school_list,
            'batches' => $batches,
            'students' => $students,
            'school_id' => $request->school_id,
            'batch_id' => $request->batch_id,
            'student_id' => $request->student_id,
            'showSchoolPrompt' => !$request->school_id,
        ]);
    }

    public function viewSubmission($id)
    {
        $studentProject = StudentProject::with(['student.school', 'student.getAssignedBatch', 'feedbacks.trainer'])
            ->whereIn('student_id', $this->allocatedStudentIds())
            ->where('is_submitted', 1)
            ->findOrFail($id);

        $sections = ProjectSection::where('status', 1)
            ->orderBy('display_order')
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->get();

        $answers = StudentProjectAnswer::where('student_project_id', $studentProject->id)
            ->pluck('answer_text', 'project_question_id');

        $attachments = StudentProjectAttachment::where('student_project_id', $studentProject->id)
            ->get()
            ->groupBy('project_question_id');

        $feedbackHistory = StudentProjectFeedback::with('trainer')
            ->where('student_project_id', $studentProject->id)
            ->orderByDesc('id')
            ->get();

        $latestFeedback = $feedbackHistory->first();

        $allQuestions = $sections->flatMap->questions;
        $titleQuestion = $allQuestions->first(fn ($q) => stripos($q->field_text, 'title') !== false);
        $themeQuestion = $allQuestions->first(fn ($q) => stripos($q->field_text, 'theme') !== false);
        $displayTitle = $titleQuestion ? $answers->get($titleQuestion->id) : null;
        $displayTheme = $themeQuestion ? $answers->get($themeQuestion->id) : null;

        return view('trainer.project.view_submission', [
            'studentProject' => $studentProject,
            'sections' => $sections,
            'answers' => $answers,
            'attachments' => $attachments,
            'displayTitle' => $displayTitle,
            'displayTheme' => $displayTheme,
            'latestFeedback' => $latestFeedback,
            'feedbackHistory' => $feedbackHistory,
        ]);
    }

    public function downloadSelectedProjects(Request $request, ProjectPdfExportService $projectPdfExportService)
    {
        $data = $request->validate([
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'integer',
        ]);

        $projectIds = collect($data['project_ids'])->unique()->values();

        $projects = StudentProject::with(['student.school', 'student.getAssignedBatch'])
            ->whereIn('id', $projectIds)
            ->whereIn('student_id', $this->allocatedStudentIds())
            ->where('is_submitted', 1)
            ->get()
            ->sortBy(fn ($project) => $projectIds->search($project->id))
            ->values();

        if ($projects->isEmpty()) {
            return redirect()->back()->with('error', 'No valid projects were selected for download.');
        }

        $pdfEntries = [];

        foreach ($projects as $project) {
            $pdfEntries[] = [
                'filename' => $projectPdfExportService->buildFileName($project),
                'binary' => $projectPdfExportService->renderPdfBinary($project),
            ];
        }

        if ($pdfEntries === []) {
            return redirect()->back()->with('error', 'Unable to generate project PDF(s).');
        }

        if (count($pdfEntries) === 1) {
            return response($pdfEntries[0]['binary'], 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $pdfEntries[0]['filename'] . '"',
            ]);
        }

        $zipBinary = SimpleZipBuilder::build(SimpleZipBuilder::dedupeNames($pdfEntries));
        $zipName = 'project-downloads-' . now()->format('Y-m-d') . '.zip';

        return response($zipBinary, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipName . '"',
        ]);
    }

    private function allocatedStudentIds()
    {
        $trainer_id = Session::get('trainer_id');
        $allocated_school_batch_list = TrainerAllocationNew::where('trainer_id', $trainer_id)
            ->pluck('school_batch_id')
            ->filter();

        return Students::whereIn('school_batch_id', $allocated_school_batch_list)->pluck('id');
    }

    private function buildSubmissionResponsesText(StudentProject $studentProject)
    {
        $sections = ProjectSection::where('status', 1)
            ->orderBy('display_order')
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->get();

        $answers = StudentProjectAnswer::where('student_project_id', $studentProject->id)
            ->pluck('answer_text', 'project_question_id');

        $lines = [];

        foreach ($sections as $section) {
            $lines[] = $section->section_title . ':';

            foreach ($section->questions as $question) {
                $answerText = $answers->get($question->id);
                $answerText = ($answerText !== null && trim((string) $answerText) !== '') ? $answerText : '(No answer provided)';
                $lines[] = '- ' . $question->field_text . ': ' . $answerText;
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private function projectStatusLabel(StudentProject $project): string
    {
        $latestFeedback = $this->latestSubmissionFeedback($project);

        if (!$latestFeedback) {
            return 'Submitted';
        }

        return match ((int) $latestFeedback->is_publish) {
            0 => 'Draft',
            1 => 'Published',
            2 => 'Approved',
            3 => 'Re-submitted',
            default => 'Draft',
        };
    }

    private function projectStatusClass(StudentProject $project): string
    {
        return match ($this->projectStatusLabel($project)) {
            'Draft' => 'trainer-project-status-draft',
            'Published' => 'trainer-project-status-published',
            'Approved' => 'trainer-project-status-approved',
            'Re-submitted' => 'trainer-project-status-resubmitted',
            default => 'trainer-project-status-submitted',
        };
    }

    private function latestSubmissionFeedback(StudentProject $studentProject): ?StudentProjectFeedback
    {
        if ($studentProject->relationLoaded('feedbacks')) {
            return $studentProject->feedbacks
                ->sortByDesc('id')
                ->first();
        }

        return StudentProjectFeedback::with('trainer')
            ->where('student_project_id', $studentProject->id)
            ->orderByDesc('id')
            ->first();
    }

    public function generateSubmissionFeedback(Request $request, $id)
    {
        $studentProject = StudentProject::with(['student.user', 'student.getAssignedGrade'])
            ->whereIn('student_id', $this->allocatedStudentIds())
            ->where('is_submitted', 1)
            ->find($id);

        if (!$studentProject) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submitted project not found or you do not have access to it.',
                ], 404);
            }

            return redirect()->back()->with('error', 'Submitted project not found or you do not have access to it.');
        }
        
        $existingFeedback = StudentProjectFeedback::where('student_project_id', $studentProject->id)
            ->orderByDesc('id')
            ->first();

        if ($existingFeedback && (int) $existingFeedback->is_publish !== 3) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'feedback_id' => $existingFeedback->id,
                    'message' => 'Feedback already exists',
                ]);
            }

            return redirect()->route('trainer.edit-submission-feedback', $existingFeedback->id);
        }

        $student = $studentProject->student;
        $studentName = optional($student)->name ?: 'the student';
        $studentMeta = [
            // 'student_grade_id' => optional($student)->student_grade_id,
            'student_grade_name' => optional(optional($student)->getAssignedGrade)->name,
            'date_of_birth' => optional(optional($student)->user)->date_of_birth
                ? Carbon::parse(optional($student->user)->date_of_birth)->toDateString()
                : null,
        ];
        $submissionText = $this->buildSubmissionResponsesText($studentProject);
        $messages = $this->openAIService->buildTeacherFeedbackMessages($studentName, $studentMeta, $submissionText);
        
        try {
            $result = $this->openAIService->generateTeacherFeedback($messages);
        } catch (\Exception $e) {
            Log::error('Teacher Feedback Generation Error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate feedback',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to generate feedback. Please try again.');
        }

        $feedback = StudentProjectFeedback::create([
            'student_project_id' => $studentProject->id,
            'trainer_id' => Session::get('trainer_id'),
            'public_note' => $result['public_note'],
            'private_suggestions' => $result['private_suggestions'],
            'smart_score' => $result['smart_score'],
            'is_publish' => 0,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'feedback_id' => $feedback->id,
                'message' => 'Feedback generated successfully',
            ]);
        }

        return redirect()->route('trainer.edit-submission-feedback', $feedback->id)->with('success', 'Feedback generated successfully');
    }

    public function editSubmissionFeedback($feedbackId)
    {
        $feedback = StudentProjectFeedback::with('studentProject.student')->findOrFail($feedbackId);

        if ($feedback->trainer_id != Session::get('trainer_id')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        return view('trainer.project.edit-submission-feedback', compact('feedback'));
    }

    public function updateSubmissionFeedback(Request $request, $feedbackId)
    {
        $feedback = StudentProjectFeedback::with('studentProject')->findOrFail($feedbackId);

        if ($feedback->trainer_id != Session::get('trainer_id')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'public_note' => 'required|string',
            'private_suggestions' => 'required|string',
            'smart_score' => 'nullable|integer|between:1,12',
            'is_publish' => 'required|integer|in:0,1,2',
        ]);

        $feedback->update([
            'public_note' => $request->public_note,
            'private_suggestions' => $request->private_suggestions,
            'smart_score' => $request->smart_score,
            'is_publish' => $request->is_publish,
        ]);

        if (in_array((int) $request->is_publish, [2], true) && $feedback->studentProject) {
            $feedback->studentProject->update(['status' => 5]);

            $studentId = $feedback->studentProject->student_id;
            $projectId = $feedback->student_project_id;
            $totalScore = (int) ($feedback->smart_score ?? 0);
            
            // award reward points only if not already awarded for this project
            $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist(
                $studentId,
                'project_ai_score_point',
                $projectId
            );
            if (!$alreadyRewarded->count()) {
                StudentRewardPointsHelper::storeRewardPoints([
                    'student_id' => $studentId,
                    'reward_type' => 'project_ai_score_point',
                    'item_id' => $projectId,
                    'reward_points' => $totalScore,
                ]);
            }   
        }

        return redirect()->route('trainer.view-submission', $feedback->student_project_id)
            ->with('success', $request->is_publish == 0
                ? 'Feedback saved as draft successfully!'
                : ($request->is_publish == 1
                    ? 'Feedback published successfully!'
                    : 'Feedback published and project approved successfully!'));
    }

    public function viewSubmissionFeedback($feedbackId)
    {
        $feedback = StudentProjectFeedback::with('studentProject.student')->findOrFail($feedbackId);

        if ($feedback->trainer_id != Session::get('trainer_id')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        return view('trainer.project.view-submission-feedback', compact('feedback'));
    }

    public function viewProject($id)
    {
        $project = Project::with(['projectFiles', 'student:id,name', 'feedback'])->findOrFail($id);
        $basePath = 'tenants/';

        // Helper function to normalize file URLs
        $normalizePath = function ($path) use ($basePath) {
            // Ensure consistent slashes and prepend tenants/ if not already included
            $normalized = Str::startsWith($path, 'tenants/') ? $path : $basePath . ltrim($path, '/');
            return asset($normalized);
        };

        // Separate and normalize file types
        $images = $project->projectFiles->where('attachment_type', 'Image') ->pluck('attachment')->map(fn($path) => $normalizePath($path))->toArray();
        $videos = $project->projectFiles->where('attachment_type', 'Video') ->pluck('attachment') ->map(fn($path) => $normalizePath($path)) ->toArray();
        $pdfs = $project->projectFiles->where('attachment_type', 'Pdf')->pluck('attachment')->map(fn($path) => $normalizePath($path))->toArray();

        return view('trainer.project.project_view', [
            'project' => $project,
            'images'  => $images,
            'videos'  => $videos,
            'pdfs'    => $pdfs,
        ]);
    }

    /**
     * Generate AI feedback for a project
     */
    public function generateFeedback(Request $request, $projectId)
    {
        try {
            $trainer_id = Session::get('trainer_id');
            $project = Project::with('student')->find($projectId);

            if (!$project) {
                Log::warning('Project feedback generation requested for missing project', [
                    'project_id' => $projectId,
                    'trainer_id' => $trainer_id,
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Project not found. Please reopen the project and try again.',
                    ], 404);
                }

                return redirect()->back()->with('error', 'Project not found. Please reopen the project and try again.');
            }

            // Check if feedback already exists
            $existingFeedback = ProjectFeedback::where('project_id', $projectId)->first();
    
            if ($existingFeedback) {
                // Return JSON with feedback_id for AJAX request
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'feedback_id' => $existingFeedback->id,
                        'message' => 'Feedback already exists'
                    ]);
                }
                return redirect()->route('trainer.edit-feedback', $existingFeedback->id)->with('info', 'Feedback already exists for this project. You can edit it below.');
            }

            $projectData = [
                'title' => $project->title,
                'project_overview' => $project->project_overview ?? 'No overview provided',
                'project_skills' => $project->project_skills ?? 'No skills description provided',
                'project_challenges' => $project->project_challenges ?? 'No challenges described',
                'project_reflection' => $project->project_reflection ?? 'No reflection described',
            ];

            // Generate feedback using OpenAI
            $feedbackData = $this->openAIService->generateProjectFeedback($projectData);

            if (!$feedbackData) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate feedback'
                    ], 500);
                }
                return redirect()->back()->with('error', 'Failed to generate feedback. Please try again.');
            }

            // Prepare spider graph data
            $spiderGraphData = [
                'labels' => ['Curious Mindset', 'Resilient Mindset', 'Open Mindset', 'Positive Mindset', 'Creative Mindset', 'Empathetic Mindset', 'Observant Mindset', 'Abundance Mindset', 'Growth Mindset', 'Entrepreneurial Mindset'],
                'values' => array_values($feedbackData['mindset_scores'])
            ];

            // Create feedback record
            $feedback = ProjectFeedback::create([
                'project_id' => $projectId,
                'trainer_id' => $trainer_id,
                'student_id' => $project->student_id,
                'knowledge_score' => $feedbackData['knowledge_score'],
                'knowledge_feedback' => $feedbackData['knowledge_feedback'],
                'skills_score' => $feedbackData['skills_score'],
                'skills_feedback' => $feedbackData['skills_feedback'],
                'mindset_score' => $feedbackData['mindset_score'],
                'curious_score' => $feedbackData['mindset_scores']['curious'],
                'resilient_score' => $feedbackData['mindset_scores']['resilient'],
                'open_score' => $feedbackData['mindset_scores']['open'],
                'positive_score' => $feedbackData['mindset_scores']['positive'],
                'creative_score' => $feedbackData['mindset_scores']['creative'],
                'empathetic_score' => $feedbackData['mindset_scores']['empathetic'],
                'observant_score' => $feedbackData['mindset_scores']['observant'],
                'abundance_score' => $feedbackData['mindset_scores']['abundance'],
                'growth_score' => $feedbackData['mindset_scores']['growth'],
                'entrepreneurial_score' => $feedbackData['mindset_scores']['entrepreneurial'],
                'mindset_feedback' => $feedbackData['mindset_feedback'],
                'spider_graph_data' => $spiderGraphData,
                'is_publish' => 0, // Default to draft
            ]);

            // Update project status to 'Feedback Drafted'
            Project::where('id', $projectId)->update(['project_status' => 2]);

            // Return JSON for AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'feedback_id' => $feedback->id,
                    'message' => 'Feedback generated successfully'
                ]);
            }

            return redirect()->route('trainer.edit-feedback', $feedback->id)->with('success', 'Feedback generated successfully');

        } catch (\Exception $e) {
            Log::error('Feedback Generation Error: ' . $e->getMessage());
        
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while generating feedback'
                ], 500);
            }
        
            return redirect()->back()->with('error', 'An error occurred while generating feedback.');
        }
    }
    public function editFeedback($feedbackId)
    {
        try {
            $trainer_id = Session::get('trainer_id');
            $feedback = ProjectFeedback::with(['project.student'])->findOrFail($feedbackId);
        
            // Check if this trainer owns this feedback
            if ($feedback->trainer_id !== $trainer_id) {
                return redirect()->back()->with('error', 'Unauthorized access.');
            }
        
            return view('trainer.project.edit-feedback', compact('feedback'));
        
            } catch (\Exception $e) {
                Log::error('Edit Feedback Error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Feedback not found.');
            }
    }

    public function updateFeedback(Request $request, $feedbackId)
    {
        try {
            $trainer_id = Session::get('trainer_id');
            $feedback = ProjectFeedback::with('project')->findOrFail($feedbackId);
        
            // Check if this trainer owns this feedback
            if ($feedback->trainer_id !== $trainer_id) {
                return redirect()->back()->with('error', 'Unauthorized access.');
            }
        
            $request->validate([
                'knowledge_score' => 'required|integer|min:1|max:4',
                'knowledge_feedback' => 'required|string',
                'skills_score' => 'required|integer|min:1|max:4',
                'skills_feedback' => 'required|string',
                'mindset_score' => 'required|integer|min:1|max:4',
                'mindset_feedback' => 'required|string',
                'teacher_notes' => 'nullable|string',
                'is_publish' => 'required|integer|in:0,1,2',
            ]);
        
            // Update feedback (keep existing spider graph data and scores unchanged)
            $feedback->update([
                'knowledge_score' => $request->knowledge_score,
                'knowledge_feedback' => $request->knowledge_feedback,
                'skills_score' => $request->skills_score,
                'skills_feedback' => $request->skills_feedback,               
                'mindset_score' => $request->mindset_score,
                'mindset_feedback' => $request->mindset_feedback,
                'teacher_notes' => $request->teacher_notes,
                'is_publish' => $request->is_publish,
            ]);
        
            // Update project status based on is_publish (using direct assignment)
            $projectStatus = in_array((int) $request->is_publish, [1, 2], true) ? 1 : 2;
            $feedback->project->project_status = $projectStatus;
            $feedback->project->save();

            // Award reward points if feedback is published
            if (in_array((int) $request->is_publish, [1, 2], true)) {
                $studentId = $feedback->project->student_id;
                $projectId = $feedback->project_id;
            
                // Calculate total score (sum of knowledge, skills, and mindset scores)
                $totalScore = $request->knowledge_score + $request->skills_score + $request->mindset_score;
            
                // Check if reward already exists for this project
                $alreadyRewarded = StudentRewardPointsHelper::checkRewardTypeExist($studentId,'project_ai_score_point',$projectId);

                if (!$alreadyRewarded->count()) {
                    StudentRewardPointsHelper::storeRewardPoints([
                        'student_id' => $studentId,
                        'reward_type' => 'project_ai_score_point',
                        'item_id' => $projectId,
                        'reward_points' => $totalScore,
                    ]);
                }
            }

            return redirect()->route('trainer.view-project', $feedback->project_id)->with('success', $request->is_publish == 0 ? 'Feedback saved as draft successfully!' : 'Feedback published successfully!');       
        } catch (\Exception $e) {
            Log::error('Update Feedback Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating feedback.');
        }
    }

    public function viewFeedback($feedbackId)
    {
        $feedback = ProjectFeedback::with('project.student')->findOrFail($feedbackId);
        // Make sure only published feedback can be viewed
        if ((int) $feedback->is_publish === 0) {
            return redirect()->back()->with('error', 'This feedback is not yet published.');
        }

        return view('trainer.project.view-feedback', compact('feedback'));
    }
}
