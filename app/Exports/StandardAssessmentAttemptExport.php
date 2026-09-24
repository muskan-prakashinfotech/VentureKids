<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StandardAssessmentAttemptExport implements FromArray, WithHeadings
{
    protected $schoolId;
    protected $status;
    protected $levelId;
    protected $levelOrder;
    protected $availableLevelOrders;

    public function __construct($schoolId = null, $status = 'all', $levelId = null)
    {
        $this->schoolId = $schoolId ? (int) $schoolId : null;
        $this->status = $status ?: 'all';
        $this->levelId = $levelId ? (int) $levelId : null;
        $this->levelOrder = null;
        $this->availableLevelOrders = [];
        if ($this->levelId) {
            $this->levelOrder = DB::table('grades')->where('id', $this->levelId)->value('assessment_order');
            if ($this->levelOrder === null) {
                $this->levelOrder = -1;
            }
        }

        $availableLevelIds = DB::table('standard_assessment_categories')
            ->select('grade_id')
            ->distinct()
            ->pluck('grade_id')
            ->toArray();
        $this->availableLevelOrders = DB::table('grades')
            ->whereIn('id', $availableLevelIds)
            ->pluck('assessment_order')
            ->map(function ($value) {
                return $value === null ? -1 : (int) $value;
            })
            ->unique()
            ->values()
            ->toArray();
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Level',
            'Attempted (Completed/Total)',
            'Status',
        ];
    }

    public function array(): array
    {
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
                's.name as student_name',
                DB::raw('COALESCE(a.total_answers, 0) as total_answers'),
                DB::raw('COALESCE(a.submitted_answers, 0) as submitted_answers'),
                DB::raw('a.student_id as has_attempt'),
                DB::raw('GROUP_CONCAT(DISTINCT g.grade ORDER BY g.assessment_order DESC SEPARATOR \', \') as levels'),
                DB::raw('MAX(COALESCE(g.assessment_order, -1)) as max_level_order'),
            ])
            ->leftJoin('grades as g', function ($join) {
                $join->whereRaw('FIND_IN_SET(g.id, s.grade_id)');
            });

        applyCountryScope($query, 'sc.country_id');

        if ($this->schoolId) {
            $query->where('s.school_id', $this->schoolId);
        }

        if ($this->levelId) {
            $query->whereRaw('FIND_IN_SET(?, s.grade_id)', [$this->levelId]);
            $query->havingRaw('MAX(COALESCE(g.assessment_order, -1)) = ?', [$this->levelOrder]);
        } else {
            if (!empty($this->availableLevelOrders)) {
                $query->havingRaw('MAX(COALESCE(g.assessment_order, -1)) IN (' . implode(',', $this->availableLevelOrders) . ')');
            } else {
                $query->havingRaw('1 = 0');
            }
        }

        if ($this->status === 'all') {
            // no extra filter
        } elseif ($this->status === 'not_started') {
            $query->whereNull('a.student_id');
        } elseif ($this->status === 'started') {
            $query->whereNotNull('a.student_id')
                ->whereRaw('COALESCE(a.submitted_answers, 0) < COALESCE(a.total_answers, 0)');
        } else {
            $query->whereNotNull('a.student_id')
                ->whereRaw('COALESCE(a.submitted_answers, 0) >= COALESCE(a.total_answers, 0)')
                ->whereRaw('COALESCE(a.total_answers, 0) > 0');
        }

        $rows = $query
            ->groupBy('s.id', 's.name', 'a.total_answers', 'a.submitted_answers', 'a.student_id')
            ->orderBy('s.name')
            ->get();

        if ($rows->isEmpty()) {
            return [['No data found', '', '', '']];
        }

        return $rows->map(function ($row) {
            $total = (int) ($row->total_answers ?? 0);
            $submitted = (int) ($row->submitted_answers ?? 0);

            $status = 'Not Started';
            if ($total > 0 && $submitted < $total) {
                $status = 'Started';
            } elseif ($total > 0 && $submitted >= $total) {
                $status = 'Fully Attempted';
            }

            return [
                $row->student_name,
                $row->levels ?: '-',
                $submitted . ' / ' . $total,
                $status,
            ];
        })->toArray();
    }
}
