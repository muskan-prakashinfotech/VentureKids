<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\StudentObservationHelper;
use App\Helpers\StudentRewardPointsHelper;
use App\Services\StudentProgressService;
use Carbon\Carbon;
use App\Models\SchoolAcademicYear;

class ProgressreportController extends Controller
{
    protected function getAcademicYearOptionsForSchool($schoolId)
    {
        $academicYears = SchoolAcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get();

        $today = Carbon::today();
        $selectedAcademicYear = 'past';
        $currentAcademicYear = $academicYears->first(function ($academicYear) use ($today) {
            // return Carbon::parse($academicYear->start_date)->lte($today)
            //     && Carbon::parse($academicYear->end_date)->gte($today);
            return Carbon::parse($academicYear->start_date)
                && Carbon::parse($academicYear->end_date);
        });

        if (!empty($currentAcademicYear)) {
            $selectedAcademicYear = (string) $currentAcademicYear->id;
        }

        $academicYearOptions = $academicYears->map(function ($academicYear) use ($today) {
            $startDate = Carbon::parse($academicYear->start_date);
            $endDate = Carbon::parse($academicYear->end_date);

            // if ($startDate->gt($today)) {
            //     $status = 'Upcoming';
            // } elseif ($endDate->lt($today)) {
            //     $status = 'Expired';
            // } else {
            //     $status = 'Current';
            // }

            return [
                'id' => $academicYear->id,
                // 'label' => $startDate->format('d-m-Y') . ' - ' . $endDate->format('d-m-Y') . ' (' . $status . ')',
                'label' => 'AY ' . $startDate->format('Y') . ' - ' . $endDate->format('y'),
            ];
        })->values();

        return [$academicYearOptions, $selectedAcademicYear];
    }

    public function progressReport()
    {
        $levels = Grade::all();   
        $filtered_levels = [];
        $filtered_collection = $levels->filter(function ($item) use (&$filtered_levels) {
            if($item->is_primary == 1) {
                $filtered_levels['primary'][$item->id] = $item->toArray();        
            } else {
                $filtered_levels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();
        // Resolve the default/entry grade dynamically (is_primary + lowest
        // display order) instead of a hardcoded unique_code, which broke
        // every time the active database's `grades` catalog didn't contain
        // that exact code (see the matching fix in
        // Student\StudentAccountController::index() and
        // StudentService::createStudent()).
        $gradeData = Grade::select('id')->where('is_primary', 1)->orderBy('display_order_id')->first()
            ?: Grade::select('id')->orderBy('display_order_id')->first();
        $currentGradeId = $gradeData->id ?? null;
        $schoolId = Session::get('school_id');
        [$academicYearOptions, $selectedAcademicYear] = $this->getAcademicYearOptionsForSchool($schoolId);

        return view('school.progres_report.list_progress', [
            'grades' => $filtered_levels,
            'currentGradeId' => $currentGradeId,
            'academicYears' => $academicYearOptions,
            'selectedAcademicYear' => $selectedAcademicYear,
        ]);
    }

    public function studentInfo(Request $request)
    {
        return response()->json(
            StudentProgressService::getData(
                (int) $request->studentId,
                (int) $request->gradeId,
                $request->get('academic_year')
            )
        );
    }

    public function getProgressByGrade(Request $request)
    {
        $grade_id = $request->grade_id;
        $school_id = Session::get('school_id');
        $data['students'] = [];
        $students = Students::select('id','user_id', 'image')->with([
            'user' => function ($query) {
                $query->select('id', 'name');
            }
        ])->where('school_id', $school_id)->whereRaw("find_in_set($grade_id,grade_id)")->get();
        if($students->count()) {
            $data['students'] = $students->toArray();
        }
        $data['grade_name'] = Grade::select('grade')->find($grade_id);
        echo json_encode($data);
    }
    
    public function generateLeaderBoard(Request $request) {
        $school_id = Session::get('school_id');
        $academicYearFilter = $request->get('academic_year');
        $gradeId = (int) $request->get('grade_id');
        $month = $request->get('month');

        $query = Students::select('id', 'user_id', 'image')->with([
            'user' => function ($query) {
                $query->select('id', 'name');
            },
        ])->where('school_id', $school_id);

        if (!empty($gradeId)) {
            $query->whereRaw("find_in_set(?, grade_id)", [$gradeId]);
        }

        $students = $query->get();

        if ($students->count()) {
            foreach ($students as $student) {
                $reward_points = StudentRewardPointsHelper::getRewardPoints($student->id, $academicYearFilter, $month);
                $student->tot_reward_points = array_sum($reward_points);
            }
            $students = $students->sortByDesc('tot_reward_points');
        }
        
        echo json_encode(array_values($students->toArray()));
    }

    public function getRewardPointDetails(Request $request) {
        $stud_id  = (int) $request->stud_id;
        $academicYearFilter = $request->get('academic_year');
        $month = $request->get('month');
        if ($stud_id) {
            return json_encode(StudentRewardPointsHelper::getRewardPoints($stud_id, $academicYearFilter, $month));
        }
        return false;
    }

    public function getStudentObservations(Request $request)
    {
        return StudentObservationHelper::getByStudentAndGrade(
            (int) $request->studentId,
            (int) $request->gradeId
        );
    }

}
