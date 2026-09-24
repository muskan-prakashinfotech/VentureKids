<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AssessmentStudentReport;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealQAccuracyDashboardController extends Controller
{
    public function index(Request $request)
    {
        $schools        = School::orderBy('school_name')->get(['id', 'school_name']);
        $filterSchoolId = $request->input('school_id');

        return view('backend.realq_assessment.accuracy_dashboard', [
            'schools'        => $schools,
            'filterSchoolId' => $filterSchoolId,
        ]);
    }

    public function data(Request $request)
    {
        if (!$request->filled('school_id')) {
            return $this->stripDebugPayload(
                $this->withoutDeprecationNotices(fn () => datatables()->of(AssessmentStudentReport::query()->whereRaw('1 = 0'))
                    ->addIndexColumn()
                    ->addColumn('accuracy_score', fn () => '—')
                    ->with([
                        'overallAccuracy'     => null,
                        'reportsReviewed'     => 0,
                        'reportsNeedingEdits' => 0,
                    ])
                    ->make(true))
            );
        }

        $scores = DB::table('realq_assessment_student_parameter_scores')
            ->select('assessment_report_id')
            ->selectRaw('SUM(CASE WHEN rubric_id = generate_time_rubric_id THEN 1 ELSE 0 END) as matched_count')
            ->selectRaw('COUNT(*) as total_count')
            ->whereNotNull('generate_time_rubric_id')
            ->groupBy('assessment_report_id');

        $baseQuery = AssessmentStudentReport::query()
            ->join('students', 'assessment_student_report.student_id', '=', 'students.id')
            ->leftJoin('student_grade', 'students.student_grade_id', '=', 'student_grade.id')
            ->leftJoinSub($scores, 'scores', function ($join) {
                $join->on('scores.assessment_report_id', '=', 'assessment_student_report.id');
            })
            ->where('assessment_student_report.assessment_type', 'realq')
            ->whereIn('assessment_student_report.status', ['generated', 'approved'])
            ->where('students.school_id', $request->input('school_id'))
            ->select([
                'assessment_student_report.id',
                'assessment_student_report.status',
                'assessment_student_report.updated_at',
                'students.name as student_name',
                'student_grade.name as grade_name',
                'scores.matched_count',
                'scores.total_count',
            ]);

        [$overallAccuracy, $reportsReviewed, $reportsNeedingEdits] = $this->computeSummaryStats(clone $baseQuery);

        $query = $baseQuery;

        return $this->stripDebugPayload(
            $this->withoutDeprecationNotices(fn () => datatables()->of($query)
                ->addIndexColumn()
                ->filterColumn('student_name', function ($query, $keyword) {
                    $query->where('students.name', 'like', "%{$keyword}%");
                })
                ->filterColumn('grade_name', function ($query, $keyword) {
                    $query->where('student_grade.name', 'like', "%{$keyword}%");
                })
                ->orderColumn('student_name', function ($query, $direction) {
                    $query->orderBy('students.name', $direction);
                })
                ->orderColumn('grade_name', function ($query, $direction) {
                    $query->orderBy('student_grade.name', $direction);
                })
                ->orderColumn('accuracy_score', function ($query, $direction) {
                    $query->orderByRaw("(scores.matched_count / NULLIF(scores.total_count, 0)) {$direction}");
                })
                ->addColumn('accuracy_score', function ($row) {
                    if (empty($row->total_count)) {
                        return '—';
                    }

                    return round(($row->matched_count / $row->total_count) * 100) . '%';
                })
                ->editColumn('status', function ($row) {
                    return ucfirst((string) $row->status);
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('d M Y') : '—';
                })
                ->with([
                    'overallAccuracy'     => $overallAccuracy,
                    'reportsReviewed'     => $reportsReviewed,
                    'reportsNeedingEdits' => $reportsNeedingEdits,
                ])
                ->rawColumns([])
                ->make(true))
        );
    }

    private function withoutDeprecationNotices(\Closure $callback)
    {
        $previousLevel = error_reporting();
        error_reporting($previousLevel & ~E_DEPRECATED);

        try {
            return $callback();
        } finally {
            error_reporting($previousLevel);
        }
    }

    /**
     * Summary card stats — computed over the full filtered set (not just the current
     * DataTable page). Same accuracy formula as the per-row column: % of parameters
     * where rubric_id still matches generate_time_rubric_id.
     *
     * @return array{0: ?int, 1: int, 2: int} [overallAccuracy, reportsReviewed, reportsNeedingEdits]
     */
    private function computeSummaryStats($query): array
    {
        $rows            = $query->get();
        $reportsReviewed = $rows->count();
        $scoredRows      = $rows->filter(fn ($row) => !empty($row->total_count));

        $overallAccuracy = $scoredRows->isNotEmpty()
            ? (int) round($scoredRows->avg(fn ($row) => ($row->matched_count / $row->total_count) * 100))
            : null;

        $reportsNeedingEdits = $scoredRows->filter(fn ($row) => (int) $row->matched_count < (int) $row->total_count)->count();

        return [$overallAccuracy, $reportsReviewed, $reportsNeedingEdits];
    }

    private function stripDebugPayload(\Illuminate\Http\JsonResponse $response): \Illuminate\Http\JsonResponse
    {
        $data = $response->getData(true);
        unset($data['queries'], $data['input']);

        return response()->json($data);
    }
}
