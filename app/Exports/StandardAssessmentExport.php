<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StandardAssessmentExport implements FromArray, WithHeadings
{
    protected $schoolId;

    public function __construct($schoolId = null)
    {
        $this->schoolId = $schoolId ? (int) $schoolId : null;
    }

    public function headings(): array
    {
        return [
            'School Name',
            'Student Name',
            'Batch',
            'Grade',
            'Category',
            'Question',
            'Selected Option',
            'Response',
            'Option Weightage',
        ];
    }

    public function array(): array
    {
        $query = DB::table('standard_assessment_student_answers as a')
            ->join('standard_assessment_questions as q', 'q.id', '=', 'a.question_id')
            ->leftJoin('standard_assessment_categories as c', 'c.id', '=', 'q.category_id')
            ->leftJoin('grades as cg', 'cg.id', '=', 'c.grade_id')
            ->join('students as s', 's.id', '=', 'a.student_id')
            ->leftJoin('schools as sc', 'sc.id', '=', 's.school_id')
            ->leftJoin('school_batch as sb', 'sb.id', '=', 's.school_batch_id')
            ->select([
                'sc.school_name as school_name',
                's.name as student_name',
                'sb.batch_name as batch_name',
                'cg.grade as grade_name',
                'c.grade_id as grade_id',
                'c.category_name as category_name',
                'q.question_text as question_text',
                'a.selected_option as selected_option',
                DB::raw("CASE a.selected_option
                    WHEN 'a' THEN q.option_a
                    WHEN 'b' THEN q.option_b
                    WHEN 'c' THEN q.option_c
                    WHEN 'd' THEN q.option_d
                    ELSE '' END as response_text"),
                DB::raw("CASE a.selected_option
                    WHEN 'a' THEN q.option_a_score
                    WHEN 'b' THEN q.option_b_score
                    WHEN 'c' THEN q.option_c_score
                    WHEN 'd' THEN q.option_d_score
                    ELSE NULL END as option_weightage"),
            ])
            ->where('a.is_submitted', 1);

        applyCountryScope($query, 'sc.country_id');

        if ($this->schoolId) {
            $query->where('s.school_id', $this->schoolId);
        }

        $rows = $query->orderBy('s.name')->orderBy('c.category_name')->orderBy('q.id')->get();

        if ($rows->isEmpty()) {
            return [['No data found', '', '', '', '']];
        }

        return $rows->map(function ($row) {
            $gradeValue = $row->grade_name ?: ($row->grade_id ?? '');

            return [
                $row->school_name ?? '',
                $row->student_name,
                $row->batch_name ?? '',
                $gradeValue,
                $row->category_name ?? '',
                $row->question_text,
                $row->selected_option ? strtoupper($row->selected_option) : '',
                $row->response_text,
                $row->option_weightage ?? '',
            ];
        })->toArray();
    }
}

