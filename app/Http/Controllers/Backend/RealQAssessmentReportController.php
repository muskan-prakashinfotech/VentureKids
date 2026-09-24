<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSchoolReport;
use App\Models\RealQAssessmentParameter;
use App\Models\RealQAssessmentRubric;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\RealQStudentParameterScore;
use App\Models\School;
use App\Models\StudentGrade;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDF;

class RealQAssessmentReportController extends Controller
{
    public function index()
    {
        $schools = applyCountryScope(
            RealQAssessmentSchoolAssignment::query()
            ->join('schools', 'realq_assessment_school_assignments.school_id', '=', 'schools.id')
            ->leftJoin('student_grade as grades', 'realq_assessment_school_assignments.realq_assessment_assigned_grade_id', '=', 'grades.id')
            ->where('realq_assessment_school_assignments.realq_assessment_assigned', 1)
            ->where('realq_assessment_school_assignments.realq_assessment_enabled', 1)
            ->leftJoin('students as school_students', 'schools.id', '=', 'school_students.school_id')
            ->groupBy('schools.id', 'schools.school_name')
            ->select([
                'schools.id as school_id',
                'schools.school_name as school_name',
                DB::raw('COUNT(DISTINCT school_students.id) as total_students'),
                DB::raw('MIN(realq_assessment_school_assignments.realq_assessment_enabled_from) as enabled_from'),
                DB::raw('MAX(realq_assessment_school_assignments.realq_assessment_enabled_to) as enabled_to'),
                DB::raw("GROUP_CONCAT(DISTINCT grades.name ORDER BY grades.name SEPARATOR ', ') as grade_names"),
            ])
            ->orderBy('schools.school_name'),
            'schools.country_id'
        )->get();

        $reportMap = AssessmentSchoolReport::where('assessment_type', 'realq')
            ->orderByDesc('id')
            ->get()
            ->groupBy('school_id')
            ->map(function ($rows) {
                return $rows->first();
            });

        $attemptedCountMap = DB::table('assessment_student_report')
            ->join('students', 'assessment_student_report.student_id', '=', 'students.id')
            ->select('students.school_id', DB::raw('COUNT(DISTINCT assessment_student_report.student_id) as attempted_students'))
            ->where('assessment_student_report.assessment_type', 'realq')
            ->whereIn('students.school_id', $schools->pluck('school_id')->all())
            ->groupBy('students.school_id')
            ->pluck('attempted_students', 'school_id');

        return view('backend.realq_assessment.reports.index', [
            'schools' => $schools,
            'reportMap' => $reportMap,
            'attemptedCountMap' => $attemptedCountMap,
        ]);
    }

    public function generate(Request $request, int $schoolId, OpenAIService $openAIService)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'School not found.',
            ]);
        }

        if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'School not found.',
            ], 403);
        }

        [$grades, $totalStudentsAll, $reportText, $error] = $this->buildSchoolReportPayload($schoolId, $openAIService);
        if ($error !== '') {
            return response()->json([
                'success' => false,
                'message' => $error,
            ]);
        }

        $reportSections = $this->parseSchoolReportSections((string) $reportText);
        $tenantId = (string) ($school->tenant_id ?? 'common');
        $fileName = Str::random(12) . dechex(time()) . '.pdf';
        $reportPath = trim($tenantId, '/') . '/school/reports/realq_assessment/' . $fileName;
        $absoluteDirectory = public_path('tenants/' . trim($tenantId, '/') . '/school/reports/realq_assessment');
        if (!File::exists($absoluteDirectory)) {
            File::makeDirectory($absoluteDirectory, 0755, true);
        }

        $pdf = PDF::loadView('backend.realq_assessment.reports.report_pdf', [
            'schoolName' => (string) ($school->school_name ?? 'School'),
            'schoolLogoPath' => $this->resolveSchoolLogoPath($school),
            'reportSections' => $reportSections,
            'generatedAt' => now()->toDateTimeString(),
            'totalStudents' => $totalStudentsAll,
            'gradeSummary' => $grades,
            'assessmentFormat' => 'Subjective, scenario-based REALQ questions',
        ]);

        File::put(public_path('tenants/' . $reportPath), $pdf->output());

        $reportRow = AssessmentSchoolReport::create([
            'school_id' => $schoolId,
            'assessment_type' => 'realq',
            'report_path' => $reportPath,
        ]);

        return response()->json([
            'success' => true,
            'school_id' => $schoolId,
            'total_students' => $totalStudentsAll,
            'grades' => $grades,
            'report_text' => $reportText,
            'report_id' => $reportRow->id,
            'download_url' => route('backend.realqassessment.reports.download', $reportRow->id),
        ]);
    }

    public function preview(Request $request, int $schoolId, OpenAIService $openAIService)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return response('School not found.', 404);
        }

        if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
            return response('School not found.', 403);
        }

        [$grades, $totalStudentsAll, $reportText, $error] = $this->buildSchoolReportPayload($schoolId, $openAIService);
        if ($error !== '') {
            return response($error, 400);
        }
        echo '<pre>';
        print_r($reportText);
        
        $reportSections = $this->parseSchoolReportSections((string) $reportText);

        return view('backend.realq_assessment.reports.report_pdf', [
            'schoolName' => (string) ($school->school_name ?? 'School'),
            'schoolLogoPath' => $this->resolveSchoolLogoPath($school),
            'reportSections' => $reportSections,
            'generatedAt' => now()->toDateTimeString(),
            'totalStudents' => $totalStudentsAll,
            'gradeSummary' => $grades,
            'assessmentFormat' => 'Subjective, scenario-based REALQ questions',
        ]);
    }

    private function buildSchoolReportPayload(int $schoolId, OpenAIService $openAIService): array
    {
        $assignments = RealQAssessmentSchoolAssignment::query()
            ->where('school_id', $schoolId)
            ->where('realq_assessment_assigned', 1)
            ->where('realq_assessment_enabled', 1)
            ->orderBy('realq_assessment_assigned_grade_id')
            ->get();

        if ($assignments->isEmpty()) {
            return [[], 0, '', 'No RealQ assignments found for this school.'];
        }

        $gradeIds = $assignments->pluck('realq_assessment_assigned_grade_id')->filter()->unique()->values()->all();
        $gradeNames = StudentGrade::whereIn('id', $gradeIds)->pluck('name', 'id')->all();

        $latestReportSubQuery = DB::table('assessment_student_report')
            ->select('student_id', DB::raw('MAX(id) as latest_report_id'))
            ->where('assessment_type', 'realq')
            ->where('status', 'approved')
            ->groupBy('student_id');

        $grades = [];
        foreach ($assignments as $assignment) {
            $gradeId = (int) $assignment->realq_assessment_assigned_grade_id;
            $paramIds = array_values(array_filter(array_map('intval', explode(',', (string) $assignment->realq_assessment_assigned_parameters_id))));
            if (empty($paramIds)) {
                continue;
            }

            $parameters = RealQAssessmentParameter::whereIn('id', $paramIds)->get()->keyBy('id');
            $rubrics = RealQAssessmentRubric::where('scale_id', $assignment->realq_assessment_assigned_scale_id)
                ->orderBy('score')
                ->get();
            $rubricIds = $rubrics->pluck('id')->all();

            $scoreBaseQuery = RealQStudentParameterScore::query()
                ->joinSub($latestReportSubQuery, 'latest_reports', function ($join) {
                    $join->on('realq_assessment_student_parameter_scores.assessment_report_id', '=', 'latest_reports.latest_report_id')
                        ->on('realq_assessment_student_parameter_scores.student_id', '=', 'latest_reports.student_id');
                })
                ->where('realq_assessment_student_parameter_scores.school_id', $schoolId)
                ->where('realq_assessment_student_parameter_scores.student_grade_id', $gradeId)
                ->whereIn('realq_assessment_student_parameter_scores.parameter_id', $paramIds)
                ->whereIn('realq_assessment_student_parameter_scores.rubric_id', $rubricIds);

            $totalStudents = (clone $scoreBaseQuery)
                ->distinct()
                ->count('realq_assessment_student_parameter_scores.student_id');

            $counts = (clone $scoreBaseQuery)
                ->select(
                    'realq_assessment_student_parameter_scores.parameter_id',
                    'realq_assessment_student_parameter_scores.rubric_id',
                    DB::raw('COUNT(DISTINCT realq_assessment_student_parameter_scores.student_id) as total')
                )
                ->groupBy('parameter_id', 'rubric_id')
                ->get();

            $countMap = [];
            foreach ($counts as $row) {
                $countMap[$row->parameter_id][$row->rubric_id] = (int) $row->total;
            }

            $parameterRows = [];
            foreach ($paramIds as $paramId) {
                if (!isset($parameters[$paramId])) {
                    continue;
                }
                $paramName = (string) ($parameters[$paramId]->name ?? '');
                $levels = [];
                foreach ($rubrics as $rubric) {
                    $rubricId = (int) $rubric->id;
                    $count = (int) ($countMap[$paramId][$rubricId] ?? 0);
                    $percent = $totalStudents > 0 ? (int) round(($count / $totalStudents) * 100) : 0;
                    $levels[] = [
                        'name' => (string) ($rubric->name ?? ''),
                        'count' => $count,
                        'percent' => $percent,
                    ];
                }

                $parameterRows[] = [
                    'name' => $paramName,
                    'levels' => $levels,
                ];
            }

            $grades[] = [
                'grade_name' => $gradeNames[$gradeId] ?? ('Grade ' . $gradeId),
                'total_students' => $totalStudents,
                'parameters' => $parameterRows,
            ];
        }

        $totalStudentsAll = array_sum(array_map(fn ($g) => (int) $g['total_students'], $grades));

        $payload = [
            'total_students' => $totalStudentsAll,
            'grades' => $grades,
        ];

        $prompt = $openAIService->buildSchoolReportPromptTemplate($payload);
        $reportText = (string) $openAIService->generateAssessmentReport($prompt, 2200);
        if ($reportText === '') {
            return [$grades, $totalStudentsAll, '', 'Report generation is temporarily unavailable. Please try again.'];
        }

        return [$grades, $totalStudentsAll, $reportText, ''];
    }

    public function download(int $reportId)
    {
        $report = AssessmentSchoolReport::find($reportId);
        if (!$report) {
            return redirect()
                ->route('backend.realqassessment.reports.index')
                ->with('error', 'Report not found.');
        }
        if (!applyCountryScope(School::query()->whereKey($report->school_id ?? null), 'country_id')->exists()) {
            return redirect()
                ->route('backend.realqassessment.reports.index')
                ->with('error', 'Report not found.');
        }
        $path = public_path('tenants/' . ltrim((string) $report->report_path, '/'));
        if (!File::exists($path)) {
            return redirect()
                ->route('backend.realqassessment.reports.index')
                ->with('error', 'Report file not found.');
        }
        return response()->download($path, basename($report->report_path));
    }

    private function resolveSchoolLogoPath(School $school): string
    {
        $defaultLogo = public_path('asset/images/kids-tm-logo.png');
        $logo = trim((string) ($school->school_logo ?? ''));
        if ($logo !== '') {
            $absolute = public_path($logo);
            if (File::exists($absolute)) {
                return $absolute;
            }
        }
        return $defaultLogo;
    }

    private function parseSchoolReportSections(string $text): array
    {
        $sections = [
            'assessment_overview' => '',
            'school_wide_parameter_snapshot' => '',
            'cross_parameter_patterns' => '',
            'what_this_means' => '',
            'actionable_recommendations' => '',
            'moderation_note' => '',
        ];

        $clean = trim($text);
        if ($clean === '') {
            return $sections;
        }

        $clean = $this->cleanReportText($clean);
        $clean = preg_replace('/^\s*\*\*\s*(\d+)\.\s*([^*]+)\*\*\s*$/m', '$2', $clean);
        $clean = preg_replace('/^\s*(\d+)\.\s*(Assessment Overview|School-Wide Parameter Snapshot|Cross-Parameter Patterns|What This Means for Our School|Actionable Recommendations|Moderation & Editing Note)\s*$/mi', '$2', $clean);

        $labels = [
            'assessment_overview' => 'Assessment Overview',
            'school_wide_parameter_snapshot' => 'School-Wide Parameter Snapshot',
            'cross_parameter_patterns' => 'Cross-Parameter Patterns',
            'what_this_means' => 'What This Means for Our School',
            'actionable_recommendations' => 'Actionable Recommendations',
            'moderation_note' => 'Moderation & Editing Note',
        ];

        $positions = [];
        foreach ($labels as $key => $label) {
            $pos = stripos($clean, $label);
            if ($pos !== false) {
                $positions[$key] = $pos;
            }
        }

        if (empty($positions)) {
            $sections['assessment_overview'] = $clean;
            return $sections;
        }

        $ordered = array_keys($labels);
        foreach ($ordered as $idx => $key) {
            if (!isset($positions[$key])) {
                continue;
            }
            $start = $positions[$key];
            $nextStart = null;
            for ($j = $idx + 1; $j < count($ordered); $j++) {
                $nextKey = $ordered[$j];
                if (isset($positions[$nextKey])) {
                    $nextStart = $positions[$nextKey];
                    break;
                }
            }
            $slice = $nextStart !== null
                ? substr($clean, $start, $nextStart - $start)
                : substr($clean, $start);
            $slice = trim(preg_replace('/^.*' . preg_quote($labels[$key], '/') . '\s*:?/i', '', $slice, 1));
            $sections[$key] = $this->cleanReportText($slice);
        }

        return $sections;
    }

    private function cleanReportText(string $text): string
    {
        $text = preg_replace('/^\s*[-*_]{3,}\s*$/m', '', $text);
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text);
        $text = preg_replace('/__([^_]+)__/', '$1', $text);
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        $text = preg_replace('/^\s*#{1,6}\s*/m', '', $text);
        // Normalize spacing but keep bullets and numbering
        $text = preg_replace('/\r\n?/', "\n", $text);
        $text = preg_replace('/^\s+/', '', $text);
        $text = preg_replace('/[ \t]+$/m', '', $text);
        $text = preg_replace('/\n{2,}/', "\n", $text);
        $text = preg_replace('/^\n+/', '', $text);
        return trim($text);
    }
}
