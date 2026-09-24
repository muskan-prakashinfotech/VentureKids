<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\StudentRewardPointsHelper;
use App\Http\Controllers\Controller;
use App\Models\AssessmentStudentReport;
use App\Models\Grade;
use App\Models\School;
use App\Models\StandardAssessmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StandardAssessmentAttemptExport;

class StandardAssessmentAttemptController extends Controller
{
    public function index()
    {
        $schools = applyCountryScope(
            School::select('id', 'school_name', 'standard_assessment_enabled_from', 'standard_assessment_enabled_to')
            ->where('standard_assessment_assigned', 1)
            ->where('standard_assessment_enabled', 1)
            ->orderBy('school_name'),
            'country_id'
        )->get();

        $availableLevelIds = StandardAssessmentCategory::select('grade_id')
            ->distinct()
            ->pluck('grade_id')
            ->toArray();
        $availableLevelOrders = Grade::whereIn('id', $availableLevelIds)
            ->pluck('assessment_order')
            ->map(function ($value) {
                return $value === null ? -1 : (int) $value;
            })
            ->unique()
            ->values()
            ->toArray();

        $levels = Grade::select('id', 'grade', 'assessment_order')
            ->whereIn('id', $availableLevelIds)
            ->orderByRaw('assessment_order IS NULL, assessment_order DESC, grade ASC')
            ->get();

        return view('backend.standard_assessment.attempts.list', compact('schools', 'levels', 'availableLevelOrders'));
    }

    public function data(Request $request)
    {
        $schoolId = $request->school_id ? (int) $request->school_id : null;
        $status = $request->status ?: 'all';
        $levelId = $request->level_id ? (int) $request->level_id : null;
        $levelOrder = null;
        $availableLevelIds = StandardAssessmentCategory::select('grade_id')
            ->distinct()
            ->pluck('grade_id')
            ->toArray();
        $availableLevelOrders = Grade::whereIn('id', $availableLevelIds)
            ->pluck('assessment_order')
            ->map(function ($value) {
                return $value === null ? -1 : (int) $value;
            })
            ->unique()
            ->values()
            ->toArray();
        if ($levelId) {
            $levelOrder = Grade::where('id', $levelId)->value('assessment_order');
            if ($levelOrder === null) {
                $levelOrder = -1;
            }
        }

        $attemptsSub = DB::table('standard_assessment_student_answers')
            ->select([
                'student_id',
                DB::raw('COUNT(id) as total_answers'),
                DB::raw('SUM(CASE WHEN is_submitted = 1 THEN 1 ELSE 0 END) as submitted_answers'),
            ])
            ->groupBy('student_id');

        $query = DB::table('students as s')
            ->leftJoinSub($attemptsSub, 'a', function ($join) {
                $join->on('s.id', '=', 'a.student_id');
            })
            ->leftJoin('schools as sc', 'sc.id', '=', 's.school_id')
            ->select([
                's.id as student_id',
                's.name as student_name',
                's.school_id as school_id',
                DB::raw('COALESCE(a.total_answers, 0) as total_answers'),
                DB::raw('COALESCE(a.submitted_answers, 0) as submitted_answers'),
                DB::raw('GROUP_CONCAT(DISTINCT g.grade ORDER BY g.assessment_order DESC SEPARATOR \', \') as levels'),
                DB::raw('MAX(COALESCE(g.assessment_order, -1)) as max_level_order'),
            ])
            ->leftJoin('grades as g', function ($join) {
                $join->whereRaw('FIND_IN_SET(g.id, s.grade_id)');
            })
            ->groupBy('s.id', 's.name', 's.school_id', 'a.total_answers', 'a.submitted_answers');

        applyCountryScope($query, 'sc.country_id');

        if ($schoolId) {
            $query->where('s.school_id', $schoolId);
        }

        if ($levelId) {
            $query->whereRaw('FIND_IN_SET(?, s.grade_id)', [$levelId]);
            $query->havingRaw('MAX(COALESCE(g.assessment_order, -1)) = ?', [$levelOrder]);
        } else {
            if (!empty($availableLevelOrders)) {
                $query->havingRaw('MAX(COALESCE(g.assessment_order, -1)) IN (' . implode(',', $availableLevelOrders) . ')');
            } else {
                $query->havingRaw('1 = 0');
            }
        }

        if ($status === 'all') {
            // no extra filter
        } elseif ($status === 'not_started') {
            $query->whereNull('a.student_id');
        } elseif ($status === 'started') {
            $query->whereNotNull('a.student_id')
                ->whereRaw('COALESCE(a.submitted_answers, 0) < COALESCE(a.total_answers, 0)');
        } else {
            $query->whereNotNull('a.student_id')
                ->whereRaw('COALESCE(a.submitted_answers, 0) >= COALESCE(a.total_answers, 0)')
                ->whereRaw('COALESCE(a.total_answers, 0) > 0');
        }

        return datatables()->of($query)
            ->filterColumn('student_name', function ($query, $keyword) {
                $query->where('s.name', 'like', '%' . $keyword . '%');
            })
            ->addColumn('levels', function ($row) {
                return $row->levels ?: '-';
            })
            ->addColumn('status', function ($row) {
                $total = (int) ($row->total_answers ?? 0);
                $submitted = (int) ($row->submitted_answers ?? 0);

                if ($total <= 0) {
                    return 'Not Started';
                }

                if ($submitted < $total) {
                    return 'Started';
                }

                return 'Fully Attempted';
            })
            ->addColumn('attempt_progress', function ($row) {
                $total = (int) ($row->total_answers ?? 0);
                $submitted = (int) ($row->submitted_answers ?? 0);
                return $submitted . ' / ' . $total;
            })
            ->addColumn('action', function ($row) {
                $hasAttempt = ((int) ($row->total_answers ?? 0)) > 0;
                if (!$hasAttempt) {
                    return '<button type="button" class="btn btn-secondary btn-sm" disabled>Reset</button>';
                }

                return '<button type="button" class="btn btn-danger btn-sm reset-attempt" data-student-id="' . $row->student_id . '">Reset</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $studentId = (int) $request->student_id;

        DB::beginTransaction();
        try {
            $reports = AssessmentStudentReport::where('student_id', $studentId)
                ->where('assessment_type', 'standard')
                ->get();

            foreach ($reports as $report) {
                $relativePath = ltrim((string) $report->report_path, '/');
                $fullPath = public_path('tenants/' . $relativePath);
                if ($relativePath !== '' && File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }

            DB::table('standard_assessment_student_answers')
                ->where('student_id', $studentId)
                ->delete();

            AssessmentStudentReport::where('student_id', $studentId)
                ->where('assessment_type', 'standard')
                ->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset attempt.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $schoolId = $request->get('school_id');
        $status = $request->get('status', 'all');
        $levelId = $request->get('level_id');

        $fileName = 'standard_assessment_status_report.xlsx';
        if ($schoolId) {
            $school = School::select('school_name')->find((int) $schoolId);
            if ($school && $school->school_name) {
                $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $school->school_name);
                $safeName = trim($safeName, '_');
                if ($safeName !== '') {
                    $fileName = $safeName . '-standard_assessment_status_report.xlsx';
                }
            }
        }

        return Excel::download(
            new StandardAssessmentAttemptExport($schoolId, $status, $levelId),
            $fileName
        );
    }

    public function assignRewardPoints()
    {
        $reports = AssessmentStudentReport::select('id', 'student_id')
            ->where('assessment_type', 'standard')
            ->orderBy('id')
            ->get();

        $processed = 0;
        $created = 0;
        $skipped = 0;

        foreach ($reports as $reportRow) {
            $processed++;

            $existingReward = StudentRewardPointsHelper::checkRewardTypeExist(
                $reportRow->student_id,
                'pre_assessment_score',
                $reportRow->id
            );

            if ($existingReward->count()) {
                $skipped++;
                continue;
            }

            StudentRewardPointsHelper::storeRewardPoints([
                'student_id' => $reportRow->student_id,
                'reward_type' => 'pre_assessment_score',
                'item_id' => $reportRow->id,
                'item_type' => 'standard',
                'reward_points' => 5,
            ]);

            $created++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Standard assessment reward points processed successfully.',
            'processed_reports' => $processed,
            'created_rewards' => $created,
            'skipped_existing_rewards' => $skipped,
        ]);
    }
}
