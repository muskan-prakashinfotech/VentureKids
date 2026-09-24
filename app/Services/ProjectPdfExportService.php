<?php

namespace App\Services;

use App\Models\ProjectSection;
use App\Models\StudentProject;
use App\Models\StudentProjectAnswer;
use App\Models\StudentProjectAttachment;
use Illuminate\Support\Str;

class ProjectPdfExportService
{
    public function buildPdfData(StudentProject $studentProject): array
    {
        $studentProject->loadMissing(['student.school', 'student.getAssignedBatch']);

        $sections = ProjectSection::where('status', 1)
            ->orderBy('display_order')
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->get();

        $answers = StudentProjectAnswer::where('student_project_id', $studentProject->id)
            ->pluck('answer_text', 'project_question_id');

        $attachments = StudentProjectAttachment::where('student_project_id', $studentProject->id)
            ->where('file_type', 'videos')
            ->get()
            ->groupBy('project_question_id');

        $allQuestions = $sections->flatMap->questions;
        $titleQuestion = $allQuestions->first(fn ($q) => stripos($q->field_text, 'title') !== false);
        $themeQuestion = $allQuestions->first(fn ($q) => stripos($q->field_text, 'theme') !== false);

        $displayTitle = $titleQuestion ? $answers->get($titleQuestion->id) : null;
        $displayTheme = $themeQuestion ? $answers->get($themeQuestion->id) : null;

        $sectionsData = $sections->map(function ($section) use ($answers, $attachments) {
            return [
                'section_title' => $section->section_title,
                'questions' => $section->questions->map(function ($question) use ($answers, $attachments) {
                    $questionAttachments = $attachments->get($question->id, collect());

                    return [
                        'field_text' => $question->field_text,
                        'answer_text' => ($answers->get($question->id) !== null && trim((string) $answers->get($question->id)) !== '')
                            ? $answers->get($question->id)
                            : '',
                        'has_answer' => ($answers->get($question->id) !== null && trim((string) $answers->get($question->id)) !== ''),
                        'attachments' => $questionAttachments->map(function ($attachment) {
                            return [
                                'file_name' => $attachment->file_name,
                                'file_type' => $attachment->file_type,
                                'url' => $attachment->url,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        return [
            'studentProject' => $studentProject,
            'studentName' => optional($studentProject->student)->name ?: 'Student',
            'studentSchool' => optional(optional($studentProject->student)->school)->school_name ?: 'No School',
            'studentBatch' => optional(optional($studentProject->student)->getAssignedBatch)->batch_name ?: 'No Batch',
            'displayTitle' => $displayTitle ?: 'Untitled Project',
            'displayTheme' => $displayTheme ?: 'No Theme Assigned',
            'lastSubmittedOn' => optional($studentProject->updated_at)->format('F d, Y'),
            'sections' => $sectionsData,
        ];
    }

    public function renderPdfBinary(StudentProject $studentProject): string
    {
        $pdf = \PDF::loadView('pdf.project_download', $this->buildPdfData($studentProject));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    public function buildFileName(StudentProject $studentProject): string
    {
        $studentProject->loadMissing(['student']);
        $title = $this->resolveProjectTitle($studentProject);
        $studentName = $this->sanitizeFilenameSegment(optional($studentProject->student)->name ?: 'Student');
        $projectTitle = $this->buildProjectTitleFilenameSegment($title);
        $date = optional($studentProject->updated_at)->format('Y-m-d') ?: now()->format('Y-m-d');

        return trim($studentName . ' - ' . $projectTitle . ' - ' . $date) . '.pdf';
    }

    public function resolveProjectTitle(StudentProject $studentProject): string
    {
        $sections = ProjectSection::where('status', 1)
            ->orderBy('display_order')
            ->with(['questions' => function ($query) {
                $query->where('status', 1)->orderBy('display_order');
            }])
            ->get();

        $answers = StudentProjectAnswer::where('student_project_id', $studentProject->id)
            ->pluck('answer_text', 'project_question_id');

        $allQuestions = $sections->flatMap->questions;
        $titleQuestion = $allQuestions->first(fn ($q) => stripos($q->field_text, 'title') !== false);

        return $titleQuestion ? (string) ($answers->get($titleQuestion->id) ?: 'Untitled Project') : 'Untitled Project';
    }

    private function sanitizeFilenameSegment(string $value): string
    {
        $value = preg_replace('/[\\\\\\/:"*?<>|]+/', '', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function buildProjectTitleFilenameSegment(string $value): string
    {
        $value = $this->sanitizeFilenameSegment($value);
        $value = preg_replace('/\s+/', '_', $value) ?? $value;
        $value = preg_replace('/_+/', '_', $value) ?? $value;

        return trim($value, '_');
    }
}
